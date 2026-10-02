<?php
// /src/Service/ReviewService.php

declare(strict_types=1);

namespace Src\Service;

use App\Models\Review;
use App\Models\ReviewCriterion;
use App\Models\ReviewCriterionScore;
use App\Models\ReviewReport;
use App\Models\ReviewResponse;
use App\Models\ReviewTag;
use App\Models\Tenancy;
use App\Traits\RecentActivityLogger;
use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager as Capsule;
use Src\Utils\ReviewReportReasons;
use Src\Utils\ReviewTagCatalog;

/**
 * ReviewService
 * Submission + the double-blind release algorithm
 * (landlord_and_tenant_validation.pdf §6) + disputes (§9) + right of
 * response (§10).
 *
 * Double-blind release has two trigger paths, both funneling through
 * maybeRelease(): (a) immediately on submission, so a pair releases the
 * instant the second review lands without waiting for a cron tick, and
 * (b) the scheduled sweep (scripts/cron/sweep-review-window-expiry.php),
 * the only path that can release a LONE review whose counterpart never
 * showed up. Both take a row lock on the tenancy so a late second
 * submission can never race a concurrent cron sweep into a double-release
 * or an inconsistent released_at.
 */
class ReviewService
{
    use RecentActivityLogger;

    private const REVIEW_WINDOW_DAYS = 30;

    /**
     * @param array<int, array{criterion_id:int, stars:?int, is_na:bool}> $criteriaInput Must cover every criterion for $revieweeType.
     * @param string[] $tagKeys
     * @return array{success:bool, message?:string, errors?:string[], review?:Review}
     */
    public static function submit(
        Tenancy $tenancy,
        int $reviewerUserId,
        string $revieweeType,
        array $criteriaInput,
        ?string $comment,
        array $tagKeys
    ): array {
        if (Review::where('tenancy_id', $tenancy->id)->where('reviewer_user_id', $reviewerUserId)->exists()) {
            return ['success' => false, 'errors' => ['You have already submitted a review for this tenancy.']];
        }

        $criteria = ReviewCriterion::forSubject($revieweeType);
        $criteriaById = $criteria->keyBy('id');

        $errors = [];
        $inputByCriterionId = [];
        foreach ($criteriaInput as $entry) {
            $criterionId = (int) ($entry['criterion_id'] ?? 0);
            if (!$criteriaById->has($criterionId)) {
                continue;
            }
            $inputByCriterionId[$criterionId] = $entry;
        }

        foreach ($criteria as $criterion) {
            if (!isset($inputByCriterionId[$criterion->id])) {
                $errors[] = "\"{$criterion->label}\" must be rated or marked N/A.";
                continue;
            }
            $entry = $inputByCriterionId[$criterion->id];
            $isNa = (bool) ($entry['is_na'] ?? false);
            $stars = $entry['stars'] ?? null;

            if (!$isNa && (!is_numeric($stars) || (int) $stars < 1 || (int) $stars > 5)) {
                $errors[] = "\"{$criterion->label}\" needs a 1-5 rating or N/A.";
            }
        }

        foreach ($tagKeys as $tagKey) {
            if (!ReviewTagCatalog::isValid($revieweeType, (string) $tagKey)) {
                $errors[] = 'Invalid tag selected.';
                break;
            }
        }

        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $revieweeLandlordId = $revieweeType === 'landlord' ? $tenancy->landlord_id : null;
        $revieweeTenantId = $revieweeType === 'tenant' ? $tenancy->tenant_id : null;

        $review = Capsule::connection()->transaction(function () use (
            $tenancy, $reviewerUserId, $revieweeType, $revieweeLandlordId, $revieweeTenantId,
            $comment, $tagKeys, $criteria, $inputByCriterionId
        ) {
            $lockedTenancy = Tenancy::where('id', $tenancy->id)->lockForUpdate()->first();

            if ($lockedTenancy->review_window_opened_at === null) {
                $lockedTenancy->update([
                    'review_window_opened_at' => Carbon::now(),
                    'review_window_closes_at' => Carbon::now()->addDays(self::REVIEW_WINDOW_DAYS),
                ]);
            }

            $review = Review::create([
                'tenancy_id' => $lockedTenancy->id,
                'reviewer_user_id' => $reviewerUserId,
                'reviewee_type' => $revieweeType,
                'reviewee_landlord_id' => $revieweeLandlordId,
                'reviewee_tenant_id' => $revieweeTenantId,
                'comment' => $comment !== null && trim($comment) !== '' ? trim($comment) : null,
                'country_id' => $lockedTenancy->country_id,
            ]);

            foreach ($criteria as $criterion) {
                $entry = $inputByCriterionId[$criterion->id];
                $isNa = (bool) ($entry['is_na'] ?? false);

                ReviewCriterionScore::create([
                    'review_id' => $review->id,
                    'criterion_id' => $criterion->id,
                    'stars' => $isNa ? null : (int) $entry['stars'],
                    'is_na' => $isNa,
                ]);
            }

            foreach (array_unique($tagKeys) as $tagKey) {
                ReviewTag::create(['review_id' => $review->id, 'tag_key' => (string) $tagKey]);
            }

            self::maybeRelease($lockedTenancy);

            self::logActivity('Submitted a review', 'Review', $review->id, $reviewerUserId);

            return $review->fresh();
        });

        return [
            'success' => true,
            'review' => $review,
            'message' => $review->release_status === 'released'
                ? 'Review published.'
                : 'Review submitted — your review will be published after the other party submits theirs or when the review period closes.',
        ];
    }

    /**
     * Releases every pending review on a tenancy once both parties have
     * submitted, OR once the review window has closed (whichever is
     * checked first — the caller is responsible for passing a tenancy
     * that's already locked if called inside a transaction that needs
     * that guarantee; the cron sweep locks separately per tenancy).
     */
    public static function maybeRelease(Tenancy $tenancy): void
    {
        $reviews = Review::where('tenancy_id', $tenancy->id)->get();

        $bothSubmitted = $reviews->count() === 2;
        $windowExpired = $tenancy->review_window_closes_at !== null
            && $tenancy->review_window_closes_at->isPast();

        if ($bothSubmitted || $windowExpired) {
            Review::where('tenancy_id', $tenancy->id)
                ->where('release_status', 'pending')
                ->update(['release_status' => 'released', 'released_at' => Carbon::now()]);
        }
    }

    /**
     * "Report Review" (PDF §9) — only offered on reviews currently visible
     * to the reporter (released + published), enforced by the caller/UI; a
     * reported review never auto-hides, it just enters the moderation
     * queue (see ReviewModerationController).
     *
     * @return array{success:bool, errors?:string[]}
     */
    public static function report(int $reviewId, int $reporterUserId, string $reason, ?string $details): array
    {
        if (!ReviewReportReasons::isValid($reason)) {
            return ['success' => false, 'errors' => ['Invalid report reason.']];
        }

        if (ReviewReport::where('review_id', $reviewId)->where('reporter_user_id', $reporterUserId)->exists()) {
            return ['success' => false, 'errors' => ['You have already reported this review.']];
        }

        ReviewReport::create([
            'review_id' => $reviewId,
            'reporter_user_id' => $reporterUserId,
            'reason' => $reason,
            'details' => $details !== null && trim($details) !== '' ? trim($details) : null,
        ]);

        return ['success' => true];
    }

    /**
     * Right of response (PDF §10) — exactly one per review, from the
     * reviewed party. DB-enforced via a unique constraint on review_id; the
     * caller should check Review::response()->exists() first for a clean
     * error message, but the constraint is the real backstop.
     *
     * @return array{success:bool, errors?:string[], response?:ReviewResponse}
     */
    public static function respond(int $reviewId, int $responderUserId, string $responseText): array
    {
        if (trim($responseText) === '') {
            return ['success' => false, 'errors' => ['A response cannot be empty.']];
        }

        if (ReviewResponse::where('review_id', $reviewId)->exists()) {
            return ['success' => false, 'errors' => ['This review already has a response.']];
        }

        try {
            $response = ReviewResponse::create([
                'review_id' => $reviewId,
                'responder_user_id' => $responderUserId,
                'response' => trim($responseText),
            ]);
        } catch (\Throwable $e) {
            return ['success' => false, 'errors' => ['This review already has a response.']];
        }

        return ['success' => true, 'response' => $response];
    }
}

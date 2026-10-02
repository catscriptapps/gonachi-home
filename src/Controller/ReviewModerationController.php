<?php
// /src/Controller/ReviewModerationController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Review;
use App\Models\ReviewReport;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * ReviewModerationController
 * Admin-only disputes queue (landlord_and_tenant_validation.pdf §9) —
 * replaces the old LandlordReportReviewController/TenantReportReviewController
 * pre-publish approval queues entirely, since reviews now publish
 * automatically via the double-blind release mechanic (ReviewService) and
 * the only thing an admin ever moderates is a REPORTED review. A reported
 * review never auto-hides; it stays visible until an admin upholds the
 * report, which is the only action that flips the parent review's
 * moderation_status.
 */
class ReviewModerationController
{
    public static function pending(int $perPage = 15): LengthAwarePaginator
    {
        return ReviewReport::with(['review.reviewer', 'review.criterionScores.criterion', 'review.tenancy', 'reporter'])
            ->pendingReview()
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public static function uphold(int $reportId, int $resolvedByUserId, ?string $notes = null): bool
    {
        $report = ReviewReport::where('status', 'pending_review')->find($reportId);
        if (!$report) {
            return false;
        }

        $report->update([
            'status' => 'upheld',
            'resolved_by_user_id' => $resolvedByUserId,
            'resolved_at' => Carbon::now(),
            'resolution_notes' => $notes,
        ]);

        Review::where('id', $report->review_id)->update(['moderation_status' => 'removed_by_moderation']);

        return true;
    }

    public static function dismiss(int $reportId, int $resolvedByUserId, ?string $notes = null): bool
    {
        $report = ReviewReport::where('status', 'pending_review')->find($reportId);
        if (!$report) {
            return false;
        }

        return $report->update([
            'status' => 'dismissed',
            'resolved_by_user_id' => $resolvedByUserId,
            'resolved_at' => Carbon::now(),
            'resolution_notes' => $notes,
        ]);
    }
}

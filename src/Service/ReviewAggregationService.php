<?php
// /src/Service/ReviewAggregationService.php

declare(strict_types=1);

namespace Src\Service;

use App\Models\LandlordRecord;
use App\Models\Review;
use App\Models\ReviewCriterion;
use App\Models\TenantRecord;
use Illuminate\Support\Collection;

/**
 * ReviewAggregationService
 * The weighted-score math from landlord_and_tenant_validation.pdf §7-8 —
 * two independent levels of aggregation:
 *   1. reviewOverallScore() — within ONE review, across its own (up to 8)
 *      criterion scores: N/A criteria excluded, remaining weights
 *      re-normalized to sum to 100%, then weighted-summed.
 *   2. profileAggregate() — across ALL of one landlord/tenant's published
 *      reviews, for the public profile headline + per-category breakdown.
 * Nothing here is cached/stored — both levels are computed fresh from
 * ltv_review_criterion_scores (the only source of truth) on every read, so
 * retuning a criterion's weight in ltv_review_criteria never leaves stale
 * scores behind.
 */
class ReviewAggregationService
{
    public const MIN_REVIEWS_FOR_ESTABLISHED_TIER = 3;

    /**
     * @param Collection<int, \App\Models\ReviewCriterionScore> $criterionScores Must be eager-loaded with ->criterion.
     * @return float|null 1.0–5.0, or null if every criterion was N/A.
     */
    public static function reviewOverallScore(Collection $criterionScores): ?float
    {
        $applicable = $criterionScores->filter(fn ($s) => !$s->is_na && $s->stars !== null);

        $totalWeight = $applicable->sum(fn ($s) => $s->criterion->weight ?? 0);
        if ($totalWeight <= 0) {
            return null;
        }

        $weightedSum = 0.0;
        foreach ($applicable as $score) {
            $renormalizedWeight = $score->criterion->weight / $totalWeight;
            $weightedSum += $renormalizedWeight * $score->stars;
        }

        return $weightedSum;
    }

    /**
     * Profile-level aggregate for a landlord or tenant: headline score,
     * published review count, and the per-criterion breakdown
     * ("Property Maintenance 4.8", etc.).
     *
     * Deliberate design choice: the headline score is the AVERAGE OF EACH
     * REVIEW'S OWN already-renormalized score — every published review
     * counts equally toward it, regardless of how many of its 8 criteria
     * the reviewer filled in. This is NOT a global pool of every
     * criterion-score row across every review (which would let a reviewer
     * who only scores one heavily-weighted criterion exert outsized
     * influence compared to one who conscientiously scores all 8).
     *
     * @return array{overall: ?float, reviewCount: int, byCriterion: array<string, ?float>, tier: string}
     */
    public static function profileAggregate(string $subjectType, int $subjectId): array
    {
        $column = $subjectType === 'tenant' ? 'reviewee_tenant_id' : 'reviewee_landlord_id';

        $reviews = Review::visible()
            ->where($column, $subjectId)
            ->with('criterionScores.criterion')
            ->get();

        $perReviewOverall = $reviews
            ->map(fn ($review) => self::reviewOverallScore($review->criterionScores))
            ->filter(fn ($score) => $score !== null);

        $overall = $perReviewOverall->isEmpty() ? null : round($perReviewOverall->avg(), 1);
        $reviewCount = $reviews->count();

        $byCriterion = [];
        foreach (ReviewCriterion::forSubject($subjectType) as $criterion) {
            $starsAcrossReviews = $reviews
                ->flatMap(fn ($r) => $r->criterionScores)
                ->where('criterion_id', $criterion->id)
                ->where('is_na', false)
                ->whereNotNull('stars')
                ->pluck('stars');

            $byCriterion[$criterion->key] = $starsAcrossReviews->isEmpty() ? null : round($starsAcrossReviews->avg(), 1);
        }

        return [
            'overall' => $overall,
            'reviewCount' => $reviewCount,
            'byCriterion' => $byCriterion,
            'tier' => self::displayTier($reviewCount),
        ];
    }

    /**
     * Minimum-reviews-before-showing-a-public-score tiers (PDF §8).
     */
    public static function displayTier(int $publishedReviewCount): string
    {
        if ($publishedReviewCount === 0) {
            return 'none';
        }
        if ($publishedReviewCount < self::MIN_REVIEWS_FOR_ESTABLISHED_TIER) {
            return 'limited';
        }
        return 'established';
    }

    /**
     * "Frequently mentioned" tag chips (PDF §13) — the most-used structured
     * tags across a subject's published reviews, most-frequent first.
     *
     * @return array<int, array{key: string, label: string, count: int}>
     */
    public static function frequentTags(string $subjectType, int $subjectId, int $limit = 5): array
    {
        $column = $subjectType === 'tenant' ? 'reviewee_tenant_id' : 'reviewee_landlord_id';

        $reviewIds = Review::visible()->where($column, $subjectId)->pluck('id');

        $counts = \App\Models\ReviewTag::whereIn('review_id', $reviewIds)
            ->selectRaw('tag_key, COUNT(*) as tag_count')
            ->groupBy('tag_key')
            ->orderByDesc('tag_count')
            ->limit($limit)
            ->get();

        return $counts->map(fn ($row) => [
            'key' => $row->tag_key,
            'label' => \Src\Utils\ReviewTagCatalog::labelFor($subjectType, $row->tag_key) ?? $row->tag_key,
            'count' => (int) $row->tag_count,
        ])->all();
    }

    public static function verifiedTenancyCount(string $subjectType, int $subjectId): int
    {
        $column = $subjectType === 'tenant' ? 'tenant_id' : 'landlord_id';

        return \App\Models\Tenancy::where($column, $subjectId)->where('is_verified', true)->count();
    }
}

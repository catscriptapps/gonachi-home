<?php
// /src/Controller/ReviewProfileController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\LandlordRecord;
use App\Models\Review;
use App\Models\TenantRecord;
use Illuminate\Pagination\LengthAwarePaginator;
use Src\Service\ReviewAggregationService;

/**
 * ReviewProfileController
 * Read-side of the rebuilt review system: profile pages (landlord_and_
 * tenant_validation.pdf §13), search, and the landing page's live
 * counters/teasers — replaces LandlordDirectoryController/
 * TenantDirectoryController's read methods, now backed by the weighted
 * Review/Tenancy model instead of a single overall rating.
 */
class ReviewProfileController
{
    /**
     * @return array{record: LandlordRecord, aggregate: array, frequentTags: array, verifiedTenancyCount: int, recentReviews: \Illuminate\Support\Collection}
     */
    public static function landlordProfile(int $landlordId): ?array
    {
        $landlord = LandlordRecord::find($landlordId);
        if (!$landlord) {
            return null;
        }

        return [
            'record' => $landlord,
            'aggregate' => ReviewAggregationService::profileAggregate('landlord', $landlordId),
            'frequentTags' => ReviewAggregationService::frequentTags('landlord', $landlordId),
            'verifiedTenancyCount' => ReviewAggregationService::verifiedTenancyCount('landlord', $landlordId),
            'recentReviews' => self::recentReviews('landlord', $landlordId),
        ];
    }

    public static function tenantProfile(int $tenantId): ?array
    {
        $tenant = TenantRecord::find($tenantId);
        if (!$tenant) {
            return null;
        }

        return [
            'record' => $tenant,
            'aggregate' => ReviewAggregationService::profileAggregate('tenant', $tenantId),
            'frequentTags' => ReviewAggregationService::frequentTags('tenant', $tenantId),
            'verifiedTenancyCount' => ReviewAggregationService::verifiedTenancyCount('tenant', $tenantId),
            'recentReviews' => self::recentReviews('tenant', $tenantId),
        ];
    }

    private static function recentReviews(string $subjectType, int $subjectId, int $limit = 5)
    {
        $column = $subjectType === 'tenant' ? 'reviewee_tenant_id' : 'reviewee_landlord_id';

        return Review::visible()
            ->where($column, $subjectId)
            ->with(['criterionScores.criterion', 'tags', 'tenancy', 'response'])
            ->orderByDesc('released_at')
            ->limit($limit)
            ->get();
    }

    public static function searchLandlords(string $query, int $perPage = 12, ?int $countryId = null): LengthAwarePaginator
    {
        $needle = trim($query);

        $q = LandlordRecord::whereHas('tenancies.reviews', fn ($rq) => $rq->visible())
            ->where('name', 'like', "%{$needle}%");

        if ($countryId !== null) {
            $q->whereHas('tenancies', fn ($tq) => $tq->where('country_id', $countryId));
        }

        return $q->orderBy('name')->paginate($perPage);
    }

    public static function searchTenants(string $query, int $perPage = 12, ?int $countryId = null): LengthAwarePaginator
    {
        $needle = trim($query);

        $q = TenantRecord::whereHas('tenancies.reviews', fn ($rq) => $rq->visible())
            ->where('name', 'like', "%{$needle}%");

        if ($countryId !== null) {
            $q->whereHas('tenancies', fn ($tq) => $tq->where('country_id', $countryId));
        }

        return $q->orderBy('name')->paginate($perPage);
    }

    public static function recentlyReviewedLandlord(?int $countryId = null): ?LandlordRecord
    {
        $q = LandlordRecord::whereHas('tenancies.reviews', fn ($rq) => $rq->visible());
        if ($countryId !== null) {
            $q->whereHas('tenancies', fn ($tq) => $tq->where('country_id', $countryId));
        }
        return $q->orderByDesc('updated_at')->first();
    }

    public static function recentlyReviewedTenant(?int $countryId = null): ?TenantRecord
    {
        $q = TenantRecord::whereHas('tenancies.reviews', fn ($rq) => $rq->visible());
        if ($countryId !== null) {
            $q->whereHas('tenancies', fn ($tq) => $tq->where('country_id', $countryId));
        }
        return $q->orderByDesc('updated_at')->first();
    }

    public static function totalReviewedLandlords(?int $countryId = null): int
    {
        $q = LandlordRecord::whereHas('tenancies.reviews', fn ($rq) => $rq->visible());
        if ($countryId !== null) {
            $q->whereHas('tenancies', fn ($tq) => $tq->where('country_id', $countryId));
        }
        return $q->count();
    }

    public static function totalReviewedTenants(?int $countryId = null): int
    {
        $q = TenantRecord::whereHas('tenancies.reviews', fn ($rq) => $rq->visible());
        if ($countryId !== null) {
            $q->whereHas('tenancies', fn ($tq) => $tq->where('country_id', $countryId));
        }
        return $q->count();
    }

    public static function totalPublishedLandlordReviews(?int $countryId = null): int
    {
        $q = Review::visible()->where('reviewee_type', 'landlord');
        if ($countryId !== null) {
            $q->where('country_id', $countryId);
        }
        return $q->count();
    }

    public static function totalPublishedTenantReviews(?int $countryId = null): int
    {
        $q = Review::visible()->where('reviewee_type', 'tenant');
        if ($countryId !== null) {
            $q->where('country_id', $countryId);
        }
        return $q->count();
    }

    /**
     * Character-based ★/☆ row — same convention as the old
     * LandlordDirectoryController::starHtml().
     */
    public static function starHtml(?float $score): string
    {
        $filled = $score !== null ? (int) round($score) : 0;
        $filled = max(0, min(5, $filled));

        return str_repeat('★', $filled) . str_repeat('☆', 5 - $filled);
    }
}

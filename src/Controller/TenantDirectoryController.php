<?php
// /src/Controller/TenantDirectoryController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\TenantRecord;
use App\Models\TenantReport;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * TenantDirectoryController
 * The mirror-image of LandlordDirectoryController: landlords report
 * problematic tenants instead of tenants reporting landlords — same
 * find-or-create + admin-review-gated confidence engine, per
 * landlord_and_tenant_validation.pdf's Tenant Profile spec (Rental History,
 * Previous Property Records, Landlord Reviews, Verification Status,
 * References, Confidence Score) and Product Vision ("Landlords avoid
 * problematic tenants"). starHtml() is shared as-is from
 * LandlordDirectoryController — it's a pure ★/☆ formatter, not
 * landlord-specific.
 */
class TenantDirectoryController
{
    private const REQUIRED_FIELDS = ['tenant_name', 'conduct_type'];

    /**
     * Normalize, find-or-create the tenant, then create the report itself
     * (always starts pending_review).
     *
     * @param array $input Decoded JSON body: tenant_name, property_address,
     *                      duration_of_tenancy, conduct_type, notes,
     *                      rating (1-5), reference_name, reference_phone
     *                      (optional — backfills the tenant's record once
     *                      this report is approved, see
     *                      TenantReportReviewController::approve()).
     * @return array{success: bool, errors: string[]}
     */
    public static function submitReport(array $input, int $userId): array
    {
        $errors = [];
        foreach (self::REQUIRED_FIELDS as $field) {
            if (trim((string) ($input[$field] ?? '')) === '') {
                $errors[] = "The {$field} field is required.";
            }
        }

        $rating = $input['rating'] ?? null;
        if (!is_numeric($rating) || (int) $rating < 1 || (int) $rating > 5) {
            $errors[] = 'A star rating (1-5) is required.';
        }

        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $tenantName = trim((string) $input['tenant_name']);

        $tenant = TenantRecord::firstOrCreate(
            ['normalized_name' => self::normalize($tenantName)],
            ['name' => $tenantName]
        );

        TenantReport::create([
            'tenant_id' => $tenant->id,
            'user_id' => $userId,
            'property_address' => trim((string) ($input['property_address'] ?? '')) ?: null,
            'duration_of_tenancy' => trim((string) ($input['duration_of_tenancy'] ?? '')) ?: null,
            'conduct_type' => (string) $input['conduct_type'],
            'notes' => trim((string) ($input['notes'] ?? '')) ?: null,
            'rating' => (int) $rating,
            'reference_name' => trim((string) ($input['reference_name'] ?? '')) ?: null,
            'reference_phone' => trim((string) ($input['reference_phone'] ?? '')) ?: null,
            'status' => 'pending_review',
        ]);

        return ['success' => true, 'errors' => []];
    }

    /**
     * Deterministic confidence score — same shape as
     * LandlordDirectoryController::confidenceScore(), with a "has a
     * reference on file" bonus standing in for that method's photo-evidence
     * bonus (tenant reports carry no attachments, per the PDF's own field
     * list — "References" is the Tenant Profile's evidentiary equivalent).
     */
    public static function confidenceScore(TenantRecord $tenant): int
    {
        $published = $tenant->reports()->published()->get();
        $count = $published->count();

        if ($count === 0) {
            return 0;
        }

        $uniqueReporters = $published->pluck('user_id')->unique()->count();
        $hasReference = $published->contains(fn($r) => !empty($r->reference_phone));

        $score = 25 + ($count * 15) + ($uniqueReporters >= 2 ? 10 : 0) + ($hasReference ? 10 : 0);

        return min(95, $score);
    }

    /**
     * Most recently reported tenant records, for the landing page's
     * "Recently Reported Tenant" card.
     *
     * @return \Illuminate\Support\Collection<int, TenantRecord>
     */
    public static function recentPublished(int $limit = 1)
    {
        return self::publishedTenantsQuery()
            ->orderByDesc('latest_report_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Search published tenant records by name.
     */
    public static function search(string $query, int $perPage = 12): LengthAwarePaginator
    {
        $needle = trim($query);

        return self::publishedTenantsQuery()
            ->where('name', 'like', "%{$needle}%")
            ->orderByDesc('latest_report_at')
            ->paginate($perPage);
    }

    /**
     * Distinct tenant records with at least one published report — the
     * landing page's "Tenant Records" live counter.
     */
    public static function totalPublishedTenants(): int
    {
        return TenantRecord::whereHas('reports', fn($q) => $q->published())->count();
    }

    /**
     * Total published tenant reports — the landing page's
     * "Tenant Reports" live counter.
     */
    public static function totalPublishedReports(): int
    {
        return TenantReport::published()->count();
    }

    /**
     * Base query for tenant records with at least one published report,
     * annotated with the count/recency/average_rating the read paths above
     * need.
     */
    private static function publishedTenantsQuery()
    {
        return TenantRecord::whereHas('reports', fn($q) => $q->published())
            ->withCount(['reports as published_reports_count' => fn($q) => $q->published()])
            ->withMax(['reports as latest_report_at' => fn($q) => $q->published()], 'created_at')
            ->withAvg(['reports as average_rating' => fn($q) => $q->published()], 'rating');
    }

    private static function normalize(string $value): string
    {
        return mb_strtolower(preg_replace('/\s+/', ' ', trim($value)));
    }
}

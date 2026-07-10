<?php
// /src/Controller/LandlordDirectoryController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\LandlordRecord;
use App\Models\LandlordReport;
use App\Models\LandlordReportPhoto;
use App\Models\PropertyRecord;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * LandlordDirectoryController
 * Owns the report-a-landlord contribution loop: normalizing/deduping
 * landlord + property records, storing reports (gated behind admin review
 * before they count toward a property's public confidence score), and the
 * read-side (recent record, search) that the landing page displays.
 */
class LandlordDirectoryController
{
    private const REQUIRED_FIELDS = ['address', 'landlord_name', 'issue_type'];

    /**
     * Only URLs under this path are trusted when attaching photos to a
     * report — guards against a crafted payload smuggling in an external
     * URL (e.g. a tracking pixel an admin's browser would load when
     * reviewing the report). See report-landlord-photo-upload.php /
     * report-landlord-document-upload.php, the only two writers of this path.
     */
    private const ALLOWED_UPLOAD_PATH = 'images/uploads/landlord-reports/';

    /**
     * Normalize, find-or-create the landlord + property, then create the
     * report itself (always starts pending_review). Photos are uploaded
     * separately beforehand (report-landlord-photo-upload.php /
     * report-landlord-document-upload.php, both fronted by JS upload flows)
     * — this just attaches the resulting URLs to the report.
     *
     * @param array $input Decoded JSON body: address, landlord_name, property_type,
     *                      duration_of_tenancy, issue_type, notes, building_picture_urls[], supporting_evidence_urls[].
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

        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $address = trim((string) $input['address']);
        $landlordName = trim((string) $input['landlord_name']);

        $landlord = LandlordRecord::firstOrCreate(
            ['normalized_name' => self::normalize($landlordName)],
            ['name' => $landlordName]
        );

        $property = PropertyRecord::firstOrCreate(
            ['landlord_id' => $landlord->id, 'normalized_address' => self::normalize($address)],
            [
                'address' => $address,
                'property_type' => trim((string) ($input['property_type'] ?? '')) ?: null,
            ]
        );

        $report = LandlordReport::create([
            'property_id' => $property->id,
            'landlord_id' => $landlord->id,
            'user_id' => $userId,
            'duration_of_tenancy' => trim((string) ($input['duration_of_tenancy'] ?? '')) ?: null,
            'issue_type' => (string) $input['issue_type'],
            'notes' => trim((string) ($input['notes'] ?? '')) ?: null,
            'status' => 'pending_review',
        ]);

        self::attachPhotos($report, 'building_picture', $input['building_picture_urls'] ?? []);
        self::attachPhotos($report, 'supporting_evidence', $input['supporting_evidence_urls'] ?? []);

        return ['success' => true, 'errors' => []];
    }

    /**
     * Deterministic confidence score, no ML: rewards published reports,
     * corroboration from more than one distinct reporter, and photo
     * evidence. Computed on read rather than cached — dataset is
     * moderation-gated and small, so there's no staleness to manage.
     */
    public static function confidenceScore(PropertyRecord $property): int
    {
        $published = $property->reports()->published()->get();
        $count = $published->count();

        if ($count === 0) {
            return 0;
        }

        $uniqueReporters = $published->pluck('user_id')->unique()->count();
        $hasPhotos = LandlordReportPhoto::whereIn('report_id', $published->pluck('id'))->exists();

        $score = 25 + ($count * 15) + ($uniqueReporters >= 2 ? 10 : 0) + ($hasPhotos ? 10 : 0);

        return min(95, $score);
    }

    /**
     * Most recently published property records, for the landing page's
     * "Recently Added Property Record" card.
     *
     * @return \Illuminate\Support\Collection<int, PropertyRecord>
     */
    public static function recentPublished(int $limit = 1)
    {
        return self::publishedPropertiesQuery()
            ->orderByDesc('latest_report_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Search published property records by landlord name or address.
     */
    public static function search(string $query, int $perPage = 12): LengthAwarePaginator
    {
        $needle = trim($query);

        return self::publishedPropertiesQuery()
            ->where(function ($q) use ($needle) {
                $q->where('address', 'like', "%{$needle}%")
                    ->orWhereHas('landlord', fn($lq) => $lq->where('name', 'like', "%{$needle}%"));
            })
            ->orderByDesc('latest_report_at')
            ->paginate($perPage);
    }

    /**
     * Distinct property records with at least one published report — the
     * landing page's "Property Records" live counter.
     */
    public static function totalPublishedProperties(): int
    {
        return PropertyRecord::whereHas('reports', fn($q) => $q->published())->count();
    }

    /**
     * Total published reports across every property — the landing page's
     * "Landlord Reports" live counter.
     */
    public static function totalPublishedReports(): int
    {
        return LandlordReport::published()->count();
    }

    /**
     * Base query for property records with at least one published report,
     * annotated with the count and recency needed by both read paths above.
     */
    private static function publishedPropertiesQuery()
    {
        return PropertyRecord::whereHas('reports', fn($q) => $q->published())
            ->with('landlord')
            ->withCount(['reports as published_reports_count' => fn($q) => $q->published()])
            ->withMax(['reports as latest_report_at' => fn($q) => $q->published()], 'created_at');
    }

    /**
     * @param string[] $urls Already-uploaded URLs, e.g. "/images/uploads/landlord-reports/xyz.jpg"
     *                       locally or "/gonachi-home/images/uploads/landlord-reports/xyz.jpg" on
     *                       a production deploy under a subdirectory (getAssetBase() prefixes
     *                       whichever applies — see report-landlord-photo-upload.php).
     */
    private static function attachPhotos(LandlordReport $report, string $kind, array $urls): void
    {
        $assetBase = getAssetBase();

        foreach ($urls as $url) {
            $url = trim((string) $url);

            if ($url === '' || !str_starts_with($url, $assetBase)) {
                continue;
            }

            // Strip the environment's base path back off so file_path is
            // always stored relative — read paths prepend $assetBase fresh
            // each time, so a baked-in base path here would break the moment
            // the environment's subdirectory ever changed.
            $relative = substr($url, strlen($assetBase));

            if (!str_starts_with($relative, self::ALLOWED_UPLOAD_PATH)) {
                continue;
            }

            LandlordReportPhoto::create([
                'report_id' => $report->id,
                'kind' => $kind,
                'file_path' => $relative,
            ]);
        }
    }

    private static function normalize(string $value): string
    {
        return mb_strtolower(preg_replace('/\s+/', ' ', trim($value)));
    }
}

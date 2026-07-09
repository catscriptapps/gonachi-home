<?php
// /src/Controller/LandlordDirectoryController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\LandlordRecord;
use App\Models\LandlordReport;
use App\Models\LandlordReportPhoto;
use App\Models\PropertyRecord;
use Illuminate\Pagination\LengthAwarePaginator;
use Src\Service\ImageUploadService;

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
     * Normalize, find-or-create the landlord + property, then create the
     * report itself (always starts pending_review). Any uploaded photos are
     * stored via ImageUploadService and attached to the report.
     *
     * @param array $input Raw $_POST fields.
     * @param array $files Raw $_FILES entries (expects 'building_pictures' / 'supporting_evidence' keys, each multi-file).
     * @return array{success: bool, errors: string[]}
     */
    public static function submitReport(array $input, array $files, int $userId): array
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

        self::storePhotos($report, $files);

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

    private static function storePhotos(LandlordReport $report, array $files): void
    {
        $uploadDir = realpath(__DIR__ . '/../../public/images/uploads/') . '/landlord-reports/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $service = new ImageUploadService($uploadDir);

        foreach (['building_pictures' => 'building_picture', 'supporting_evidence' => 'supporting_evidence'] as $inputName => $kind) {
            if (empty($files[$inputName]['tmp_name'][0])) {
                continue;
            }

            $service->upload($files[$inputName], function (array $uploadedFiles) use ($report, $kind) {
                foreach ($uploadedFiles as $file) {
                    LandlordReportPhoto::create([
                        'report_id' => $report->id,
                        'kind' => $kind,
                        'file_path' => 'images/uploads/landlord-reports/' . $file['fileName'],
                    ]);
                }
                return $uploadedFiles;
            });
        }
    }

    private static function normalize(string $value): string
    {
        return mb_strtolower(preg_replace('/\s+/', ' ', trim($value)));
    }
}

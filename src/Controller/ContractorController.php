<?php
// /src/Controller/ContractorController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Contractor;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * ContractorController
 * Owns the Contractor Discovery directory: admin-curated seed contractors
 * (see scripts/reset/cde-seed.php) plus auto-collected ones from
 * SerperContractorConnector/ContractorIngestionService (see
 * scripts/cron/run-contractor-discovery.php) — browsable by
 * category/location and searchable by business name/description.
 */
class ContractorController
{
    public const CATEGORY_LABELS = [
        'plumbing' => 'Plumbing',
        'electrical' => 'Electrical',
        'painting' => 'Painting',
        'building_construction' => 'Building Construction',
        'interior_design' => 'Interior Design',
        'renovation' => 'Renovation',
        'solar_installation' => 'Solar Installation',
        'other' => 'Other',
    ];

    /**
     * Real, active contractors — newest first, optional category/location/search filters.
     */
    public static function browse(?string $category, ?string $location, ?string $search, int $perPage = 10): LengthAwarePaginator
    {
        $query = Contractor::active()->orderByDesc('created_at');

        if ($category) {
            $query->where('service_category', $category);
        }

        if ($location) {
            $query->where('location', 'like', "%{$location}%");
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    public static function find(int $id): ?Contractor
    {
        return Contractor::active()->find($id);
    }

    /**
     * Live counter for the directory header.
     */
    public static function totalCount(): int
    {
        return Contractor::active()->count();
    }
}

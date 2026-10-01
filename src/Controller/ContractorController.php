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
     * Resolves a contractor from the URL segment used in /contractor/{slug-or-id}
     * — the SEO-friendly slug (see buildUniqueSlug()) for any contractor
     * created after that feature shipped, or a bare numeric id for backward
     * compatibility with links already sent out (outreach SMS/email, social
     * shares, bookmarks). Scoped to active, matching find()'s existing
     * behavior — there's no draft/pending-review state for contractors to
     * distinguish from "not found" the way Leads' findBySlugOrId() does.
     */
    public static function findBySlugOrId(string $slugOrId): ?Contractor
    {
        $query = Contractor::active();

        return ctype_digit($slugOrId)
            ? $query->find((int) $slugOrId)
            : $query->where('slug', $slugOrId)->first();
    }

    /**
     * Deterministic slug from the business name + the city/area portion of
     * its location (e.g. "Interior Edge Design Studio" in "Ikoyi, Lagos" ->
     * "interior-edge-design-studio-ikoyi") — mirrors
     * LeadsController::buildUniqueSlug()'s numeric-suffix collision handling.
     * Unlike leads, every contractor has a business_name + location from
     * creation, so this always produces a slug (no "incomplete record" gate).
     */
    public static function buildUniqueSlug(Contractor $contractor): ?string
    {
        $businessName = trim((string) $contractor->business_name);
        if ($businessName === '') {
            return null;
        }

        $areaPart = trim(explode(',', (string) $contractor->location)[0] ?? '');
        $base = \Illuminate\Support\Str::slug(trim("{$businessName} {$areaPart}"));

        if ($base === '') {
            return null;
        }

        $slug = $base;
        $suffix = 2;

        while (Contractor::where('slug', $slug)->where('id', '!=', $contractor->id)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /**
     * Live counter for the directory header.
     */
    public static function totalCount(): int
    {
        return Contractor::active()->count();
    }
}

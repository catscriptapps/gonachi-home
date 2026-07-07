<?php
// /src/Controller/LeadCategoryController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Lead;
use App\Models\LeadCategory;
use App\Models\Location;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * LeadCategoryController
 * Powers the SEO landing pages combining a lead category with a location
 * (e.g. /home-buyers-lagos), per the PDF's "System generates searchable
 * pages" acquisition engine. Pages are resolved dynamically from whatever
 * category/location rows exist — no per-combination file or route needed.
 */
class LeadCategoryController
{
    /**
     * Match a single-segment URL slug (e.g. "home-buyers-lagos") against
     * known category + location slugs. Returns null if no combination
     * matches, so the caller can fall through to a normal 404.
     *
     * @return array{category: LeadCategory, location: Location}|null
     */
    public static function matchSlug(string $slug): ?array
    {
        $categories = LeadCategory::orderByRaw('LENGTH(slug) DESC')->get();

        foreach ($categories as $category) {
            $prefix = $category->slug . '-';

            if (!str_starts_with($slug, $prefix)) {
                continue;
            }

            $locationSlug = substr($slug, strlen($prefix));
            $location = Location::where('slug', $locationSlug)->first();

            if ($location) {
                return ['category' => $category, 'location' => $location];
            }
        }

        return null;
    }

    /**
     * Active leads for a category, within a location and its immediate
     * child locations (e.g. "Lagos" includes "Lekki", "Ikeja", ...).
     */
    public static function leadsFor(LeadCategory $category, Location $location, int $perPage = 10): LengthAwarePaginator
    {
        $locationIds = Location::where('id', $location->id)
            ->orWhere('parent_id', $location->id)
            ->pluck('id');

        return Lead::with(['location.parent', 'category', 'source'])
            ->where('category_id', $category->id)
            ->whereIn('location_id', $locationIds)
            ->active()
            ->orderByDesc('posted_at')
            ->orderByDesc('scraped_at')
            ->paginate($perPage);
    }
}

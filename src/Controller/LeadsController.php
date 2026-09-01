<?php
// /src/Controller/LeadsController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Lead;
use App\Models\Location;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * LeadsController
 * Read-side access to publicly-visible (status = active) extracted leads,
 * for the homepage discovery feed and future category/location landing pages.
 */
class LeadsController
{
    /**
     * Count of active leads per request type, for the homepage live counters.
     *
     * @return array<string, int>
     */
    public static function activeCounts(): array
    {
        return [
            'buyer' => Lead::active()->where('request_type', 'buyer')->count(),
            'seller' => Lead::active()->where('request_type', 'seller')->count(),
        ];
    }

    /**
     * Most recent active leads for the homepage discovery feed.
     */
    public static function recentActive(int $limit = 5): Collection
    {
        return Lead::with(['location.parent', 'category', 'source'])
            ->active()
            ->orderByDesc('posted_at')
            ->orderByDesc('scraped_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Searchable, region-filterable, paginated active leads — powers the
     * search bar + region select on the real-estate-leads discovery page.
     * $regionSlug matches a region (see regions()) or any of its child
     * locations, e.g. "lagos" also matches leads located in "Lekki".
     */
    public static function browse(?string $search, ?string $regionSlug, int $perPage = 6): LengthAwarePaginator
    {
        $query = Lead::with(['location.parent', 'category', 'source'])->active();

        if ($search) {
            $term = '%' . $search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('raw_text', 'like', $term)
                    ->orWhere('location_raw', 'like', $term);
            });
        }

        if ($regionSlug) {
            $region = Location::where('slug', $regionSlug)->first();
            if ($region) {
                $locationIds = $region->children()->pluck('id')->push($region->id);
                $query->whereIn('location_id', $locationIds);
            }
        }

        return $query->orderByDesc('posted_at')
            ->orderByDesc('scraped_at')
            ->paginate($perPage);
    }

    /**
     * Regions for the search bar's "region" select — the state-level
     * locations (Lagos, Abuja, ...), i.e. locations whose own parent is a
     * top-level (country) location. Deliberately not hardcoded so a future
     * admin-managed location list stays in sync automatically.
     */
    public static function regions(): Collection
    {
        return Location::whereHas('parent', fn($q) => $q->whereNull('parent_id'))
            ->orderBy('name')
            ->get();
    }

    /**
     * Human-readable "Seeking: 4 Bedroom House" style headline built from
     * structured fields, falling back to a trimmed excerpt of the raw
     * scraped text when property type/bedrooms weren't extracted.
     */
    public static function headline(Lead $lead): string
    {
        $propertyLabel = match ($lead->property_type) {
            'residential' => 'House',
            'commercial' => 'Commercial Property',
            'land' => 'Land',
            default => null,
        };

        if ($propertyLabel === null) {
            $excerpt = trim((string) $lead->raw_text);
            return mb_strlen($excerpt) > 70 ? mb_substr($excerpt, 0, 70) . '…' : $excerpt;
        }

        $subject = $lead->bedrooms
            ? "{$lead->bedrooms} Bedroom {$propertyLabel}"
            : $propertyLabel;

        $verb = match ($lead->request_type) {
            'seller' => 'For Sale',
            'renter' => 'Wanted To Rent',
            'investor' => 'Investment Target',
            default => 'Seeking',
        };

        return "{$verb}: {$subject}";
    }

    /**
     * Location display string, preferring the resolved Location (with its
     * parent, e.g. "Lekki, Lagos") over the raw scraped location text.
     */
    public static function locationLabel(Lead $lead): string
    {
        if ($lead->location) {
            return $lead->location->parent
                ? "{$lead->location->name}, {$lead->location->parent->name}"
                : $lead->location->name;
        }

        return $lead->location_raw ?? 'Location Unspecified';
    }

    /**
     * Badge label + Tailwind color classes per request type.
     *
     * @return array{label: string, classes: string}
     */
    public static function requestTypeBadge(Lead $lead): array
    {
        return match ($lead->request_type) {
            'seller' => ['label' => 'Home Seller', 'classes' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-400'],
            'investor' => ['label' => 'Investor', 'classes' => 'bg-secondary-100 text-secondary-800 dark:bg-secondary-950 dark:text-secondary-400'],
            'renter' => ['label' => 'Renter', 'classes' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-400'],
            default => ['label' => 'Home Buyer', 'classes' => 'bg-primary-100 text-primary-800 dark:bg-primary-950 dark:text-primary-400'],
        };
    }

    /**
     * Label + Tailwind text color for the lead's intent_level.
     *
     * @return array{label: string, classes: string}
     */
    public static function intentBadge(Lead $lead): array
    {
        return match ($lead->intent_level) {
            'high' => ['label' => 'High Engagement', 'classes' => 'text-emerald-600 dark:text-emerald-400'],
            'low' => ['label' => 'Low Engagement', 'classes' => 'text-gray-500 dark:text-gray-400'],
            default => ['label' => 'Medium Engagement', 'classes' => 'text-amber-600 dark:text-amber-400'],
        };
    }

    /**
     * Human-readable property type, for the full detail view.
     */
    public static function propertyTypeLabel(Lead $lead): string
    {
        return match ($lead->property_type) {
            'residential' => 'Residential',
            'commercial' => 'Commercial',
            'land' => 'Land',
            default => 'Not specified',
        };
    }

    /**
     * Formats budget_min/budget_max as a Naira amount or range, or null
     * when neither was extracted.
     */
    public static function budgetLabel(Lead $lead): ?string
    {
        $min = $lead->budget_min !== null ? (float) $lead->budget_min : null;
        $max = $lead->budget_max !== null ? (float) $lead->budget_max : null;

        if ($min === null && $max === null) {
            return null;
        }

        if ($min !== null && $max !== null && $min !== $max) {
            return '₦' . number_format($min) . ' – ₦' . number_format($max);
        }

        return '₦' . number_format($min ?? $max);
    }
}

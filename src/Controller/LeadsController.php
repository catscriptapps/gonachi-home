<?php
// /src/Controller/LeadsController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Lead;
use App\Models\LeadCategory;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Src\Utils\ContactMasker;

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
        return self::matchingQuery($search, $regionSlug)
            ->with(['location.parent', 'category', 'source'])
            ->orderByDesc('posted_at')
            ->orderByDesc('scraped_at')
            ->paginate($perPage);
    }

    /**
     * How many active leads currently match a (search, region) pair —
     * powers the match count on a saved alert.
     */
    public static function countMatching(?string $search, ?string $regionSlug): int
    {
        return self::matchingQuery($search, $regionSlug)->count();
    }

    /**
     * How many matching active leads were scraped after $since — powers a
     * saved alert's "N new since you last checked" badge. A null $since
     * (never viewed) counts everything as new.
     */
    public static function countNewMatching(?string $search, ?string $regionSlug, ?Carbon $since): int
    {
        $query = self::matchingQuery($search, $regionSlug);

        if ($since) {
            $query->where('scraped_at', '>', $since);
        }

        return $query->count();
    }

    /**
     * Shared filter logic behind browse()/countMatching()/countNewMatching()
     * — same (search, region) semantics everywhere so a saved alert's match
     * count always agrees with what "View Matches" actually shows.
     * $regionSlug matches a region (see regions()) or any of its child
     * locations, e.g. "lagos" also matches leads located in "Lekki".
     */
    private static function matchingQuery(?string $search, ?string $regionSlug)
    {
        $query = Lead::active();

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

        return $query;
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
     * Homepage "Spotlight" card copy — the (category, location) pairing
     * with the most active leads, preferring the last 14 days so it reads
     * as a genuine trend rather than a lifetime total, but falling back to
     * all-time if nothing recent has enough signal. Returns null when there
     * isn't enough data yet (fewer than 2 matching leads) so the page can
     * show a neutral placeholder instead of a fabricated "trend".
     *
     * @return array{headline: string, text: string, count: int}|null
     */
    public static function spotlight(): ?array
    {
        $topCombo = self::topCategoryLocationCombo(Carbon::now()->subDays(14));

        if (!$topCombo || $topCombo->total < 2) {
            $topCombo = self::topCategoryLocationCombo(null);
        }

        if (!$topCombo || $topCombo->total < 2) {
            return null;
        }

        $category = LeadCategory::find($topCombo->category_id);
        $location = Location::with('parent')->find($topCombo->location_id);

        if (!$category || !$location) {
            return null;
        }

        $locationLabel = $location->parent ? "{$location->name}, {$location->parent->name}" : $location->name;

        return [
            'headline' => "{$category->name} Demand Is Rising",
            'text' => "{$locationLabel} leads new {$category->name} interest this month.",
            'count' => $topCombo->total,
        ];
    }

    /**
     * @return object{category_id: int, location_id: int, total: int}|null
     */
    private static function topCategoryLocationCombo(?Carbon $since)
    {
        $query = Lead::active()
            ->whereNotNull('category_id')
            ->whereNotNull('location_id');

        if ($since) {
            $query->where('scraped_at', '>=', $since);
        }

        return $query->selectRaw('category_id, location_id, count(*) as total')
            ->groupBy('category_id', 'location_id')
            ->orderByDesc('total')
            ->first();
    }

    /**
     * Specific residential property terms to look for in a lead's raw text,
     * most-specific first (checked before the generic "House" fallback) —
     * e.g. "Duplex" or "Bungalow" instead of the coarse property_type
     * category alone. Only residential leads use this list; property_type
     * itself stays a plain 'residential'/'commercial'/'land' for category
     * matching (see resolveCategory()) — this is purely a display refinement.
     */
    private const RESIDENTIAL_TERMS = [
        ['/\bsemi[- ]?detached\b/i', 'Semi-Detached Duplex'],
        ['/\bdetached\b/i', 'Detached Duplex'],
        ['/\bpenthouse\b/i', 'Penthouse'],
        ['/\bmaisonette\b/i', 'Maisonette'],
        ['/\bduplex\b/i', 'Duplex'],
        ['/\bbungalow\b/i', 'Bungalow'],
        ['/\btownhouse\b/i', 'Townhouse'],
        ['/\bterrace(?:d)?\b/i', 'Terrace'],
        ['/\bmansion\b/i', 'Mansion'],
        ['/\bstudio\b/i', 'Studio Apartment'],
        ['/\bself[- ]?contain(?:ed)?\b/i', 'Self-Contain'],
        ['/\bapartment\b/i', 'Apartment'],
        ['/\bflat\b/i', 'Flat'],
    ];

    private const COMMERCIAL_TERMS = [
        ['/\bwarehouse\b/i', 'Warehouse'],
        ['/\boffice\b/i', 'Office Space'],
        ['/\bshop\b/i', 'Shop'],
    ];

    private const LAND_TERMS = [
        ['/\bplot\b/i', 'Plot of Land'],
    ];

    /**
     * Human-readable "For Sale: 3-Bedroom Duplex in Lekki" style headline
     * built from structured fields (bedrooms, a specific property term
     * pulled from the raw text, and location when known), falling back to a
     * trimmed excerpt of the raw scraped text when property type wasn't
     * even coarsely extracted.
     */
    public static function headline(Lead $lead): string
    {
        $propertyLabel = self::specificPropertyLabel($lead);

        if ($propertyLabel === null) {
            $excerpt = trim((string) $lead->raw_text);
            return mb_strlen($excerpt) > 70 ? mb_substr($excerpt, 0, 70) . '…' : $excerpt;
        }

        $subject = $lead->bedrooms
            ? "{$lead->bedrooms}-Bedroom {$propertyLabel}"
            : $propertyLabel;

        $verb = match ($lead->request_type) {
            'seller' => 'For Sale',
            'renter' => 'Wanted To Rent',
            'investor' => 'Investment Target',
            default => 'Seeking',
        };

        return "{$verb}: {$subject}" . self::headlineLocationSuffix($lead);
    }

    /**
     * The generic property_type category label, upgraded to a more specific
     * term when one appears in the raw text (e.g. 'residential' -> "Duplex"
     * instead of the generic "House").
     */
    private static function specificPropertyLabel(Lead $lead): ?string
    {
        [$genericLabel, $terms] = match ($lead->property_type) {
            'residential' => ['House', self::RESIDENTIAL_TERMS],
            'commercial' => ['Commercial Property', self::COMMERCIAL_TERMS],
            'land' => ['Land', self::LAND_TERMS],
            default => [null, []],
        };

        if ($genericLabel === null) {
            return null;
        }

        $text = (string) $lead->raw_text;
        foreach ($terms as [$pattern, $label]) {
            if (preg_match($pattern, $text)) {
                return $label;
            }
        }

        return $genericLabel;
    }

    /**
     * " in Lekki" when a location is known, otherwise an empty string —
     * appended to the headline rather than always shown, since
     * locationLabel()'s own "Location Unspecified" fallback would read
     * strangely tacked onto the end of a headline.
     */
    private static function headlineLocationSuffix(Lead $lead): string
    {
        if ($lead->location) {
            return ' in ' . $lead->location->name;
        }

        if ($lead->location_raw) {
            return ' in ' . $lead->location_raw;
        }

        return '';
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
     * Partly-obfuscated version of a lead's raw contact text (e.g.
     * "+2348012345678" -> "+234*****", "buyer@example.com" -> "bu****@example.com")
     * — shown to non-admin viewers even after they've spent a credit to
     * unlock a lead, so the raw scraped contact itself (the actual asset
     * this platform sells access to) never appears verbatim outside of an
     * admin session. Only admins ever see $lead->contact_info_raw directly.
     * Delegates to the shared ContactMasker — Contractor Discovery's own
     * contact-reveal flow (ContractorController) uses the exact same masking.
     */
    public static function maskedContact(?string $raw): ?string
    {
        return ContactMasker::mask($raw);
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

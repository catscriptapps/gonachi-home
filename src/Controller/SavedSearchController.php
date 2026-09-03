<?php
// /src/Controller/SavedSearchController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\SavedSearch;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * SavedSearchController
 * Owns the Saved Alerts feature: a user saves a (search, region) pair —
 * the exact filter vocabulary LeadsController::browse() already uses — and
 * comes back later to see how many active leads match it, with a "N new
 * since you last checked" signal driven by last_viewed_at.
 */
class SavedSearchController
{
    private const MAX_PER_USER = 20;

    public static function forUser(int $userId): Collection
    {
        return SavedSearch::where('user_id', $userId)->orderByDesc('created_at')->get();
    }

    /**
     * @return array{success: bool, errors: string[], saved_search?: SavedSearch}
     */
    public static function create(int $userId, ?string $search, ?string $regionSlug): array
    {
        $search = $search !== null ? trim($search) : null;
        $search = $search === '' ? null : $search;
        $regionSlug = $regionSlug ?: null;

        if (!$search && !$regionSlug) {
            return ['success' => false, 'errors' => ['Enter a keyword or pick a region to save an alert.']];
        }

        if (self::forUser($userId)->count() >= self::MAX_PER_USER) {
            return ['success' => false, 'errors' => ['You can save up to ' . self::MAX_PER_USER . ' alerts. Delete one to add another.']];
        }

        $dupQuery = SavedSearch::where('user_id', $userId);
        $search ? $dupQuery->where('search_query', $search) : $dupQuery->whereNull('search_query');
        $regionSlug ? $dupQuery->where('region_slug', $regionSlug) : $dupQuery->whereNull('region_slug');

        if ($dupQuery->exists()) {
            return ['success' => false, 'errors' => ['You already have an alert saved for this search.']];
        }

        $saved = SavedSearch::create([
            'user_id' => $userId,
            'search_query' => $search,
            'region_slug' => $regionSlug,
            'last_viewed_at' => Carbon::now(),
        ]);

        return ['success' => true, 'errors' => [], 'saved_search' => $saved];
    }

    /**
     * Ownership-checked delete — returns false if the alert doesn't exist
     * or doesn't belong to $userId.
     */
    public static function delete(int $id, int $userId): bool
    {
        return (bool) SavedSearch::where('id', $id)->where('user_id', $userId)->delete();
    }

    /**
     * Resets the "new since last checked" clock for every one of a user's
     * saved alerts — call only after their current new-match counts have
     * already been computed/rendered for this visit.
     */
    public static function markAllViewed(int $userId): void
    {
        SavedSearch::where('user_id', $userId)->update(['last_viewed_at' => Carbon::now()]);
    }
}

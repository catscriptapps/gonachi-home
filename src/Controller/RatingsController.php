<?php
// /src/Controller/RatingsController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Rating;
use App\Models\RatingCriterion;
use App\Models\User;
use App\Traits\RecentActivityLogger;

/**
 * Owns the Real Estate World Ratings module — ported from the legacy
 * gonachi-old platform's standalone /ratings/ app. A user rates ANOTHER
 * user (not a listing/property) in the context of a role (user type) and
 * location, leaving a free-text review plus optional 1-5 star scores
 * against a criteria set that depends on the chosen role (some roles share
 * a generic set, Contractor and Real Estate Agent get their own
 * specialized set, and the generic "User" role has no criteria at all —
 * review only, matching gonachi-old's own user_type_id=8 special case).
 *
 * Deliberate deviations from the legacy source (documented, not silent):
 *  - gonachi-old's live /ratings/ app never actually checks the caller is
 *    logged in before inserting a rating (a real gap — the UI shows a
 *    login form to guests but the AJAX endpoint itself trusts the client).
 *    Every mutation here requires AuthService::userId(), matching every
 *    other module already built in this app.
 *  - No moderation/approval, no aggregate/average score, and no
 *    self-service delete — this matches gonachi-old's actual live
 *    behavior exactly (its `status_id` column was hard-coded to 1 and
 *    never read; its only delete path lived in orphaned, unrouted legacy
 *    code, not the live app).
 *  - Duplicate ratings of the same person are allowed, matching legacy
 *    (no dedup guard exists there either).
 */
class RatingsController
{
    use RecentActivityLogger;

    private const EAGER = ['rater.country', 'rater.region', 'ratee', 'country', 'region', 'scores.criterion'];

    /**
     * The star-criteria list for a given destination role — empty array for
     * roles with none (e.g. the generic "User" type).
     *
     * @return array<int, array{criteria_id:int, criteria:string}>
     */
    public static function criteriaFor(int $userTypeId): array
    {
        return RatingCriterion::where('user_type_id', $userTypeId)
            ->orderBy('criteria_id')
            ->get(['criteria_id', 'criteria'])
            ->map(fn (RatingCriterion $c) => ['criteria_id' => $c->criteria_id, 'criteria' => $c->criteria])
            ->all();
    }

    /**
     * Create a new rating. $input['scores'] is an optional array of
     * {criteria_id, stars} pairs — only scores for criteria that actually
     * belong to the chosen dest_user_type_id are persisted, so a
     * hand-crafted request can't attach scores from an unrelated role.
     *
     * @return array{success: bool, errors: string[], rating: ?Rating}
     */
    public static function submit(array $input, int $userId): array
    {
        $destUserId = (int) ($input['dest_user_id'] ?? 0);
        $userTypeId = (int) ($input['dest_user_type_id'] ?? 0);
        $comment = trim((string) ($input['comment'] ?? ''));

        if (!$destUserId || !$userTypeId || $comment === '') {
            return ['success' => false, 'errors' => ['A user, a role, and a review are required.'], 'rating' => null];
        }

        if ($destUserId === $userId) {
            return ['success' => false, 'errors' => ['You cannot rate yourself.'], 'rating' => null];
        }

        $ratee = User::find($destUserId);
        if (!$ratee) {
            return ['success' => false, 'errors' => ['User not found.'], 'rating' => null];
        }

        $rating = Rating::create([
            'orig_user_id' => $userId,
            'dest_user_id' => $destUserId,
            'dest_country_id' => (int) ($input['country_id'] ?? 0) ?: $ratee->country_id,
            'dest_region_id' => (int) ($input['region_id'] ?? 0) ?: $ratee->region_id,
            'dest_city' => trim((string) ($input['city'] ?? '')) ?: $ratee->city,
            'dest_user_type_id' => $userTypeId,
            'comment' => $comment,
        ]);

        $validCriteriaIds = RatingCriterion::where('user_type_id', $userTypeId)->pluck('criteria_id')->all();
        $scores = is_array($input['scores'] ?? null) ? $input['scores'] : [];

        foreach ($scores as $score) {
            $criteriaId = (int) ($score['criteria_id'] ?? 0);
            $stars = (int) ($score['stars'] ?? 0);

            if (!in_array($criteriaId, $validCriteriaIds, true) || $stars < 1 || $stars > 5) {
                continue;
            }

            $rating->scores()->create(['criteria_id' => $criteriaId, 'stars' => $stars]);
        }

        $rating->load(self::EAGER);
        self::logActivity("Rated {$ratee->full_name}", 'Ratings', $rating->rating_id, $userId);

        return ['success' => true, 'errors' => [], 'rating' => $rating];
    }

    /**
     * Every rating a user has RECEIVED, newest first.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function receivedBy(int $destUserId): array
    {
        return Rating::with(self::EAGER)
            ->where('dest_user_id', $destUserId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Rating $r) => self::toRow($r))
            ->all();
    }

    /**
     * Every rating a user has GIVEN, newest first (legacy's "Rated By Me" tab).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function givenBy(int $origUserId): array
    {
        return Rating::with(self::EAGER)
            ->where('orig_user_id', $origUserId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Rating $r) => self::toRow($r, true))
            ->all();
    }

    public static function totalCount(): int
    {
        return Rating::count();
    }

    private static function toRow(Rating $r, bool $showRatee = false): array
    {
        $person = $showRatee ? $r->ratee : $r->rater;

        return [
            'rating_id' => $r->rating_id,
            'person_name' => $person->full_name ?? 'User',
            'person_initial' => strtoupper(substr($person->full_name ?? 'U', 0, 1)),
            'person_avatar' => $person->avatar_url ?? null,
            'user_type_id' => $r->dest_user_type_id,
            'user_type_name' => UserTypesController::label($r->dest_user_type_id),
            'city' => $r->dest_city,
            'region_name' => $r->region->region ?? null,
            'country_name' => $r->country->country ?? null,
            'comment' => $r->comment,
            'scores' => $r->scores->map(fn ($s) => [
                'criteria' => $s->criterion->criteria ?? '',
                'stars' => $s->stars,
            ])->all(),
            'created_at' => $r->created_at?->diffForHumans(),
        ];
    }
}

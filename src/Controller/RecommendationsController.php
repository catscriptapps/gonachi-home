<?php
// /src/Controller/RecommendationsController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Recommendation;
use App\Models\User;
use App\Traits\RecentActivityLogger;

/**
 * Owns the Real Estate World Recommendations module — ported from the
 * legacy gonachi-old platform's standalone /recommend/ app. A user
 * recommends ANOTHER user (in a given role + location) to an AUDIENCE
 * GROUP — e.g. "I recommend this contractor to Landlords" — with a
 * free-text comment. No star/criteria scoring here (Recommendations never
 * had that in gonachi-old, unlike Ratings).
 *
 * gonachi-old's schema also carried an "Individual" targeting mode
 * (recommend to one specific named person) alongside "User Groups", but
 * the live app's UI only ever exposed "User Groups" and its submit handler
 * hard-forced the individual-target field to empty regardless of input —
 * that mode was dead code there, not a real feature, so it isn't
 * resurrected here.
 *
 * Same deliberate deviations as RatingsController: login is required
 * server-side (legacy's live app didn't enforce this), no moderation, no
 * self-service delete, duplicates allowed — all matching legacy's actual
 * live behavior otherwise.
 */
class RecommendationsController
{
    use RecentActivityLogger;

    private const EAGER = ['recommender.country', 'recommender.region', 'recommended', 'country', 'region'];

    /**
     * @return array{success: bool, errors: string[], recommendation: ?Recommendation}
     */
    public static function submit(array $input, int $userId): array
    {
        $destUserId = (int) ($input['dest_user_id'] ?? 0);
        $userTypeId = (int) ($input['dest_user_type_id'] ?? 0);
        $recUserTypeId = (int) ($input['rec_user_type_id'] ?? 0);
        $comment = trim((string) ($input['comment'] ?? ''));

        if (!$destUserId || !$userTypeId || !$recUserTypeId || $comment === '') {
            return ['success' => false, 'errors' => ['A user, a role, an audience, and a comment are required.'], 'recommendation' => null];
        }

        if ($destUserId === $userId) {
            return ['success' => false, 'errors' => ['You cannot recommend yourself.'], 'recommendation' => null];
        }

        $recommended = User::find($destUserId);
        if (!$recommended) {
            return ['success' => false, 'errors' => ['User not found.'], 'recommendation' => null];
        }

        $recommendation = Recommendation::create([
            'orig_user_id' => $userId,
            'dest_user_id' => $destUserId,
            'dest_country_id' => (int) ($input['country_id'] ?? 0) ?: $recommended->country_id,
            'dest_region_id' => (int) ($input['region_id'] ?? 0) ?: $recommended->region_id,
            'dest_city' => trim((string) ($input['city'] ?? '')) ?: $recommended->city,
            'dest_user_type_id' => $userTypeId,
            'rec_user_type_id' => $recUserTypeId,
            'comment' => $comment,
        ]);

        $recommendation->load(self::EAGER);
        self::logActivity("Recommended {$recommended->full_name}", 'Recommendations', $recommendation->recommend_id, $userId);

        return ['success' => true, 'errors' => [], 'recommendation' => $recommendation];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function receivedBy(int $destUserId): array
    {
        return Recommendation::with(self::EAGER)
            ->where('dest_user_id', $destUserId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Recommendation $r) => self::toRow($r))
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function givenBy(int $origUserId): array
    {
        return Recommendation::with(self::EAGER)
            ->where('orig_user_id', $origUserId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Recommendation $r) => self::toRow($r, true))
            ->all();
    }

    public static function totalCount(): int
    {
        return Recommendation::count();
    }

    private static function toRow(Recommendation $r, bool $showRecommended = false): array
    {
        $person = $showRecommended ? $r->recommended : $r->recommender;

        return [
            'recommend_id' => $r->recommend_id,
            'person_name' => $person->full_name ?? 'User',
            'person_initial' => strtoupper(substr($person->full_name ?? 'U', 0, 1)),
            'person_avatar' => $person->avatar_url ?? null,
            'user_type_id' => $r->dest_user_type_id,
            'user_type_name' => UserTypesController::label($r->dest_user_type_id),
            'rec_user_type_id' => $r->rec_user_type_id,
            'rec_user_type_name' => UserTypesController::label($r->rec_user_type_id),
            'city' => $r->dest_city,
            'region_name' => $r->region->region ?? null,
            'country_name' => $r->country->country ?? null,
            'comment' => $r->comment,
            'created_at' => $r->created_at?->diffForHumans(),
        ];
    }
}

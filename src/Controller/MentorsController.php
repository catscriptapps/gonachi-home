<?php
// /src/Controller/MentorsController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Mentor;
use App\Traits\RecentActivityLogger;
use App\Utils\IdEncoder;

/**
 * Owns the Real Estate World Mentors module — ported from the legacy
 * gonachi/ platform (Src\Controller\MentorsController). Same shape as
 * legacy: any logged-in user can register as a mentor instantly (no
 * approval workflow — the vestigial `status_id` column legacy never
 * actually checks anywhere is dropped here), everyone's active mentor
 * profiles show in one shared directory (including your own, with
 * edit/delete overlaid), and there's no separate "my mentors" page — that
 * matches legacy exactly.
 *
 * Legacy's mentorship request handshake runs entirely through its
 * Notification system, which gonachi-home doesn't have — so that handshake
 * is surfaced as a "Requests" list directly inside the mentor's own view
 * modal instead (see MentorRequestsController).
 */
class MentorsController
{
    use RecentActivityLogger;

    private const EAGER = ['user.country', 'user.region', 'country', 'region', 'stakeholderType'];

    /**
     * The shared directory feed — every active mentor, optional search +
     * target-type filter. No "mine vs all" split, matching legacy.
     *
     * @return array{html: string, count: int}
     */
    public static function browse(?string $search, ?int $targetType, int $viewerId): array
    {
        $query = Mentor::with(self::EAGER)->where('is_active', true);

        if ($search) {
            $term = '%' . trim($search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('headline', 'like', $term)
                    ->orWhere('bio', 'like', $term)
                    ->orWhere('skills', 'like', $term)
                    ->orWhere('city', 'like', $term)
                    ->orWhereHas('country', fn ($r) => $r->where('country', 'like', $term))
                    ->orWhereHas('region', fn ($r) => $r->where('region', 'like', $term));
            });
        }

        if ($targetType) {
            $query->where('target_stakeholder_type_id', $targetType);
        }

        $mentors = $query->orderByDesc('created_at')->get();

        $html = '';
        foreach ($mentors as $mentor) {
            $html .= self::renderCard($mentor, $viewerId);
        }

        return ['html' => $html, 'count' => $mentors->count()];
    }

    public static function totalActiveCount(): int
    {
        return Mentor::where('is_active', true)->count();
    }

    /**
     * Create (no encoded_id) or update (owner-only) a mentor profile.
     *
     * @return array{success: bool, errors: string[], mentor: ?Mentor}
     */
    public static function save(array $input, int $userId): array
    {
        $headline = trim((string) ($input['headline'] ?? ''));
        if ($headline === '') {
            return ['success' => false, 'errors' => ['A headline is required.'], 'mentor' => null];
        }

        $encodedId = (string) ($input['encoded_id'] ?? '');
        $id = $encodedId !== '' ? self::decodeId($encodedId) : null;

        $skillsRaw = $input['skills'] ?? [];
        $skillsArray = is_array($skillsRaw) ? $skillsRaw : explode(',', (string) $skillsRaw);
        $skills = array_values(array_filter(array_map('trim', $skillsArray), fn ($s) => $s !== ''));

        $data = [
            'headline' => $headline,
            'bio' => trim((string) ($input['bio'] ?? '')),
            'skills' => $skills,
            'years_experience' => (int) ($input['years_experience'] ?? 0),
            'target_stakeholder_type_id' => (int) ($input['target_stakeholder_type_id'] ?? 0) ?: null,
            'country_id' => (int) ($input['country_id'] ?? 0) ?: null,
            'region_id' => (int) ($input['region_id'] ?? 0) ?: null,
            'city' => trim((string) ($input['city'] ?? '')) ?: null,
            'youtube_url' => trim((string) ($input['youtube_url'] ?? '')) ?: null,
            'website_url' => trim((string) ($input['website_url'] ?? '')) ?: null,
        ];

        if ($id) {
            $mentor = Mentor::where('id', $id)->where('orig_user_id', $userId)->first();
            if (!$mentor) {
                return ['success' => false, 'errors' => ['You can only edit your own mentor profile.'], 'mentor' => null];
            }
            $mentor->update($data);
        } else {
            $data['orig_user_id'] = $userId;
            $data['is_active'] = true;
            $mentor = Mentor::create($data);
        }

        $mentor->load(self::EAGER);
        $actionLabel = $id ? 'Updated mentor profile' : 'Registered as a mentor';
        self::logActivity("{$actionLabel}: {$mentor->headline}", 'Mentor', $mentor->id, $userId);

        return ['success' => true, 'errors' => [], 'mentor' => $mentor];
    }

    public static function delete(string $encodedId, int $userId): array
    {
        $id = self::decodeId($encodedId);
        $mentor = $id ? Mentor::where('id', $id)->where('orig_user_id', $userId)->first() : null;

        if (!$mentor) {
            return ['success' => false, 'message' => 'Mentor profile not found, or not yours to delete.'];
        }

        $headline = $mentor->headline;
        $mentor->delete();
        self::logActivity("Deleted mentor profile: {$headline}", 'Mentor', $id, $userId);

        return ['success' => true];
    }

    public static function renderCard(Mentor $mentor, int $viewerId): string
    {
        $data = self::buildItemArray($mentor, $viewerId);
        $assetBase = getAssetBase();

        ob_start();
        include __DIR__ . '/../../resources/views/components/mentors/data-card.php';
        return ob_get_clean() ?: '';
    }

    private static function buildItemArray(Mentor $mentor, int $viewerId): array
    {
        $owner = $mentor->user;

        return [
            'encoded_id' => IdEncoder::encode((int) $mentor->id),
            'id' => $mentor->id,
            'headline' => $mentor->headline,
            'bio' => $mentor->bio,
            'skills' => $mentor->skills ?: [],
            'years_experience' => $mentor->years_experience,
            'city' => $mentor->city,
            'country_id' => $mentor->country_id,
            'country_name' => $mentor->country->country ?? 'N/A',
            'region_id' => $mentor->region_id,
            'region_name' => $mentor->region->region ?? 'N/A',
            'target_stakeholder_type_id' => $mentor->target_stakeholder_type_id,
            'target_stakeholder_type_name' => $mentor->stakeholderType->name ?? 'Expert',
            'youtube_url' => $mentor->youtube_url,
            'website_url' => $mentor->website_url,
            'is_active' => $mentor->is_active,
            'created_at' => $mentor->created_at,
            'updated_at' => $mentor->updated_at,
            'owner_id' => (int) $mentor->orig_user_id,
            'owner_name' => $owner->full_name ?? 'User',
            'owner_avatar' => $owner->avatar_url ?? null,
            'owner_initial' => strtoupper(substr($owner->full_name ?? 'U', 0, 1)),
            'owner_location' => $owner ? trim(($owner->city ?: 'Remote') . ', ' . ($owner->country->country ?? '')) : 'Unknown',
            'is_card_owner' => (int) $mentor->orig_user_id === $viewerId,
        ];
    }

    private static function decodeId(string $raw): ?int
    {
        if ($raw === '') {
            return null;
        }

        if (ctype_digit($raw)) {
            return (int) $raw;
        }

        return IdEncoder::decode($raw);
    }
}

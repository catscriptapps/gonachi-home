<?php
// /src/Controller/AdvertsController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Advert;
use App\Models\User;
use App\Traits\RecentActivityLogger;
use App\Utils\IdEncoder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Owns the Real Estate World Adverts module — ported from the legacy
 * gonachi/ platform (Src\Controller\AdvertsController). Same shape as
 * legacy: new adverts always start 'pending' and only appear on the public
 * "Browse Adverts" feed once an admin approves them; targeting is a
 * client-trusted selected_countries/selected_user_types array (or the
 * literal 'ALL' sentinel) matched in-memory against the viewer, same as
 * legacy and the Social Feed's (since-removed) sponsored-card logic.
 * Package tiers are cosmetic only — no price/payment/duration enforcement,
 * matching legacy exactly.
 */
class AdvertsController
{
    use RecentActivityLogger;

    /**
     * Bumps the view counter unless the viewer is the ad's own owner.
     * Wired into the shared views-increment.php endpoint (type: 'ad').
     */
    public static function incrementView(string $encodedId, int $viewerId): ?int
    {
        $id = self::decodeId($encodedId);
        $advert = $id ? Advert::find($id) : null;

        if (!$advert) {
            return null;
        }

        if ((int) $advert->user_id !== $viewerId) {
            $advert->increment('views');
        }

        return $advert->fresh()->views;
    }

    /**
     * Public "Browse Adverts" feed: active ads only, filtered in-memory by
     * targeting (country + user type) against the viewer — matches legacy
     * exactly (DB can't efficiently query inside a JSON array + 'ALL'
     * sentinel, so legacy loads active ads then filters in PHP, same here).
     */
    public static function browse(?string $search, int $viewerId, int $perPage = 12): LengthAwarePaginator
    {
        $viewer = User::find($viewerId);

        $query = Advert::where('status', Advert::STATUS_ACTIVE)->with(['owner', 'cta', 'package', 'pictures']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('keywords', 'like', "%{$search}%");
            });
        }

        $all = $query->orderByDesc('created_at')->get();
        $matched = $all->filter(fn(Advert $ad) => self::matchesTargeting($ad, $viewer))->values();

        return self::paginateCollection($matched, $perPage);
    }

    /**
     * "My Adverts": the owner's own ads, every status, unfiltered by targeting.
     */
    public static function mine(?string $search, int $userId, int $perPage = 12): LengthAwarePaginator
    {
        $query = Advert::where('user_id', $userId)->with(['owner', 'cta', 'package', 'pictures']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * Admin moderation table: every ad, tab-filtered by status, searchable
     * by title/description/owner name.
     */
    public static function adminList(?string $search, string $tab, int $perPage = 100): LengthAwarePaginator
    {
        $query = Advert::query()->with(['owner', 'cta', 'package', 'pictures']);

        if ($tab !== 'all') {
            $query->where('status', $tab);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('owner', fn($o) => $o->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"));
            });
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * Create (no encoded_id) or update (owner-only) an advert.
     *
     * @return array{success: bool, errors: string[], advert: ?Advert}
     */
    public static function save(array $input, int $userId): array
    {
        $title = trim((string) ($input['title'] ?? ''));
        if ($title === '') {
            return ['success' => false, 'errors' => ['A title is required.'], 'advert' => null];
        }

        $encodedId = (string) ($input['encoded_id'] ?? '');
        $id = $encodedId !== '' ? self::decodeId($encodedId) : null;

        $data = [
            'title' => $title,
            'description' => trim((string) ($input['description'] ?? '')),
            'cta_id' => !empty($input['call_to_action_id']) ? (int) $input['call_to_action_id'] : null,
            'keywords' => trim((string) ($input['keywords'] ?? '')) ?: null,
            'landing_page_url' => trim((string) ($input['landing_page_url'] ?? '')) ?: null,
            'selected_countries' => self::normalizeTargetArray($input['selected_countries'] ?? []),
            'selected_user_types' => self::normalizeTargetArray($input['selected_user_types'] ?? []),
            'package_id' => !empty($input['advert_package']) ? (int) $input['advert_package'] : Advert::PACKAGE_FREE,
        ];

        if ($id) {
            $advert = Advert::where('id', $id)->where('user_id', $userId)->first();
            if (!$advert) {
                return ['success' => false, 'errors' => ["You can only edit your own adverts."], 'advert' => null];
            }
            $advert->update($data);
        } else {
            $data['user_id'] = $userId;
            $data['status'] = Advert::STATUS_PENDING;
            $data['views'] = 0;
            $advert = Advert::create($data);
        }

        $advert->load(['owner', 'cta', 'package', 'pictures']);

        return ['success' => true, 'errors' => [], 'advert' => $advert];
    }

    /**
     * Owner-only. Cascades to delete pictures (DB rows + files, via model events).
     */
    public static function delete(string $encodedId, int $userId): array
    {
        $id = self::decodeId($encodedId);
        $advert = $id ? Advert::where('id', $id)->where('user_id', $userId)->first() : null;

        if (!$advert) {
            return ['success' => false, 'message' => 'Advert not found, or not yours to delete.'];
        }

        $advert->delete();

        return ['success' => true];
    }

    /**
     * Owner-only. At most one video per advert — this always replaces
     * whichever video is already attached (deleting its file first), which
     * is what enforces the "1 video" cap rather than a separate count check.
     */
    public static function attachVideo(string $encodedId, int $userId, string $fileName): array
    {
        $id = self::decodeId($encodedId);
        $advert = $id ? Advert::where('id', $id)->where('user_id', $userId)->first() : null;

        if (!$advert) {
            return ['success' => false, 'message' => 'Advert not found, or not yours to manage.'];
        }

        if ($advert->video_name) {
            $oldPath = __DIR__ . '/../../public/videos/adverts/' . basename($advert->video_name);
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        $advert->video_name = $fileName;
        $advert->save();

        return ['success' => true, 'advert' => $advert];
    }

    /**
     * Owner-only.
     */
    public static function removeVideo(string $encodedId, int $userId): array
    {
        $id = self::decodeId($encodedId);
        $advert = $id ? Advert::where('id', $id)->where('user_id', $userId)->first() : null;

        if (!$advert) {
            return ['success' => false, 'message' => 'Advert not found, or not yours to manage.'];
        }

        if ($advert->video_name) {
            $path = __DIR__ . '/../../public/videos/adverts/' . basename($advert->video_name);
            if (file_exists($path)) {
                @unlink($path);
            }
        }

        $advert->video_name = null;
        $advert->save();

        return ['success' => true];
    }

    /**
     * Admin-only moderation action: approve (-> active), deactivate (-> inactive),
     * or reject (-> rejected).
     */
    public static function updateStatus(string $encodedId, string $status, int $adminUserId): array
    {
        if (!in_array($status, [Advert::STATUS_ACTIVE, Advert::STATUS_INACTIVE, Advert::STATUS_REJECTED], true)) {
            return ['success' => false, 'message' => 'Invalid status.'];
        }

        $id = self::decodeId($encodedId);
        $advert = $id ? Advert::with(['owner', 'cta', 'package', 'pictures'])->find($id) : null;

        if (!$advert) {
            return ['success' => false, 'message' => 'Advert not found.'];
        }

        $previousStatus = $advert->status;
        $advert->status = $status;
        $advert->save();

        // Legacy also sends the owner a notification here — gonachi-home
        // doesn't have a working Notification model/table yet (the
        // /notifications page already references one that doesn't exist),
        // so that's intentionally left out; activity logging covers the
        // audit trail in the meantime.
        self::logActivity(
            "Advert status changed from {$previousStatus} to {$status}",
            'Advert',
            $advert->id,
            $adminUserId
        );

        return ['success' => true, 'advert' => $advert, 'status' => $status];
    }

    /**
     * Renders one advert card (public browse / my-adverts grid).
     */
    public static function renderCard(Advert $advert, int $viewerId): string
    {
        $data = self::buildItemArray($advert, $viewerId);
        $assetBase = getAssetBase();
        $isCardOwner = (int) $advert->user_id === $viewerId;

        ob_start();
        include __DIR__ . '/../../resources/views/components/adverts/data-card.php';
        return ob_get_clean() ?: '';
    }

    /**
     * Renders one admin table row.
     */
    public static function renderAdminRow(Advert $advert): string
    {
        $data = self::buildItemArray($advert, 0);
        $assetBase = getAssetBase();

        ob_start();
        include __DIR__ . '/../../resources/views/components/adverts/data-row.php';
        return ob_get_clean() ?: '';
    }

    public static function totalActiveCount(): int
    {
        return Advert::where('status', Advert::STATUS_ACTIVE)->count();
    }

    /**
     * True if the ad's targeting allows the given viewer to see it —
     * 'ALL' (or an empty/missing array) means untargeted; otherwise the
     * viewer's own country_id / user_type_ids must intersect.
     */
    private static function matchesTargeting(Advert $advert, ?User $viewer): bool
    {
        if (!$viewer) {
            return false;
        }

        $countries = $advert->selected_countries ?: ['ALL'];
        $countryOk = in_array('ALL', $countries, true) || in_array((string) $viewer->country_id, $countries, true);

        $userTypes = $advert->selected_user_types ?: ['ALL'];
        $viewerTypes = array_map('strval', $viewer->user_type_ids ?? []);
        $typesOk = in_array('ALL', $userTypes, true) || count(array_intersect(array_map('strval', $userTypes), $viewerTypes)) > 0;

        return $countryOk && $typesOk;
    }

    /**
     * @param mixed $raw Either the literal ['ALL'] sentinel or an array of
     *                    country/user-type IDs from the targeting picker UI.
     * @return string[]
     */
    private static function normalizeTargetArray($raw): array
    {
        if (!is_array($raw) || $raw === []) {
            return ['ALL'];
        }

        return array_values(array_map('strval', $raw));
    }

    /**
     * @return array<string, mixed> Everything data-card.php / data-row.php /
     *                              the view-advert modal's data-* payload need.
     */
    private static function buildItemArray(Advert $advert, int $viewerId): array
    {
        $owner = $advert->owner;
        $countries = $advert->selected_countries ?: ['ALL'];
        $userTypes = $advert->selected_user_types ?: ['ALL'];

        return [
            'encoded_id' => IdEncoder::encode((int) $advert->id),
            'title' => $advert->title,
            'description' => $advert->description,
            'keywords' => $advert->keywords,
            'landing_page_url' => $advert->landing_page_url,
            'cta_id' => $advert->cta_id,
            'cta_text' => $advert->cta->label ?? 'Learn More',
            'selected_countries' => $countries,
            'country_names' => self::countryNames($countries),
            'selected_user_types' => $userTypes,
            'user_type_names' => self::userTypeNames($userTypes),
            'package_id' => $advert->package_id,
            'package_name' => $advert->package->package_name ?? 'Free',
            'package_description' => $advert->package->package_description ?? '',
            'package_icon' => $advert->package->package_icon ?? '',
            'status' => $advert->status,
            'views' => $advert->views,
            'created_at' => $advert->created_at,
            'updated_at' => $advert->updated_at,
            'thumbnail' => $advert->pictures->first()->pic_name ?? null,
            'pictures_count' => $advert->pictures->count(),
            'video_name' => $advert->video_name,
            'owner_id' => (int) $advert->user_id,
            'owner_name' => $owner->full_name ?? 'User',
            'owner_avatar' => $owner->avatar_url ?? null,
            'owner_initial' => strtoupper(substr($owner->full_name ?? 'U', 0, 1)),
            'owner_location' => $owner ? trim(($owner->city ?: 'Remote') . ', ' . ($owner->country->country ?? '')) : 'Unknown',
            'owner_user_types' => $owner->user_type_ids ?? [],
            'is_card_owner' => (int) $advert->user_id === $viewerId,
        ];
    }

    private static function countryNames(array $ids): array
    {
        if (in_array('ALL', $ids, true)) {
            return ['All Countries'];
        }

        return \App\Models\Country::whereIn('id', $ids)->pluck('country')->all();
    }

    private static function userTypeNames(array $ids): array
    {
        if (in_array('ALL', $ids, true)) {
            return ['All User Types'];
        }

        $all = \Src\Controller\UserTypesController::list();
        $lookup = [];
        foreach ($all as $t) {
            $lookup[(string) $t->user_type_id] = $t->user_type;
        }

        return array_values(array_filter(array_map(fn($id) => $lookup[(string) $id] ?? null, $ids)));
    }

    private static function paginateCollection($collection, int $perPage): LengthAwarePaginator
    {
        $page = (int) (($_GET['page'] ?? 1));
        $page = $page > 0 ? $page : 1;

        return new LengthAwarePaginator(
            $collection->forPage($page, $perPage)->values(),
            $collection->count(),
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );
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

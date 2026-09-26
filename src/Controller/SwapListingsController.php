<?php
// /src/Controller/SwapListingsController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\SwapListing;
use App\Models\SwapListingCategory;
use App\Models\SwapListingPic;
use App\Models\SwapSavedListing;
use App\Traits\RecentActivityLogger;
use App\Utils\IdEncoder;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Pagination\LengthAwarePaginator;
use Src\Service\PictureOrderService;

/**
 * SwapListingsController
 * Read side ported from the standalone gonachi-swap app's
 * ListingsController::index(); the CRUD side (save/delete/setStatus/
 * renderCard) mirrors Real Estate World's own ListingsController
 * (src/Controller/ListingsController.php, rew_listings) — same
 * modal-based add/edit, card-embedded edit/delete, IdEncoder-obfuscated
 * ids, and owner-scoped `where('user_id', $userId)` permission model —
 * simplified to Swap Marketplace's own (much smaller) field set. Named distinctly to
 * avoid colliding with that class/its rew_ tables.
 */
class SwapListingsController
{
    use RecentActivityLogger;

    /**
     * Only URLs under this path are trusted when attaching photos to a
     * listing — guards against a crafted payload smuggling in an external
     * URL. See swap-listing-photo-upload.php, the only writer of this path.
     */
    private const ALLOWED_UPLOAD_PATH = 'images/uploads/swap-listings/';

    /** Relative to public/ — where swap-listing-upload-video.php writes files. */
    private const VIDEO_UPLOAD_PATH = 'videos/swap-listings/';

    public const TYPE_LABELS = [
        'swap' => 'Swap',
        'sale' => 'Sale',
        'gift' => 'Gift',
    ];

    public const CONDITION_LABELS = [
        'new' => 'New',
        'like_new' => 'Like New',
        'used' => 'Used',
        'parts' => 'For Parts',
    ];

    /**
     * Browse posted listings, optionally filtered by free-text search,
     * category slug, and listing type.
     */
    public static function browse(?string $search, ?string $categorySlug, ?string $type, int $perPage = 12): LengthAwarePaginator
    {
        $query = SwapListing::posted()->with(['category', 'pictures', 'user']);

        if ($search) {
            $needle = trim($search);
            $query->where(function ($q) use ($needle) {
                $q->where('title', 'like', "%{$needle}%")
                    ->orWhere('description', 'like', "%{$needle}%")
                    ->orWhere('city', 'like', "%{$needle}%");
            });
        }

        if ($categorySlug) {
            $query->whereHas('category', fn($q) => $q->where('slug', $categorySlug));
        }

        if ($type && isset(self::TYPE_LABELS[$type])) {
            $query->where('listing_type', $type);
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * The signed-in user's own listings, every status — powers /my-swap-listings.
     */
    public static function mine(int $userId, ?string $search, int $perPage = 12): LengthAwarePaginator
    {
        $query = SwapListing::where('user_id', $userId)->with(['category', 'pictures', 'user']);

        if ($search) {
            $needle = trim($search);
            $query->where(function ($q) use ($needle) {
                $q->where('title', 'like', "%{$needle}%")->orWhere('city', 'like', "%{$needle}%");
            });
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * Listings the signed-in user has bookmarked — powers /saved-swap-listings.
     */
    public static function saved(int $userId, int $perPage = 12): LengthAwarePaginator
    {
        // No status filter — a bookmark stays active/visible even after the
        // listing is later marked completed, rather than silently
        // disappearing. The card itself already renders a "Completed" badge
        // in that case (see data-card.php), so the saver still gets a clear
        // signal it's no longer available without losing their bookmark.
        $listingIds = SwapSavedListing::where('user_id', $userId)->pluck('listing_id');

        return SwapListing::whereIn('id', $listingIds)
            ->with(['category', 'pictures', 'user'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public static function totalListings(): int
    {
        return SwapListing::posted()->count();
    }

    /**
     * Active categories with a posted-listing count, ordered by name.
     *
     * @return \Illuminate\Support\Collection<int, SwapListingCategory>
     */
    public static function categories()
    {
        return SwapListingCategory::withCount(['listings as posted_listings_count' => fn($q) => $q->posted()])
            ->orderBy('name')
            ->get();
    }

    /**
     * Create or update a listing (encoded_id present + resolvable + owned
     * by $userId => update; otherwise create).
     *
     * @param array $input title, description, category_id, listing_type,
     *                      condition, price, trade_pref, city,
     *                      photo_urls[] (already-uploaded URLs — the full
     *                      desired photo set; replaces whatever the
     *                      listing had before, matching how the edit
     *                      modal is prefilled straight from the card's own
     *                      data-photos attribute with no separate fetch).
     * @return array{success: bool, errors: string[], listing: ?SwapListing}
     */
    public static function save(array $input, int $userId): array
    {
        $title = trim((string) ($input['title'] ?? ''));
        if ($title === '') {
            return ['success' => false, 'errors' => ['A listing title is required.'], 'listing' => null];
        }

        $encodedId = trim((string) ($input['encoded_id'] ?? ''));
        $listing = $encodedId !== '' ? self::ownedListing($encodedId, $userId) : null;

        if ($encodedId !== '' && !$listing) {
            return ['success' => false, 'errors' => ['Listing not found, or not yours to edit.'], 'listing' => null];
        }

        $listingType = (string) ($input['listing_type'] ?? 'swap');
        if (!isset(self::TYPE_LABELS[$listingType])) {
            $listingType = 'swap';
        }

        $condition = (string) ($input['condition'] ?? 'used');
        if (!isset(self::CONDITION_LABELS[$condition])) {
            $condition = 'used';
        }

        $categoryId = (int) ($input['category_id'] ?? 0) ?: null;
        $price = $listingType === 'sale' && is_numeric($input['price'] ?? null) ? (float) $input['price'] : null;
        $tradePref = $listingType === 'swap' ? (trim((string) ($input['trade_pref'] ?? '')) ?: null) : null;

        $attributes = [
            'category_id' => $categoryId,
            'title' => $title,
            'description' => trim((string) ($input['description'] ?? '')) ?: null,
            'listing_type' => $listingType,
            'condition' => $condition,
            'price' => $price,
            'trade_pref' => $tradePref,
            'city' => trim((string) ($input['city'] ?? '')) ?: null,
        ];

        if ($listing) {
            $listing->update($attributes);
            $actionLabel = 'Updated listing';
        } else {
            $listing = SwapListing::create($attributes + ['user_id' => $userId, 'status' => 'posted', 'views' => 0]);
            $actionLabel = 'Posted new listing';
        }

        if (array_key_exists('photo_urls', $input) && is_array($input['photo_urls'])) {
            self::replacePhotos($listing, $input['photo_urls']);
        }

        $listing->load(['category', 'pictures', 'user']);
        self::logActivity("{$actionLabel}: {$listing->title}", 'SwapListing', $listing->id, $userId);

        return ['success' => true, 'errors' => [], 'listing' => $listing];
    }

    /**
     * @return array{success: bool, message?: string}
     */
    public static function delete(string $encodedId, int $userId): array
    {
        $listing = self::ownedListing($encodedId, $userId);

        if (!$listing) {
            return ['success' => false, 'message' => 'Listing not found, or not yours to delete.'];
        }

        $title = $listing->title;
        $listing->delete(); // cascades pictures via SwapListing::booted()

        self::logActivity("Deleted listing: {$title}", 'SwapListing', null, $userId);

        return ['success' => true];
    }

    /**
     * Toggle between 'posted' and 'completed' — the only two states this
     * MVP exposes (draft/archived exist in the schema for later, not
     * wired to any UI yet).
     *
     * @return array{success: bool, message?: string, listing?: SwapListing}
     */
    public static function setStatus(string $encodedId, string $status, int $userId): array
    {
        if (!in_array($status, ['posted', 'completed'], true)) {
            return ['success' => false, 'message' => 'Invalid status.'];
        }

        $listing = self::ownedListing($encodedId, $userId);

        if (!$listing) {
            return ['success' => false, 'message' => 'Listing not found, or not yours to update.'];
        }

        $listing->status = $status;
        $listing->save();
        $listing->load(['category', 'pictures', 'user']);

        self::logActivity(
            ($status === 'completed' ? 'Marked listing as completed: ' : 'Reactivated listing: ') . $listing->title,
            'SwapListing',
            $listing->id,
            $userId
        );

        return ['success' => true, 'listing' => $listing];
    }

    /**
     * @return array{success: bool, saved: bool}
     */
    public static function toggleSave(int $listingId, int $userId): array
    {
        $existing = SwapSavedListing::where('user_id', $userId)->where('listing_id', $listingId)->first();

        if ($existing) {
            $existing->delete();
            return ['success' => true, 'saved' => false];
        }

        SwapSavedListing::create(['user_id' => $userId, 'listing_id' => $listingId]);
        return ['success' => true, 'saved' => true];
    }

    /**
     * Attaches an uploaded video to a listing — max one at a time, mirroring
     * Real Estate World's Quotation::attachVideo(): always replaces
     * whichever video already existed (unlinking its file first) rather
     * than enforcing a count.
     *
     * @return array{success: bool, message?: string, listing?: SwapListing}
     */
    public static function attachVideo(string $encodedId, int $userId, string $fileName): array
    {
        $listing = self::ownedListing($encodedId, $userId);

        if (!$listing) {
            return ['success' => false, 'message' => 'Listing not found, or not yours to manage.'];
        }

        if ($listing->video_name) {
            $oldPath = dirname(__DIR__, 2) . '/public/' . self::VIDEO_UPLOAD_PATH . basename($listing->video_name);
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        $listing->video_name = $fileName;
        $listing->save();
        $listing->load(['category', 'pictures', 'user']);

        self::logActivity("Added video to listing: {$listing->title}", 'SwapListing', $listing->id, $userId);

        return ['success' => true, 'listing' => $listing];
    }

    /**
     * @return array{success: bool, message?: string, listing?: SwapListing}
     */
    /**
     * @param int[] $order Picture ids in the desired order.
     * @return array{success: bool, message?: string, listing?: SwapListing}
     */
    public static function reorderPhotos(string $encodedId, array $order, int $userId): array
    {
        $listing = self::ownedListing($encodedId, $userId);

        if (!$listing) {
            return ['success' => false, 'message' => 'Listing not found, or not yours to manage.'];
        }

        if (!PictureOrderService::apply($listing->pictures()->get(), $order, 'id', 'position')) {
            return ['success' => false, 'message' => 'That photo order is out of date — please reopen the listing and try again.'];
        }

        $listing->load(['category', 'pictures', 'user']);

        return ['success' => true, 'listing' => $listing];
    }

    /**
     * Removes one photo (DB row + file, via SwapListingPic's deleting hook)
     * from a listing the caller owns, and closes the position gap so the
     * remaining photos stay contiguous.
     *
     * @return array{success: bool, message?: string, listing?: SwapListing}
     */
    public static function deletePhoto(int $picId, int $userId): array
    {
        $pic = SwapListingPic::with('listing')->find($picId);

        if (!$pic || !$pic->listing || (int) $pic->listing->user_id !== $userId) {
            return ['success' => false, 'message' => 'Photo not found, or not yours to manage.'];
        }

        $listing = $pic->listing;
        $pic->delete();

        $listing->pictures()->get()->values()->each(function (SwapListingPic $remaining, int $position) {
            if ((int) $remaining->position !== $position) {
                $remaining->position = $position;
                $remaining->save();
            }
        });

        $listing->load(['category', 'pictures', 'user']);

        return ['success' => true, 'listing' => $listing];
    }

    public static function removeVideo(string $encodedId, int $userId): array
    {
        $listing = self::ownedListing($encodedId, $userId);

        if (!$listing) {
            return ['success' => false, 'message' => 'Listing not found, or not yours to manage.'];
        }

        if ($listing->video_name) {
            $path = dirname(__DIR__, 2) . '/public/' . self::VIDEO_UPLOAD_PATH . basename($listing->video_name);
            if (is_file($path)) {
                @unlink($path);
            }
        }

        $listing->video_name = null;
        $listing->save();
        $listing->load(['category', 'pictures', 'user']);

        return ['success' => true, 'listing' => $listing];
    }

    /**
     * Renders the shared card partial for a single listing — used both by
     * page views (iterating a paginator) and by the API's create/update/
     * status-toggle JSON responses (so the grid updates in place, no full
     * page reload).
     */
    public static function renderCard(SwapListing $listing, ?int $viewerId): string
    {
        $data = self::cardData($listing, $viewerId);
        $assetBase = getAssetBase();

        ob_start();
        include __DIR__ . '/../../resources/views/components/swap-listings/data-card.php';
        return ob_get_clean();
    }

    /**
     * @return array<string, mixed>
     */
    private static function cardData(SwapListing $listing, ?int $viewerId): array
    {
        $assetBase = getAssetBase();
        $thumbnail = $listing->pictures->first();

        $isSaved = $viewerId
            ? SwapSavedListing::where('user_id', $viewerId)->where('listing_id', $listing->id)->exists()
            : false;

        $owner = $listing->user;

        return [
            'encoded_id' => IdEncoder::encode($listing->id),
            'title' => $listing->title,
            'description' => $listing->description,
            'category_id' => $listing->category_id,
            'category_name' => $listing->category->name ?? null,
            'listing_type' => $listing->listing_type,
            'type_label' => self::TYPE_LABELS[$listing->listing_type] ?? ucfirst($listing->listing_type),
            'condition' => $listing->condition,
            'condition_label' => self::CONDITION_LABELS[$listing->condition] ?? ucfirst($listing->condition),
            'price' => $listing->price,
            'trade_pref' => $listing->trade_pref,
            'city' => $listing->city,
            'status' => $listing->status,
            'views' => (int) $listing->views,
            'thumbnail' => $thumbnail ? $assetBase . $thumbnail->file_path : null,
            'photos' => $listing->pictures->map(fn(SwapListingPic $pic) => [
                'id' => $pic->id,
                'url' => $assetBase . $pic->file_path,
            ])->values()->all(),
            'video_url' => $listing->video_name ? $assetBase . self::VIDEO_UPLOAD_PATH . $listing->video_name : null,
            'created_at' => $listing->created_at?->format('M d, Y'),
            'updated_at' => $listing->updated_at?->format('M d, Y'),
            'owner_id' => (int) $listing->user_id,
            'owner_name' => $owner->full_name ?? 'Gonachi Member',
            'owner_avatar' => $owner && !empty($owner->avatar_url) ? $assetBase . 'images/uploads/avatars/' . $owner->avatar_url : null,
            'owner_initial' => $owner && !empty($owner->full_name) ? strtoupper(substr($owner->full_name, 0, 1)) : 'G',
            'owner_location' => $owner->city ?? null,
            'viewer_id' => $viewerId,
            'is_card_owner' => $viewerId !== null && (int) $listing->user_id === $viewerId,
            'is_saved' => $isSaved,
        ];
    }

    /**
     * Reconciles a listing's photos with the submitted (full desired) set
     * — an edit that didn't touch photos resubmits the same URLs it
     * already had, so this must leave those rows (and their files) alone
     * rather than deleting and recreating them: SwapListingPic's deleting
     * hook unlinks the real file, and recreating a row never re-writes
     * it — a naive delete-everything-then-recreate would leave the new
     * row pointing at a file that was just deleted a moment earlier.
     *
     * @param string[] $urls Already-uploaded URLs from swap-listing-photo-upload.php.
     */
    private static function replacePhotos(SwapListing $listing, array $urls): void
    {
        $assetBase = getAssetBase();

        $desiredPaths = [];
        foreach ($urls as $url) {
            $url = trim((string) $url);

            if ($url === '' || !str_starts_with($url, $assetBase)) {
                continue;
            }

            $relative = substr($url, strlen($assetBase));

            if (str_starts_with($relative, self::ALLOWED_UPLOAD_PATH)) {
                $desiredPaths[] = $relative;
            }
        }

        // Server-side enforcement of the same 12-photo cap the compose
        // modal's client-side check uses (getMediaLimit()) — the client
        // check alone isn't authoritative.
        $desiredPaths = array_slice($desiredPaths, 0, getMediaLimit());

        Capsule::connection()->transaction(function () use ($listing, $desiredPaths) {
            $existingPics = $listing->pictures()->get()->keyBy('file_path');
            $keptPaths = [];
            $position = 0;

            foreach ($desiredPaths as $path) {
                $existing = $existingPics->get($path);

                if ($existing && !in_array($path, $keptPaths, true)) {
                    $existing->position = $position++;
                    $existing->save();
                    $keptPaths[] = $path;
                    continue;
                }

                SwapListingPic::create([
                    'listing_id' => $listing->id,
                    'file_path' => $path,
                    'position' => $position++,
                ]);
            }

            // Delete only pics that are no longer wanted — this is what
            // actually unlinks a truly-removed photo's file.
            foreach ($existingPics as $path => $pic) {
                if (!in_array($path, $keptPaths, true)) {
                    $pic->delete();
                }
            }
        });
    }

    private static function ownedListing(string $encodedId, int $userId): ?SwapListing
    {
        $id = self::decodeId($encodedId);
        if (!$id) {
            return null;
        }

        return SwapListing::where('id', $id)->where('user_id', $userId)->first();
    }

    private static function decodeId(string $raw): ?int
    {
        return ctype_digit($raw) ? (int) $raw : IdEncoder::decode($raw);
    }
}

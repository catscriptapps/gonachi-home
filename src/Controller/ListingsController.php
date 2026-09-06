<?php
// /src/Controller/ListingsController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Listing;
use App\Models\ListingResponse;
use App\Models\User;
use App\Traits\RecentActivityLogger;
use App\Utils\IdEncoder;

/**
 * Owns the Real Estate World Listings module — ported from the legacy
 * gonachi/ platform (Src\Controller\ListingsController). Same shape as
 * legacy: a listing is Active or Archived by the owner's own toggle (no
 * admin moderation queue), the public browse feed only ever shows Active
 * ones across every user, and "My Listings" shows the owner's own regardless
 * of status. Listings use a two-level taxonomy (ListingCategory ->
 * ListingCategoryType) — categories 2 ("Real Estate Services") and 3
 * ("Other") are treated as "service" listings, which suppresses every
 * property-specific field (unit/house type, bedrooms/bathrooms, price,
 * agreement type, move-in date, amenities), matching legacy's isService
 * split exactly.
 *
 * Legacy's inquiry/accept/decline handshake runs entirely through its
 * Notification system, which gonachi-home doesn't have. Here the same
 * ListingResponse data and accept/decline logic (ListingResponsesController)
 * is surfaced directly inside the listing's own view modal as an
 * "Inquiries" list instead of a notification bell — matching how Quotations
 * and Mentors were already ported in this codebase.
 *
 * Two intentional deviations from legacy behavior (flagged, not silent):
 *  - legacy's save()/delete() have no ownership check at all (only
 *    deactivate/reactivate do) — here every mutation is ownership-enforced,
 *    matching the pattern already used for Adverts/Quotations/Mentors here.
 *  - legacy's public /listings page's infinite-scroll/search AJAX actually
 *    hits an endpoint that (by default) scopes to the caller's own listings
 *    only, even on the public page — an apparent legacy bug. Here browse()
 *    is properly scoped to all Active listings regardless of who is asking.
 */
class ListingsController
{
    use RecentActivityLogger;

    private const EAGER = [
        'user.country',
        'user.region',
        'category',
        'categoryType',
        'unitType',
        'houseType',
        'bedroom',
        'bathroom',
        'agreementType',
        'country',
        'region',
        'pictures',
    ];

    private const PER_PAGE = 10;

    public static function incrementView(string $encodedId, int $viewerId): ?int
    {
        $id = self::decodeId($encodedId);
        $listing = $id ? Listing::find($id) : null;

        if (!$listing) {
            return null;
        }

        if ((int) $listing->orig_user_id !== $viewerId) {
            $listing->increment('views');
        }

        return $listing->fresh()->views;
    }

    /**
     * Public "Browse Listings" feed — Active listings across every user,
     * paginated for infinite scroll (matches legacy's actual UX for this
     * module, unlike Quotations/Mentors/Adverts which render everything at
     * once here).
     *
     * @return array{html: string, total: int, hasMore: bool}
     */
    public static function browse(?string $search, int $viewerId, int $page = 1): array
    {
        $query = Listing::with(self::EAGER)->where('status_id', Listing::STATUS_ACTIVE);
        self::applySearch($query, $search);

        return self::paginateAndRender($query, $viewerId, $page);
    }

    /**
     * "My Listings": the owner's own listings, every status (active/archived).
     *
     * @return array{html: string, total: int, hasMore: bool}
     */
    public static function mine(?string $search, int $userId, int $page = 1): array
    {
        $query = Listing::with(self::EAGER)->where('orig_user_id', $userId);
        self::applySearch($query, $search);

        return self::paginateAndRender($query, $userId, $page);
    }

    public static function totalActiveCount(): int
    {
        return Listing::where('status_id', Listing::STATUS_ACTIVE)->count();
    }

    /**
     * Create (no encoded_id) or update (owner-only) a listing.
     *
     * @return array{success: bool, errors: string[], listing: ?Listing}
     */
    public static function save(array $input, int $userId): array
    {
        $title = trim((string) ($input['listing_title'] ?? ''));
        if ($title === '') {
            return ['success' => false, 'errors' => ['A listing title is required.'], 'listing' => null];
        }

        $encodedId = (string) ($input['encoded_id'] ?? '');
        $id = $encodedId !== '' ? self::decodeId($encodedId) : null;

        $categoryId = (int) ($input['category_id'] ?? 0) ?: null;
        $isService = in_array($categoryId, [2, 3], true);
        $unitTypeId = $isService ? null : ((int) ($input['unit_type_id'] ?? 0) ?: null);

        $amenitiesRaw = $input['amenities'] ?? [];
        $amenities = $isService ? [] : (is_array($amenitiesRaw) ? array_values(array_map('intval', $amenitiesRaw)) : []);

        $data = [
            'listing_title' => $title,
            'listing_description' => trim((string) ($input['listing_description'] ?? '')),
            'category_id' => $categoryId,
            'category_type_id' => ($categoryId === 3) ? null : ((int) ($input['category_type_id'] ?? 0) ?: null),
            'unit_type_id' => $unitTypeId,
            'house_type_id' => ($unitTypeId === 5) ? ((int) ($input['house_type_id'] ?? 0) ?: null) : null,
            'bedroom_id' => $isService ? null : ((int) ($input['bedroom_id'] ?? 0) ?: null),
            'bathroom_id' => $isService ? null : ((int) ($input['bathroom_id'] ?? 0) ?: null),
            'city' => trim((string) ($input['city'] ?? '')) ?: null,
            'address' => $isService ? null : (trim((string) ($input['address'] ?? '')) ?: null),
            'country_id' => (int) ($input['country_id'] ?? 0) ?: null,
            'region_id' => (int) ($input['region_id'] ?? 0) ?: null,
            'agreement_type_id' => $isService ? null : ((int) ($input['agreement_type_id'] ?? 0) ?: null),
            'price' => $isService ? '0' : (trim((string) ($input['price'] ?? '')) ?: null),
            'property_size' => $isService ? null : (trim((string) ($input['property_size'] ?? '')) ?: null),
            'move_in_date' => $isService ? null : (trim((string) ($input['move_in_date'] ?? '')) ?: null),
            'is_ac' => $isService ? 0 : (int) ($input['is_ac'] ?? 0),
            'is_furnished' => $isService ? 0 : (int) ($input['is_furnished'] ?? 0),
            'parking' => $isService ? 0 : (int) ($input['parking'] ?? 0),
            'pets_allowed' => $isService ? 0 : (int) ($input['pets_allowed'] ?? 0),
            'amenities' => $amenities,
            'youtube_url' => trim((string) ($input['youtube_url'] ?? '')) ?: null,
            'contact_phone' => trim((string) ($input['contact_phone'] ?? '')) ?: null,
        ];

        if ($id) {
            $listing = Listing::where('listing_id', $id)->where('orig_user_id', $userId)->first();
            if (!$listing) {
                return ['success' => false, 'errors' => ['You can only edit your own listings.'], 'listing' => null];
            }
            $listing->update($data);
        } else {
            $data['orig_user_id'] = $userId;
            $data['status_id'] = Listing::STATUS_ACTIVE;
            $data['views'] = 0;
            $listing = Listing::create($data);
        }

        $listing->load(self::EAGER);
        $actionLabel = $id ? 'Updated listing' : 'Posted new listing';
        self::logActivity("{$actionLabel}: {$listing->listing_title}", 'Listings', $listing->listing_id, $userId);

        return ['success' => true, 'errors' => [], 'listing' => $listing];
    }

    /**
     * Owner-only. Cascades to delete pictures (DB rows + files, via model events).
     */
    public static function delete(string $encodedId, int $userId): array
    {
        $id = self::decodeId($encodedId);
        $listing = $id ? Listing::where('listing_id', $id)->where('orig_user_id', $userId)->first() : null;

        if (!$listing) {
            return ['success' => false, 'message' => 'Listing not found, or not yours to delete.'];
        }

        $title = $listing->listing_title;
        $listing->delete();
        self::logActivity("Deleted listing: {$title}", 'Listings', $id, $userId);

        return ['success' => true];
    }

    /**
     * Owner-only toggle between Active and Archived ("End Listing" / "Reactivate Listing").
     */
    public static function setStatus(string $encodedId, int $statusId, int $userId): array
    {
        if (!in_array($statusId, [Listing::STATUS_ACTIVE, Listing::STATUS_ARCHIVED], true)) {
            return ['success' => false, 'message' => 'Invalid status.'];
        }

        $id = self::decodeId($encodedId);
        $listing = $id ? Listing::with(self::EAGER)->where('listing_id', $id)->where('orig_user_id', $userId)->first() : null;

        if (!$listing) {
            return ['success' => false, 'message' => 'Listing not found, or not yours to manage.'];
        }

        $listing->status_id = $statusId;
        $listing->save();

        $verb = $statusId === Listing::STATUS_ACTIVE ? 'Reactivated' : 'Ended';
        self::logActivity("{$verb} listing: {$listing->listing_title}", 'Listings', $listing->listing_id, $userId);

        return ['success' => true, 'listing' => $listing];
    }

    public static function renderCard(Listing $listing, int $viewerId): string
    {
        $data = self::buildItemArray($listing, $viewerId);
        $assetBase = getAssetBase();

        ob_start();
        include __DIR__ . '/../../resources/views/components/listings/data-card.php';
        return ob_get_clean() ?: '';
    }

    /**
     * @return array{html: string, total: int, hasMore: bool}
     */
    private static function paginateAndRender($query, int $viewerId, int $page): array
    {
        $page = max(1, $page);
        $perPage = self::PER_PAGE;
        $offset = ($page - 1) * $perPage;

        $total = (clone $query)->count();
        $listings = $query->orderByDesc('created_at')->offset($offset)->limit($perPage)->get();

        $html = '';
        foreach ($listings as $listing) {
            $html .= self::renderCard($listing, $viewerId);
        }

        return [
            'html' => $html,
            'total' => $total,
            'hasMore' => ($offset + $listings->count()) < $total,
        ];
    }

    private static function applySearch($query, ?string $search): void
    {
        if (!$search) {
            return;
        }

        $term = '%' . trim($search) . '%';
        $query->where(function ($q) use ($term) {
            $q->where('listing_title', 'like', $term)
                ->orWhere('city', 'like', $term)
                ->orWhere('listing_description', 'like', $term)
                ->orWhereHas('category', fn ($r) => $r->where('category', 'like', $term));
        });
    }

    /**
     * @return array<string, mixed> Everything data-card.php / the view modal's
     *                              data-* payload need.
     */
    private static function buildItemArray(Listing $listing, int $viewerId): array
    {
        $owner = $listing->user;
        $firstPic = $listing->pictures->sortBy('pos_index')->first();
        $isOwner = (int) $listing->orig_user_id === $viewerId;

        // Owner: how many inquiries are still awaiting a decision — shown as
        // a badge on the card and a "scroll to review" banner in the modal.
        // Non-owner: their own most recent inquiry's status on this
        // listing, if any — shown in place of "Contact Owner" so they don't
        // have to remember to check back; marking it read is a side effect
        // of ListingResponsesController::myStatusFor() itself.
        $pendingInquiriesCount = 0;
        $myResponseStatus = null;
        $myResponseUnread = false;

        if ($isOwner) {
            $pendingInquiriesCount = ListingResponse::where('listing_id', $listing->listing_id)
                ->where('status', ListingResponse::STATUS_PENDING)
                ->count();
        } elseif ($viewerId) {
            $myStatus = ListingResponsesController::myStatusFor((int) $listing->listing_id, $viewerId);
            if ($myStatus) {
                $myResponseStatus = $myStatus['status'];
                $myResponseUnread = $myStatus['unread'];
            }
        }

        return [
            'listing_id' => $listing->listing_id,
            'encoded_id' => IdEncoder::encode((int) $listing->listing_id),
            'listing_title' => $listing->listing_title,
            'listing_description' => $listing->listing_description,
            'city' => $listing->city,
            'address' => $listing->address,
            'country_id' => $listing->country_id,
            'country_name' => $listing->country->country ?? '',
            'region_id' => $listing->region_id,
            'region_name' => $listing->region->region ?? '',
            'category_id' => $listing->category_id,
            'category_name' => $listing->category->category ?? 'General',
            'category_type_id' => $listing->category_type_id,
            'category_type_name' => $listing->categoryType->category_type ?? '',
            'unit_type_id' => $listing->unit_type_id,
            'unit_type_name' => $listing->unitType->unit_type ?? '',
            'house_type_id' => $listing->house_type_id,
            'house_type_name' => $listing->houseType->house_type ?? '',
            'bedroom_id' => $listing->bedroom_id,
            'bedroom_label' => $listing->bedroom->bedroom ?? '0',
            'bathroom_id' => $listing->bathroom_id,
            'bathroom_label' => $listing->bathroom->bathroom ?? '0',
            'property_size' => $listing->property_size,
            'is_ac' => (int) $listing->is_ac,
            'is_furnished' => (int) $listing->is_furnished,
            'parking' => (int) $listing->parking,
            'pets_allowed' => (int) $listing->pets_allowed,
            'price' => $listing->price,
            'agreement_type_id' => $listing->agreement_type_id,
            'agreement_type_name' => $listing->agreementType->agreement_type ?? 'N/A',
            'move_in_date' => $listing->move_in_date,
            'amenities' => $listing->amenities ?: [],
            'amenities_data' => $listing->getAmenityModels(),
            'contact_phone' => $listing->contact_phone,
            'youtube_url' => $listing->youtube_url,
            'status_id' => $listing->status_id,
            'views' => $listing->views,
            'created_at' => $listing->created_at,
            'updated_at' => $listing->updated_at,
            'thumbnail' => $firstPic->pic_name ?? null,
            'owner_id' => (int) $listing->orig_user_id,
            'owner_name' => $owner->full_name ?? 'User',
            'owner_avatar' => $owner->avatar_url ?? null,
            'owner_initial' => strtoupper(substr($owner->full_name ?? 'U', 0, 1)),
            'owner_region' => $owner->region->region ?? 'Unknown Region',
            'owner_country' => $owner->country->country ?? 'Unknown Country',
            'owner_location' => $owner ? trim(($owner->city ?: 'Remote') . ', ' . ($owner->country->country ?? '')) : 'Unknown',
            'is_card_owner' => $isOwner,
            'pending_inquiries_count' => $pendingInquiriesCount,
            'my_response_status' => $myResponseStatus,
            'my_response_unread' => $myResponseUnread,
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

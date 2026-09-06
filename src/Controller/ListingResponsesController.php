<?php
// /src/Controller/ListingResponsesController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Listing;
use App\Models\ListingResponse;
use App\Utils\IdEncoder;

/**
 * The "Contact Owner" / inquiry handshake for a listing. Legacy delivers
 * this entirely through its Notification system (keyed off a Notification
 * row's receiver_id for authorization); gonachi-home doesn't have one, so
 * accept/decline are surfaced directly inside the listing's own view modal
 * (an "Inquiries" list, owner-only) instead of a notification bell, and
 * authorization is derived straight from the listing's own orig_user_id —
 * matching how QuotationResponsesController/MentorRequestsController were
 * already ported in this codebase.
 */
class ListingResponsesController
{
    /**
     * A prospective buyer/renter's inquiry about someone else's listing.
     */
    public static function send(array $data, int $senderId): array
    {
        $rawId = (string) ($data['listing_id'] ?? '');
        $message = trim((string) ($data['message'] ?? ''));

        $listingId = ctype_digit($rawId) ? (int) $rawId : IdEncoder::decode($rawId);
        if (!$listingId) {
            return ['success' => false, 'message' => 'Invalid listing reference.'];
        }

        $listing = Listing::find($listingId);
        if (!$listing) {
            return ['success' => false, 'message' => 'Listing not found.'];
        }

        if ($message === '') {
            return ['success' => false, 'message' => 'Message is required.'];
        }

        if ((int) $listing->orig_user_id === $senderId) {
            return ['success' => false, 'message' => 'You cannot inquire about your own listing.'];
        }

        $existing = ListingResponse::where('sender_id', $senderId)
            ->where('listing_id', $listingId)
            ->whereIn('status', [ListingResponse::STATUS_PENDING, ListingResponse::STATUS_ACCEPTED])
            ->first();

        if ($existing) {
            return ['success' => false, 'message' => 'You already have an active inquiry on this listing.'];
        }

        ListingResponse::create([
            'sender_id' => $senderId,
            'listing_id' => $listingId,
            'status' => ListingResponse::STATUS_PENDING,
            'message' => $message,
        ]);

        return ['success' => true, 'message' => 'Inquiry sent!'];
    }

    /**
     * A non-owner's own most recent inquiry status on this listing, for
     * surfacing on the card/view modal in place of "Contact Owner" — e.g.
     * "Pending" / "Accepted" / "Declined", with an unread flag the first
     * time an accept/decline is shown to them. Marking it read is a side
     * effect of this call (it's being shown to them right now), so a
     * second render of the same card no longer flags it as new.
     *
     * @return array{status: string, unread: bool}|null
     */
    public static function myStatusFor(int $listingId, int $senderId): ?array
    {
        $response = ListingResponse::where('listing_id', $listingId)
            ->where('sender_id', $senderId)
            ->orderByDesc('created_at')
            ->first();

        if (!$response) {
            return null;
        }

        $wasUnread = !$response->is_read;
        if ($wasUnread) {
            $response->is_read = true;
            $response->save();
        }

        return ['status' => $response->status, 'unread' => $wasUnread];
    }

    /**
     * Owner-only: every inquiry on one of their listings, newest first.
     */
    public static function listForListing(string $encodedListingId, int $ownerId): array
    {
        $id = ctype_digit($encodedListingId) ? (int) $encodedListingId : IdEncoder::decode($encodedListingId);
        $listing = $id ? Listing::find($id) : null;

        if (!$listing || (int) $listing->orig_user_id !== $ownerId) {
            return ['success' => false, 'message' => 'Not found, or not yours to view.'];
        }

        $responses = ListingResponse::with('sender')
            ->where('listing_id', $id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (ListingResponse $r) => [
                'id' => $r->id,
                'sender_name' => $r->sender->full_name ?? 'User',
                'sender_initial' => strtoupper(substr($r->sender->full_name ?? 'U', 0, 1)),
                'message' => $r->message,
                'status' => $r->status,
                'created_at' => $r->created_at?->diffForHumans(),
            ]);

        return ['success' => true, 'responses' => $responses->values()->all()];
    }

    public static function accept(int $responseId, int $ownerId): array
    {
        return self::updateStatus($responseId, $ownerId, ListingResponse::STATUS_ACCEPTED);
    }

    public static function decline(int $responseId, int $ownerId): array
    {
        return self::updateStatus($responseId, $ownerId, ListingResponse::STATUS_DECLINED);
    }

    private static function updateStatus(int $responseId, int $ownerId, string $status): array
    {
        $response = ListingResponse::with('listing')->find($responseId);
        if (!$response || (int) ($response->listing->orig_user_id ?? 0) !== $ownerId) {
            return ['success' => false, 'message' => 'Not found, or not yours to manage.'];
        }

        $response->status = $status;
        // The sender hasn't seen this decision yet — flips back to true the
        // next time ListingsController::buildItemArray() surfaces it to them.
        $response->is_read = false;
        $response->save();

        return ['success' => true, 'status' => $status];
    }
}

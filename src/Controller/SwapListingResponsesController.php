<?php
// /src/Controller/SwapListingResponsesController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\SwapListing;
use App\Models\SwapListingResponse;
use App\Utils\IdEncoder;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * "Connect with Owner" for Swap Marketplace listings — the same handshake
 * Real Estate World's QuotationResponsesController provides: a signed-in
 * visitor sends the owner a message, and the owner sees it (with Accept /
 * Decline) in a "Responses" list inside the listing's own detail modal.
 */
class SwapListingResponsesController
{
    private const MAX_MESSAGE_LENGTH = 2000;

    /**
     * Creates swp_listing_responses on first use if it doesn't exist yet, so
     * an already-deployed database gets the table without a (data-wiping)
     * full reset. Fresh installs / resets create it via scripts/reset.
     */
    private static function ensureTable(): void
    {
        static $checked = false;
        if ($checked) {
            return;
        }
        $checked = true;

        if (!Capsule::schema()->hasTable('swp_listing_responses')) {
            require_once __DIR__ . '/../../scripts/reset/swp-listing-responses.php';
            resetSwpListingResponsesTable();
        }
    }

    public static function send(array $data, int $senderId): array
    {
        self::ensureTable();

        $rawId = (string) ($data['listing_id'] ?? '');
        $message = trim((string) ($data['message'] ?? ''));

        $listingId = self::decodeId($rawId);
        if (!$listingId) {
            return ['success' => false, 'message' => 'Invalid listing reference.'];
        }

        $listing = SwapListing::find($listingId);
        if (!$listing) {
            return ['success' => false, 'message' => 'Listing not found.'];
        }

        if ($message === '') {
            return ['success' => false, 'message' => 'Message is required.'];
        }

        if (mb_strlen($message) > self::MAX_MESSAGE_LENGTH) {
            return ['success' => false, 'message' => 'Message is too long.'];
        }

        if ((int) $listing->user_id === $senderId) {
            return ['success' => false, 'message' => 'You cannot respond to your own listing.'];
        }

        if ($listing->status === 'completed') {
            return ['success' => false, 'message' => 'This listing has already been completed.'];
        }

        $existing = SwapListingResponse::where('sender_id', $senderId)
            ->where('listing_id', $listingId)
            ->whereIn('status', [SwapListingResponse::STATUS_PENDING, SwapListingResponse::STATUS_ACCEPTED])
            ->first();

        if ($existing) {
            return ['success' => false, 'message' => 'You already have an active response on this listing.'];
        }

        SwapListingResponse::create([
            'sender_id' => $senderId,
            'listing_id' => $listingId,
            'status' => SwapListingResponse::STATUS_PENDING,
            'message' => $message,
        ]);

        // Fresh card so the grid flips its button to "Message Sent" without a reload.
        $listing->load(['category', 'pictures', 'user']);

        return [
            'success' => true,
            'message' => 'Message sent!',
            'cardHtml' => SwapListingsController::renderCard($listing, $senderId),
        ];
    }

    /**
     * Owner-only: every response on one of their listings, newest first.
     */
    public static function listForListing(string $encodedListingId, int $ownerId): array
    {
        self::ensureTable();

        $id = self::decodeId($encodedListingId);
        $listing = $id ? SwapListing::find($id) : null;

        if (!$listing || (int) $listing->user_id !== $ownerId) {
            return ['success' => false, 'message' => 'Not found, or not yours to view.'];
        }

        $responses = SwapListingResponse::with('sender')
            ->where('listing_id', $id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (SwapListingResponse $r) => [
                'id' => $r->id,
                'sender_name' => $r->sender->full_name ?? 'User',
                'sender_initial' => strtoupper(substr($r->sender->full_name ?? 'U', 0, 1)),
                'message' => $r->message,
                'status' => $r->status,
                'created_at' => $r->created_at?->diffForHumans(),
            ]);

        return ['success' => true, 'responses' => $responses->values()->all()];
    }

    /**
     * Messages on a listing still waiting for the owner's decision — the
     * owner-only badge on its card.
     */
    public static function pendingCountFor(int $listingId): int
    {
        self::ensureTable();

        return SwapListingResponse::where('listing_id', $listingId)
            ->where('status', SwapListingResponse::STATUS_PENDING)
            ->count();
    }

    /**
     * The status of the viewer's most recent message on a listing, or '' if
     * they haven't written to its owner — drives the card/modal's "Message
     * Sent" state. Also the first thing a page render touches, so it doubles
     * as the lazy table creation point for an already-deployed database.
     */
    public static function latestStatusFor(int $listingId, int $viewerId): string
    {
        self::ensureTable();

        $status = SwapListingResponse::where('listing_id', $listingId)
            ->where('sender_id', $viewerId)
            ->orderByDesc('id')
            ->value('status');

        return (string) ($status ?? '');
    }

    /**
     * The read-only conversation between the caller and a listing's owner:
     * every message the caller sent (oldest first) with the owner's decision
     * on each. There are no owner text replies in this system — the owner's
     * side of the thread is their accept/decline.
     */
    public static function myThread(string $encodedListingId, int $userId): array
    {
        self::ensureTable();

        $id = self::decodeId($encodedListingId);
        $listing = $id ? SwapListing::with('user')->find($id) : null;

        if (!$listing) {
            return ['success' => false, 'message' => 'Listing not found.'];
        }

        if ((int) $listing->user_id === $userId) {
            return ['success' => false, 'message' => 'This is your own listing — see the messages in its detail view.'];
        }

        $rows = SwapListingResponse::where('listing_id', $id)
            ->where('sender_id', $userId)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $format = 'M j, Y \a\t g:i A';
        $last = $rows->last();

        return [
            'success' => true,
            'listing' => [
                'title' => $listing->title,
                'status' => $listing->status,
                'owner_name' => $listing->user->full_name ?? 'the owner',
            ],
            'messages' => $rows->map(fn (SwapListingResponse $r) => [
                'id' => $r->id,
                'message' => $r->message,
                'status' => $r->status,
                'sent_at' => $r->created_at?->format($format),
                'decided_at' => $r->status === SwapListingResponse::STATUS_PENDING ? null : $r->updated_at?->format($format),
            ])->values()->all(),
            // Allowed by send(): no pending/accepted message outstanding, listing still open.
            'can_send_again' => $listing->status !== 'completed'
                && (!$last || $last->status === SwapListingResponse::STATUS_DECLINED),
        ];
    }
    public static function accept(int $responseId, int $ownerId): array
    {
        return self::updateStatus($responseId, $ownerId, SwapListingResponse::STATUS_ACCEPTED);
    }

    public static function decline(int $responseId, int $ownerId): array
    {
        return self::updateStatus($responseId, $ownerId, SwapListingResponse::STATUS_DECLINED);
    }

    private static function updateStatus(int $responseId, int $ownerId, string $status): array
    {
        self::ensureTable();

        $response = SwapListingResponse::with('listing')->find($responseId);

        if (!$response || (int) ($response->listing->user_id ?? 0) !== $ownerId) {
            return ['success' => false, 'message' => 'Not found, or not yours to manage.'];
        }

        if ($response->status !== SwapListingResponse::STATUS_PENDING) {
            return ['success' => false, 'message' => 'This message has already been ' . $response->status . '.'];
        }

        $response->status = $status;
        $response->save();

        return ['success' => true, 'status' => $status];
    }

    private static function decodeId(string $raw): ?int
    {
        return ctype_digit($raw) ? (int) $raw : IdEncoder::decode($raw);
    }
}

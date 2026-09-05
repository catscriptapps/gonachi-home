<?php
// /src/Controller/QuotationResponsesController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Quotation;
use App\Models\QuotationResponse;
use App\Utils\IdEncoder;

/**
 * The "Connect"/bid handshake for a quotation request. Legacy delivers this
 * entirely through its Notification system; gonachi-home doesn't have one
 * yet, so accept/decline are surfaced directly inside the quotation's own
 * view modal (a "Responses" list, owner-only) instead of a notification bell.
 */
class QuotationResponsesController
{
    /**
     * A contractor's bid/message on someone else's quotation request.
     */
    public static function send(array $data, int $senderId): array
    {
        $rawId = (string) ($data['quotation_id'] ?? '');
        $message = trim((string) ($data['message'] ?? ''));

        $quotationId = ctype_digit($rawId) ? (int) $rawId : IdEncoder::decode($rawId);
        if (!$quotationId) {
            return ['success' => false, 'message' => 'Invalid quotation reference.'];
        }

        $quote = Quotation::find($quotationId);
        if (!$quote) {
            return ['success' => false, 'message' => 'Quotation not found.'];
        }

        if ($message === '') {
            return ['success' => false, 'message' => 'Message is required.'];
        }

        if ((int) $quote->orig_user_id === $senderId) {
            return ['success' => false, 'message' => 'You cannot bid on your own quotation.'];
        }

        $existing = QuotationResponse::where('sender_id', $senderId)
            ->where('quotation_id', $quotationId)
            ->whereIn('status', [QuotationResponse::STATUS_PENDING, QuotationResponse::STATUS_ACCEPTED])
            ->first();

        if ($existing) {
            return ['success' => false, 'message' => 'You already have an active response on this quotation.'];
        }

        QuotationResponse::create([
            'sender_id' => $senderId,
            'quotation_id' => $quotationId,
            'status' => QuotationResponse::STATUS_PENDING,
            'message' => $message,
        ]);

        return ['success' => true, 'message' => 'Response sent!'];
    }

    /**
     * Owner-only: every bid on one of their quotations, newest first.
     */
    public static function listForQuotation(string $encodedQuotationId, int $ownerId): array
    {
        $id = ctype_digit($encodedQuotationId) ? (int) $encodedQuotationId : IdEncoder::decode($encodedQuotationId);
        $quote = $id ? Quotation::find($id) : null;

        if (!$quote || (int) $quote->orig_user_id !== $ownerId) {
            return ['success' => false, 'message' => 'Not found, or not yours to view.'];
        }

        $responses = QuotationResponse::with('sender')
            ->where('quotation_id', $id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (QuotationResponse $r) => [
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
        return self::updateStatus($responseId, $ownerId, QuotationResponse::STATUS_ACCEPTED);
    }

    public static function decline(int $responseId, int $ownerId): array
    {
        return self::updateStatus($responseId, $ownerId, QuotationResponse::STATUS_DECLINED);
    }

    private static function updateStatus(int $responseId, int $ownerId, string $status): array
    {
        $response = QuotationResponse::with('quotation')->find($responseId);
        if (!$response || (int) ($response->quotation->orig_user_id ?? 0) !== $ownerId) {
            return ['success' => false, 'message' => 'Not found, or not yours to manage.'];
        }

        $response->status = $status;
        $response->save();

        return ['success' => true, 'status' => $status];
    }
}

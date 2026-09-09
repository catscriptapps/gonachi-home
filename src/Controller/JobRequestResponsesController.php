<?php
// /src/Controller/JobRequestResponsesController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\JobRequest;
use App\Models\JobRequestBid;

/**
 * The "Submit A Quote" bid handshake for a job request — Contractor
 * Discovery's equivalent of Real Estate World's
 * QuotationResponsesController, which this deliberately mirrors: a
 * contractor sends a bid/message on someone else's open job request, the
 * poster accepts/declines it inline (no notification system in this app),
 * and a contractor can look back over everything they've bid on via
 * myBids() (powers the "Bidding & Quotes" page). Job requests use plain
 * integer ids (no IdEncoder) and a bare 'open'/'closed' status string —
 * this controller follows that existing JobRequest convention rather than
 * introducing Quotation's encoded-id/int-status conventions here.
 */
class JobRequestResponsesController
{
    public static function send(array $data, int $senderId): array
    {
        $jobRequestId = (int) ($data['job_request_id'] ?? 0);
        $message = trim((string) ($data['message'] ?? ''));
        $quoteAmount = trim((string) ($data['quote_amount'] ?? ''));

        $job = $jobRequestId ? JobRequest::find($jobRequestId) : null;
        if (!$job) {
            return ['success' => false, 'message' => 'Job request not found.'];
        }

        if ($message === '') {
            return ['success' => false, 'message' => 'Message is required.'];
        }

        if ((int) $job->user_id === $senderId) {
            return ['success' => false, 'message' => 'You cannot submit a quote on your own job request.'];
        }

        $existing = JobRequestBid::where('sender_id', $senderId)
            ->where('job_request_id', $jobRequestId)
            ->whereIn('status', [JobRequestBid::STATUS_PENDING, JobRequestBid::STATUS_ACCEPTED])
            ->first();

        if ($existing) {
            return ['success' => false, 'message' => 'You already have an active quote on this job request.'];
        }

        JobRequestBid::create([
            'job_request_id' => $jobRequestId,
            'sender_id' => $senderId,
            'quote_amount' => $quoteAmount !== '' ? (float) $quoteAmount : null,
            'status' => JobRequestBid::STATUS_PENDING,
            'message' => $message,
        ]);

        return ['success' => true, 'message' => 'Quote submitted!'];
    }

    /**
     * Owner-only: every bid on one of their job requests, newest first.
     */
    public static function listForJobRequest(int $jobRequestId, int $ownerId): array
    {
        $job = JobRequest::find($jobRequestId);

        if (!$job || (int) $job->user_id !== $ownerId) {
            return ['success' => false, 'message' => 'Not found, or not yours to view.'];
        }

        $bids = JobRequestBid::with('sender')
            ->where('job_request_id', $jobRequestId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (JobRequestBid $b) => [
                'id' => $b->id,
                'sender_name' => $b->sender->full_name ?? 'User',
                'sender_initial' => strtoupper(substr($b->sender->full_name ?? 'U', 0, 1)),
                'quote_amount' => $b->quote_amount !== null ? number_format((float) $b->quote_amount, 2) : null,
                'message' => $b->message,
                'status' => $b->status,
                'created_at' => $b->created_at?->diffForHumans(),
            ]);

        return ['success' => true, 'bids' => $bids->values()->all()];
    }

    public static function accept(int $bidId, int $ownerId): array
    {
        return self::updateStatus($bidId, $ownerId, JobRequestBid::STATUS_ACCEPTED);
    }

    public static function decline(int $bidId, int $ownerId): array
    {
        return self::updateStatus($bidId, $ownerId, JobRequestBid::STATUS_DECLINED);
    }

    private static function updateStatus(int $bidId, int $ownerId, string $status): array
    {
        $bid = JobRequestBid::with('jobRequest')->find($bidId);
        if (!$bid || (int) ($bid->jobRequest->user_id ?? 0) !== $ownerId) {
            return ['success' => false, 'message' => 'Not found, or not yours to manage.'];
        }

        $bid->status = $status;
        $bid->save();

        return ['success' => true, 'status' => $status];
    }

    /**
     * A contractor's own bid history across every job request — powers the
     * "Bidding & Quotes" page.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function myBids(int $senderId): array
    {
        return JobRequestBid::with('jobRequest')
            ->where('sender_id', $senderId)
            ->orderByDesc('created_at')
            ->get()
            ->filter(fn (JobRequestBid $b) => $b->jobRequest !== null)
            ->map(fn (JobRequestBid $b) => [
                'id' => $b->id,
                'job_request_id' => $b->job_request_id,
                'service_category' => $b->jobRequest->service_category,
                'location' => $b->jobRequest->location,
                'quote_amount' => $b->quote_amount !== null ? number_format((float) $b->quote_amount, 2) : null,
                'status' => $b->status,
                'created_at' => $b->created_at,
            ])
            ->values()
            ->all();
    }
}

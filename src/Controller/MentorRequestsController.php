<?php
// /src/Controller/MentorRequestsController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Mentor;
use App\Models\MentorRequest;
use App\Utils\IdEncoder;

/**
 * The "Connect"/mentorship-request handshake. Legacy delivers this entirely
 * through its Notification system; gonachi-home doesn't have one yet, so
 * accept/decline (with the mentor's optional reply) are surfaced directly
 * inside the mentor's own view modal (a "Requests" list, owner-only)
 * instead of a notification bell.
 */
class MentorRequestsController
{
    /**
     * A mentee's request to connect with a mentor.
     */
    public static function send(array $data, int $senderId): array
    {
        $mentorId = (int) ($data['mentor_id'] ?? 0);
        $pitch = trim((string) ($data['message'] ?? ''));
        $receiverId = (int) ($data['receiver_id'] ?? 0);

        if (!$mentorId || $pitch === '') {
            return ['success' => false, 'message' => 'A message is required.'];
        }

        $mentor = Mentor::find($mentorId);
        if (!$mentor) {
            return ['success' => false, 'message' => 'Mentor not found.'];
        }

        if ((int) $mentor->orig_user_id === $senderId || $senderId === $receiverId) {
            return ['success' => false, 'message' => 'You cannot connect with yourself.'];
        }

        $existing = MentorRequest::where('sender_id', $senderId)
            ->where('mentor_id', $mentorId)
            ->whereIn('status', [MentorRequest::STATUS_PENDING, MentorRequest::STATUS_ACCEPTED])
            ->first();

        if ($existing) {
            return ['success' => false, 'message' => 'You already have an active request with this mentor.'];
        }

        MentorRequest::create([
            'sender_id' => $senderId,
            'mentor_id' => $mentorId,
            'status' => MentorRequest::STATUS_PENDING,
            'message' => $pitch,
        ]);

        return ['success' => true, 'message' => 'Mentorship request sent!'];
    }

    /**
     * Owner-only: every request on one of their mentor profiles, newest first.
     */
    public static function listForMentor(string $encodedMentorId, int $ownerId): array
    {
        $id = ctype_digit($encodedMentorId) ? (int) $encodedMentorId : IdEncoder::decode($encodedMentorId);
        $mentor = $id ? Mentor::find($id) : null;

        if (!$mentor || (int) $mentor->orig_user_id !== $ownerId) {
            return ['success' => false, 'message' => 'Not found, or not yours to view.'];
        }

        $requests = MentorRequest::with('sender')
            ->where('mentor_id', $id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (MentorRequest $r) => [
                'id' => $r->id,
                'sender_name' => $r->sender->full_name ?? 'User',
                'sender_initial' => strtoupper(substr($r->sender->full_name ?? 'U', 0, 1)),
                'message' => $r->message,
                'response_message' => $r->response_message,
                'status' => $r->status,
                'created_at' => $r->created_at?->diffForHumans(),
            ]);

        return ['success' => true, 'requests' => $requests->values()->all()];
    }

    public static function accept(int $requestId, int $ownerId, string $responseMessage): array
    {
        return self::updateStatus($requestId, $ownerId, MentorRequest::STATUS_ACCEPTED, $responseMessage);
    }

    public static function decline(int $requestId, int $ownerId, string $responseMessage): array
    {
        return self::updateStatus($requestId, $ownerId, MentorRequest::STATUS_DECLINED, $responseMessage);
    }

    private static function updateStatus(int $requestId, int $ownerId, string $status, string $responseMessage): array
    {
        $request = MentorRequest::with('mentor')->find($requestId);
        if (!$request || (int) ($request->mentor->orig_user_id ?? 0) !== $ownerId) {
            return ['success' => false, 'message' => 'Not found, or not yours to manage.'];
        }

        $request->status = $status;
        $request->response_message = trim($responseMessage) ?: null;
        $request->save();

        return ['success' => true, 'status' => $status];
    }
}

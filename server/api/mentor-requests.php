<?php
// /server/api/mentor-requests.php
//
// The "Connect"/mentorship-request handshake: GET ?id= lists the requests
// on one of the caller's own mentor profiles (owner-only). POST sends a new
// request by default, or accepts/declines one via
// {action: 'accept'|'decline', request_id, message} — legacy routes this
// through its Notification system; here it's driven straight off the
// request id since there's no notification layer, and the mentor's reply
// is saved on the request row itself (response_message) instead of a
// notification body.

declare(strict_types=1);

use Src\Controller\MentorRequestsController;
use Src\Service\AuthService;

header('Content-Type: application/json');

$userId = AuthService::userId();

if (!$userId) {
    json_response(['success' => false, 'message' => 'Please sign in.'], 401);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $encodedMentorId = (string) ($_GET['id'] ?? '');
    $result = MentorRequestsController::listForMentor($encodedMentorId, $userId);
    json_response($result, $result['success'] ? 200 : 403);
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $action = $input['action'] ?? null;

    if ($action === 'accept' || $action === 'decline') {
        $requestId = (int) ($input['request_id'] ?? 0);
        $responseMessage = (string) ($input['message'] ?? '');
        $result = $action === 'accept'
            ? MentorRequestsController::accept($requestId, $userId, $responseMessage)
            : MentorRequestsController::decline($requestId, $userId, $responseMessage);

        json_response($result, $result['success'] ? 200 : 403);
    }

    $result = MentorRequestsController::send($input, $userId);
    json_response($result, $result['success'] ? 200 : 422);
}

json_response(['success' => false, 'message' => 'Method not allowed'], 405);

<?php
// /server/api/job-request-responses.php
//
// The "Submit A Quote" bid handshake: GET ?job_request_id= lists the bids
// on one of the caller's own job requests (owner-only). POST sends a new
// bid by default, or accepts/declines one via {action: 'accept'|'decline',
// bid_id} — mirrors quotation-responses.php's shape (singular `message`,
// via json_response()) rather than job-requests.php's plural-`messages`
// convention, since this is the piece deliberately modeled on Quotations.

declare(strict_types=1);

use Src\Controller\JobRequestResponsesController;
use Src\Service\AuthService;

header('Content-Type: application/json');

$userId = AuthService::userId();

if (!$userId) {
    json_response(['success' => false, 'message' => 'Please sign in.'], 401);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $jobRequestId = (int) ($_GET['job_request_id'] ?? 0);
    $result = JobRequestResponsesController::listForJobRequest($jobRequestId, $userId);
    json_response($result, $result['success'] ? 200 : 403);
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $action = $input['action'] ?? null;

    if ($action === 'accept' || $action === 'decline') {
        $bidId = (int) ($input['bid_id'] ?? 0);
        $result = $action === 'accept'
            ? JobRequestResponsesController::accept($bidId, $userId)
            : JobRequestResponsesController::decline($bidId, $userId);

        json_response($result, $result['success'] ? 200 : 403);
    }

    $result = JobRequestResponsesController::send($input, $userId);
    json_response($result, $result['success'] ? 200 : 422);
}

json_response(['success' => false, 'message' => 'Method not allowed'], 405);

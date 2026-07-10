<?php
// /server/api/job-requests.php
//
// Handles the Contractor Discovery "post a job request" form. JSON in/out
// via fetch — no page reload/redirect, matching this project's SPA
// convention (see resources/js/pages/job-requests-page.js). Requests
// publish immediately (status = open), no moderation gate. Photos are NOT
// part of this request — they upload immediately on selection to their own
// endpoint, and this receives only the resulting URLs (see
// JobRequestController::submit()).

declare(strict_types=1);

use Src\Controller\JobRequestController;
use Src\Service\AuthService;

header('Content-Type: application/json');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'messages' => ['Method not allowed']]);
    exit;
}

$userId = AuthService::userId();

if (!$userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'messages' => ['Please sign in to post a job request.']]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];

$result = JobRequestController::submit($input, $userId);

if (!$result['success']) {
    echo json_encode(['success' => false, 'messages' => $result['errors']]);
    exit;
}

echo json_encode(['success' => true, 'messages' => ['Your job request is live — contractors in your category and area can now see it.']]);

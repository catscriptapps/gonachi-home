<?php
// /server/api/job-request-close.php
//
// Marks a job request closed ("Mark As Filled"). JSON in/out via fetch, no
// page reload. Requires login; JobRequestController::close() itself
// enforces that only the request's own owner can close it.

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
    echo json_encode(['success' => false, 'messages' => ['Please sign in.']]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$id = (int) ($input['id'] ?? 0);

if ($id <= 0 || !JobRequestController::close($id, $userId)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'messages' => ["Couldn't close that request."]]);
    exit;
}

echo json_encode(['success' => true, 'messages' => ['Request marked as filled.']]);

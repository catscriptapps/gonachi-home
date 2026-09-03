<?php
// /server/api/saved-search-delete.php
//
// Deletes one of the logged-in user's Saved Alerts (ownership-checked).
// JSON in/out via fetch, no page reload.

declare(strict_types=1);

use Src\Controller\SavedSearchController;
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

if ($id <= 0 || !SavedSearchController::delete($id, $userId)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'messages' => ["Couldn't find that alert."]]);
    exit;
}

echo json_encode(['success' => true, 'messages' => ['Alert removed.']]);

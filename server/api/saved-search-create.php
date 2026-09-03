<?php
// /server/api/saved-search-create.php
//
// Creates a Saved Alert for the logged-in user — same (search, region)
// vocabulary as the search bar on /real-estate-leads. JSON in/out via
// fetch, no page reload. See Src\Controller\SavedSearchController.

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
    echo json_encode(['success' => false, 'messages' => ['Please sign in to save an alert.']]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$search = trim((string) ($input['q'] ?? ''));
$region = trim((string) ($input['region'] ?? ''));

$result = SavedSearchController::create($userId, $search ?: null, $region ?: null);

if (!$result['success']) {
    echo json_encode(['success' => false, 'messages' => $result['errors']]);
    exit;
}

$saved = $result['saved_search'];

echo json_encode([
    'success' => true,
    'saved_search' => [
        'id' => $saved->id,
        'search_query' => $saved->search_query,
        'region_slug' => $saved->region_slug,
    ],
]);

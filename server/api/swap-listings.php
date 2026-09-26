<?php
// /server/api/swap-listings.php
//
// Create/update/delete/status-toggle hub for the signed-in user's own Swap Marketplace
// listings — mirrors Real Estate World's api/listings.php dispatch shape
// (a single POST endpoint branching on _method / intent) but scoped to
// Swap Marketplace's own simpler save() (see SwapListingsController).

declare(strict_types=1);

use Src\Controller\SwapListingsController;
use Src\Service\AuthService;

header('Content-Type: application/json');

$userId = AuthService::userId();

if (!$userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please sign in.']);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];

// 1. Delete
if (($input['_method'] ?? '') === 'DELETE') {
    $result = SwapListingsController::delete((string) ($input['encoded_id'] ?? ''), $userId);

    if (!$result['success']) {
        http_response_code(403);
    }

    echo json_encode($result);
    exit;
}

// 2. Status toggle (Mark As Completed / Reactivate)
if (in_array($input['intent'] ?? '', ['completed', 'posted'], true)) {
    $result = SwapListingsController::setStatus((string) ($input['encoded_id'] ?? ''), (string) $input['intent'], $userId);

    if (!$result['success']) {
        http_response_code(403);
        echo json_encode($result);
        exit;
    }

    echo json_encode([
        'success' => true,
        'cardHtml' => SwapListingsController::renderCard($result['listing'], $userId),
    ]);
    exit;
}

// 3. Create or update
$result = SwapListingsController::save($input, $userId);

if (!$result['success']) {
    http_response_code(422);
    echo json_encode(['success' => false, 'messages' => $result['errors']]);
    exit;
}

echo json_encode([
    'success' => true,
    'encoded_id' => \App\Utils\IdEncoder::encode($result['listing']->id),
    'cardHtml' => SwapListingsController::renderCard($result['listing'], $userId),
]);

<?php
// /server/api/swap-listing-save.php
//
// Toggles the signed-in user's bookmark on a Swap Marketplace listing (the "Saved" tab
// — see SwapListingsController::toggleSave() / saved()).

declare(strict_types=1);

use App\Models\SwapListing;
use App\Utils\IdEncoder;
use Src\Controller\SwapListingsController;
use Src\Service\AuthService;

header('Content-Type: application/json');

$userId = AuthService::userId();

if (!$userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please sign in to save listings.']);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$encodedId = (string) ($input['encoded_id'] ?? '');
$listingId = ctype_digit($encodedId) ? (int) $encodedId : IdEncoder::decode($encodedId);

$listing = $listingId ? SwapListing::find($listingId) : null;

if (!$listing) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Listing not found.']);
    exit;
}

$result = SwapListingsController::toggleSave($listing->id, $userId);

echo json_encode($result);

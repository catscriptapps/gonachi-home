<?php
// /server/api/rental-listing-submit.php
//
// Handles the "List A Property For Rent" contribution form. JSON in/out via
// fetch — no page reload/redirect. Every submitted listing starts
// pending_review; see rental-listing-review.php. Photos are NOT part of this
// request — they upload immediately on selection to their own endpoint, and
// this receives only the resulting URLs (see RentalListingController::submitListing()).

declare(strict_types=1);

use Src\Controller\RentalListingController;
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
    echo json_encode(['success' => false, 'messages' => ['Please sign in to list a property.']]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];

$result = RentalListingController::submitListing($input, $userId);

if (!$result['success']) {
    echo json_encode(['success' => false, 'messages' => $result['errors']]);
    exit;
}

echo json_encode(['success' => true, 'messages' => ["Thank you — it's in the review queue and will appear in Rental Opportunities once approved."]]);

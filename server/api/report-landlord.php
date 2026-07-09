<?php
// /server/api/report-landlord.php
//
// Handles the Report A Landlord contribution form. JSON in/out via fetch —
// no page reload/redirect (that's the SPA router's job everywhere else in
// this project; see resources/js/pages/report-landlord-page.js). Every
// submitted report starts pending_review; see landlord-report-review.php.
// Photos are NOT part of this request — Building Pictures and Supporting
// Evidence upload immediately on selection to their own endpoints, and this
// receives only the resulting URLs (see LandlordDirectoryController::submitReport()).

declare(strict_types=1);

use Src\Controller\LandlordDirectoryController;
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
    echo json_encode(['success' => false, 'messages' => ['Please sign in to submit a report.']]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];

$result = LandlordDirectoryController::submitReport($input, $userId);

if (!$result['success']) {
    echo json_encode(['success' => false, 'messages' => $result['errors']]);
    exit;
}

echo json_encode(['success' => true, 'messages' => ["Thank you — it's in the review queue and will appear on the landlord's public record once approved."]]);

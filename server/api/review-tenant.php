<?php
// /server/api/review-tenant.php
//
// Handles the "Review A Tenant" form (reviewer = landlord). Mirrors
// review-landlord.php exactly — see resources/js/pages/review-tenant-page.js.

declare(strict_types=1);

use Src\Controller\ReviewController;
use Src\Service\AuthService;
use Src\Utils\CountryScope;

header('Content-Type: application/json');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'messages' => ['Method not allowed']]);
    exit;
}

$userId = AuthService::userId();

if (!$userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'messages' => ['Please sign in to submit a review.']]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];

$countryCode = (string) ($input['country_code'] ?? 'ng');
if (!CountryScope::isSupported($countryCode)) {
    $countryCode = 'ng';
}

$result = ReviewController::submitTenantReview($input, $userId, $countryCode);

if (!$result['success']) {
    echo json_encode(['success' => false, 'messages' => $result['errors']]);
    exit;
}

echo json_encode(['success' => true, 'messages' => [$result['message']]]);

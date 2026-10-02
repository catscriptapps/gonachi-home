<?php
// /server/api/review-landlord.php
//
// Handles the "Review A Landlord" form (reviewer = tenant). JSON in/out via
// fetch, no page reload — see resources/js/pages/review-landlord-page.js.
// Replaces report-landlord.php: reviews publish automatically via the
// double-blind release mechanic (Src\Service\ReviewService), there is no
// more pre-publish admin approval step.

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

$result = ReviewController::submitLandlordReview($input, $userId, $countryCode);

if (!$result['success']) {
    echo json_encode(['success' => false, 'messages' => $result['errors']]);
    exit;
}

echo json_encode(['success' => true, 'messages' => [$result['message']]]);

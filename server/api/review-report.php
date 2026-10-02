<?php
// /server/api/review-report.php
//
// "Report Review" (landlord_and_tenant_validation.pdf §9) — any signed-in
// user can report a review they can see. JSON in/out via fetch.

declare(strict_types=1);

use Src\Service\AuthService;
use Src\Service\ReviewService;

header('Content-Type: application/json');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'messages' => ['Method not allowed']]);
    exit;
}

$userId = AuthService::userId();

if (!$userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'messages' => ['Please sign in to report a review.']]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$reviewId = (int) ($input['review_id'] ?? 0);
$reason = (string) ($input['reason'] ?? '');
$details = $input['details'] ?? null;

if ($reviewId <= 0) {
    echo json_encode(['success' => false, 'messages' => ['Invalid request.']]);
    exit;
}

$result = ReviewService::report($reviewId, $userId, $reason, $details);

if (!$result['success']) {
    echo json_encode(['success' => false, 'messages' => $result['errors']]);
    exit;
}

echo json_encode(['success' => true, 'messages' => ["Thank you — we've received your report and will review it."]]);

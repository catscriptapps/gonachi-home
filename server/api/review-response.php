<?php
// /server/api/review-response.php
//
// Right of response (landlord_and_tenant_validation.pdf §10) — exactly one
// professional response per review, allowed only to the party who was
// reviewed (not the reviewer, not a bystander). JSON in/out via fetch.

declare(strict_types=1);

use App\Models\LandlordRecord;
use App\Models\Review;
use App\Models\TenantRecord;
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
    echo json_encode(['success' => false, 'messages' => ['Please sign in to respond.']]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$reviewId = (int) ($input['review_id'] ?? 0);
$responseText = (string) ($input['response'] ?? '');

$review = Review::visible()->find($reviewId);

if (!$review) {
    echo json_encode(['success' => false, 'messages' => ['That review is not available to respond to.']]);
    exit;
}

$isTheReviewedParty = $review->reviewee_type === 'landlord'
    ? LandlordRecord::where('id', $review->reviewee_landlord_id)->where('user_id', $userId)->exists()
    : TenantRecord::where('id', $review->reviewee_tenant_id)->where('user_id', $userId)->exists();

if (!$isTheReviewedParty) {
    http_response_code(403);
    echo json_encode(['success' => false, 'messages' => ['Only the reviewed party can respond to this review.']]);
    exit;
}

$result = ReviewService::respond($reviewId, $userId, $responseText);

if (!$result['success']) {
    echo json_encode(['success' => false, 'messages' => $result['errors']]);
    exit;
}

echo json_encode(['success' => true, 'messages' => ['Your response has been posted.']]);

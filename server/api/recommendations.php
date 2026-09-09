<?php
// /server/api/recommendations.php
//
// GET: recommendations RECEIVED by a user (?dest_user_id=) for the "Browse
// Recommendations" lookup, or the caller's own GIVEN recommendations
// (?mine=1) for the "My Recommendations" tab. POST: submit a new one.

declare(strict_types=1);

use Src\Controller\RecommendationsController;
use Src\Service\AuthService;

header('Content-Type: application/json');

$userId = AuthService::userId();

if (!$userId) {
    json_response(['success' => false, 'message' => 'Please sign in.'], 401);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    if (!empty($_GET['mine'])) {
        json_response(['success' => true, 'data' => RecommendationsController::givenBy($userId)]);
    }

    $destUserId = (int) ($_GET['dest_user_id'] ?? 0);
    if (!$destUserId) {
        json_response(['success' => false, 'message' => 'A user is required.'], 400);
    }

    json_response(['success' => true, 'data' => RecommendationsController::receivedBy($destUserId)]);
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $result = RecommendationsController::submit($input, $userId);

    if (!$result['success']) {
        json_response(['success' => false, 'messages' => $result['errors']], 422);
    }

    json_response(['success' => true, 'recommend_id' => $result['recommendation']->recommend_id]);
}

json_response(['success' => false, 'message' => 'Method not allowed'], 405);

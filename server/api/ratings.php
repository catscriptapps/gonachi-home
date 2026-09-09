<?php
// /server/api/ratings.php
//
// GET: ratings RECEIVED by a user (?dest_user_id=) for the "Browse Ratings"
// lookup, or the caller's own GIVEN ratings (?mine=1) for the "My Ratings"
// tab. POST: submit a new rating.

declare(strict_types=1);

use Src\Controller\RatingsController;
use Src\Service\AuthService;

header('Content-Type: application/json');

$userId = AuthService::userId();

if (!$userId) {
    json_response(['success' => false, 'message' => 'Please sign in.'], 401);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    if (!empty($_GET['mine'])) {
        json_response(['success' => true, 'data' => RatingsController::givenBy($userId)]);
    }

    $destUserId = (int) ($_GET['dest_user_id'] ?? 0);
    if (!$destUserId) {
        json_response(['success' => false, 'message' => 'A user is required.'], 400);
    }

    json_response(['success' => true, 'data' => RatingsController::receivedBy($destUserId)]);
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $result = RatingsController::submit($input, $userId);

    if (!$result['success']) {
        json_response(['success' => false, 'messages' => $result['errors']], 422);
    }

    json_response(['success' => true, 'rating_id' => $result['rating']->rating_id]);
}

json_response(['success' => false, 'message' => 'Method not allowed'], 405);

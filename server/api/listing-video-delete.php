<?php
// /server/api/listing-video-delete.php
//
// Removes the video attached to a listing (owner-only) — DB field + file.

declare(strict_types=1);

use Src\Controller\ListingsController;
use Src\Service\AuthService;

header('Content-Type: application/json');

$userId = AuthService::userId();

if (!$userId) {
    json_response(['success' => false, 'message' => 'Please sign in.'], 401);
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$encodedId = (string) ($input['id'] ?? '');

$result = ListingsController::removeVideo($encodedId, $userId);

json_response($result, $result['success'] ? 200 : 403);

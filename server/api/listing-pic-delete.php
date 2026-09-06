<?php
// /server/api/listing-pic-delete.php
//
// Removes one photo from a listing (owner-only) — DB row + physical file.

declare(strict_types=1);

use Src\Controller\ListingPicturesController;
use Src\Service\AuthService;

header('Content-Type: application/json');

$userId = AuthService::userId();

if (!$userId) {
    json_response(['success' => false, 'message' => 'Please sign in.'], 401);
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$picId = (int) ($input['entry_id'] ?? 0);

$result = ListingPicturesController::delete($picId, $userId);

json_response($result, $result['success'] ? 200 : 403);

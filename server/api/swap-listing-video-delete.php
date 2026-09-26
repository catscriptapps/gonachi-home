<?php
// /server/api/swap-listing-video-delete.php
//
// Removes the video attached to a Swap Marketplace listing (owner-only) — DB field +
// file. Mirrors Real Estate World's quotation-video-delete.php.

declare(strict_types=1);

use Src\Controller\SwapListingsController;
use Src\Service\AuthService;

header('Content-Type: application/json');

$userId = AuthService::userId();

if (!$userId) {
    json_response(['success' => false, 'message' => 'Please sign in.'], 401);
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$encodedId = (string) ($input['id'] ?? '');

$result = SwapListingsController::removeVideo($encodedId, $userId);

if (!$result['success']) {
    json_response($result, 403);
}

json_response([
    'success' => true,
    'cardHtml' => SwapListingsController::renderCard($result['listing'], $userId),
]);

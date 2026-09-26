<?php
// /server/api/swap-listing-pic-delete.php
//
// Removes one photo from a Swap Marketplace listing (owner-only) — DB row +
// physical file. Mirrors Real Estate World's quotation-pic-delete.php.

declare(strict_types=1);

use Src\Controller\SwapListingsController;
use Src\Service\AuthService;

header('Content-Type: application/json');

$userId = AuthService::userId();

if (!$userId) {
    json_response(['success' => false, 'message' => 'Please sign in.'], 401);
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$picId = (int) ($input['pic_id'] ?? 0);

$result = SwapListingsController::deletePhoto($picId, $userId);

if (!$result['success']) {
    json_response($result, 403);
}

$assetBase = getAssetBase();

json_response([
    'success' => true,
    'cardHtml' => SwapListingsController::renderCard($result['listing'], $userId),
    'photos' => $result['listing']->pictures->map(fn ($pic) => [
        'id' => $pic->id,
        'url' => $assetBase . $pic->file_path,
    ])->values()->all(),
]);

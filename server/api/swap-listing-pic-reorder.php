<?php
// /server/api/swap-listing-pic-reorder.php
//
// Saves a new photo order for a Swap Marketplace listing (owner-only). Body:
// { id: <encoded listing id>, order: [<photo id>, ...] } — the full set of
// the listing's photo ids in the desired order.

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
$order = is_array($input['order'] ?? null) ? $input['order'] : [];

$result = SwapListingsController::reorderPhotos($encodedId, $order, $userId);

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

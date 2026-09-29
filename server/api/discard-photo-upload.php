<?php
// /server/api/discard-photo-upload.php
//
// Deletes one not-yet-attached compose-modal photo upload — called by
// resources/js/utils/compose-photo-strip.js the instant a freshly-uploaded
// photo is removed from the Photos strip before submit, or when the whole
// compose modal is dismissed unsaved with uploads still sitting in it. One
// endpoint for all four types (advert/listing/quotation/swap listing) —
// the type is inferred from the URL's own path, see
// Src\Service\PendingUploadTracker.

declare(strict_types=1);

use Src\Service\AuthService;
use Src\Service\PendingUploadTracker;

header('Content-Type: application/json');

$userId = AuthService::userId();

if (!$userId) {
    json_response(['success' => false, 'message' => 'Please sign in.'], 401);
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$url = trim((string) ($input['url'] ?? ''));

if ($url === '') {
    json_response(['success' => false, 'message' => 'No URL given.'], 400);
}

$assetBase = getAssetBase();

if (!str_starts_with($url, $assetBase)) {
    // Not one of this deploy's own asset URLs — nothing for this endpoint
    // to do, but not the caller's fault either (e.g. a stale/foreign URL).
    json_response(['success' => true]);
}

$relativePath = substr($url, strlen($assetBase));

$result = PendingUploadTracker::discard($userId, $relativePath);

json_response($result, $result['success'] ? 200 : 422);

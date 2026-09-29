<?php
// /server/api/quotation-photo-upload.php
//
// Photo upload target for the quotation compose/edit modal's Photos strip
// (resources/js/utils/compose-photo-strip.js) — lets a quotation be filled
// out and photographed in one sitting, the same "add pictures right from
// the form" pattern Swap Marketplace established
// (server/api/swap-listing-photo-upload.php). Images arrive here already
// compressed client-side by the shared upload modal's WorkerPool, so this
// just stores them and returns URLs; the quotation doesn't exist yet (or is
// mid-edit) — QuotationsController::save() attaches these URLs afterward via
// QuotationPicturesController::replacePhotos(), same as the existing
// post-creation quotation-upload-pics.php does for the view modal's own
// "Add Photo" button.

declare(strict_types=1);

use Src\Service\AuthService;
use Src\Service\ImageUploadService;
use Src\Service\PendingUploadTracker;

header('Content-Type: application/json');

$userId = AuthService::userId();

if (!$userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please sign in to upload photos.']);
    exit;
}

if (empty($_FILES['images']) || empty($_FILES['images']['tmp_name'][0])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No images found.']);
    exit;
}

$uploadDir = __DIR__ . '/../../public/images/uploads/quotations/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$service = new ImageUploadService($uploadDir, 2000, 90);
$relativePrefix = 'images/uploads/quotations/';

$uploaded = $service->upload($_FILES['images'], function (array $files) use ($relativePrefix) {
    foreach ($files as $key => $fileInfo) {
        $files[$key]['url'] = getAssetBase() . $relativePrefix . $fileInfo['fileName'];
    }
    return $files;
});

if (empty($uploaded) || (isset($uploaded['success']) && $uploaded['success'] === false)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Upload failed.']);
    exit;
}

// Tracked as "pending" until QuotationsController::save() attaches it (see
// QuotationPicturesController::replacePhotos()) or the client discards it —
// see PendingUploadTracker's docblock for the full lifecycle.
foreach ($uploaded as $file) {
    PendingUploadTracker::track($userId, $relativePrefix . $file['fileName']);
}

echo json_encode([
    'success' => true,
    'files' => array_map(fn($f) => ['url' => $f['url'], 'fileName' => $f['fileName']], $uploaded),
]);

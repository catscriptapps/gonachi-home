<?php
// /server/api/contractor-claim-document-upload.php
//
// Business-document upload target for the "Claim This Profile" form, called
// by the shared upload modal (resources/js/modals/upload-modal.js's
// createUploadHandler) — same pattern as job-request-photo-upload.php.
// Images arrive here already compressed client-side by its WorkerPool; this
// just stores the one document image and returns its filename. The main
// /api/contractor-claim submission references that filename, it never
// receives the raw file itself (see ContractorClaimController::submit()).

declare(strict_types=1);

use Src\Service\AuthService;
use Src\Service\ImageUploadService;

header('Content-Type: application/json');

if (!AuthService::userId()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please sign in to upload a document.']);
    exit;
}

if (empty($_FILES['images']) || empty($_FILES['images']['tmp_name'][0])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No image found.']);
    exit;
}

$uploadDir = realpath(__DIR__ . '/../../public/images/uploads/') . '/contractor-claims/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$service = new ImageUploadService($uploadDir, 1600, 85);
$relativePrefix = 'images/uploads/contractor-claims/';

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

echo json_encode([
    'success' => true,
    'files' => array_map(fn($f) => ['url' => $f['url'], 'fileName' => $f['fileName']], $uploaded),
]);

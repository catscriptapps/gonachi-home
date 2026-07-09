<?php
// /server/api/report-landlord-photo-upload.php
//
// Building-pictures upload target for the Report A Landlord form, called by
// the shared upload modal (resources/js/modals/upload-modal.js's
// createUploadHandler). Images arrive here already compressed client-side
// by its WorkerPool, so this just stores them and returns URLs — the main
// /api/report-landlord submission references those URLs, it never receives
// raw files itself (see LandlordDirectoryController::submitReport()).

declare(strict_types=1);

use Src\Service\AuthService;
use Src\Service\ImageUploadService;

header('Content-Type: application/json');

if (!AuthService::userId()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please sign in to upload photos.']);
    exit;
}

if (empty($_FILES['images']) || empty($_FILES['images']['tmp_name'][0])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No images found.']);
    exit;
}

$uploadDir = realpath(__DIR__ . '/../../public/images/uploads/') . '/landlord-reports/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$service = new ImageUploadService($uploadDir);
$relativePrefix = 'images/uploads/landlord-reports/';

$uploaded = $service->upload($_FILES['images'], function (array $files) use ($relativePrefix) {
    foreach ($files as $key => $fileInfo) {
        $files[$key]['url'] = '/' . $relativePrefix . $fileInfo['fileName'];
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

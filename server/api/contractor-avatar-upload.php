<?php
// /server/api/contractor-avatar-upload.php
//
// Photo upload for a contractor's profile — called by the shared upload
// modal (resources/js/modals/upload-modal.js's createUploadHandler,
// single-file mode) from resources/js/pages/contractor-page.js. Mirrors
// server/api/avatar-upload.php's single-file replace pattern exactly.
// Admin-only for now (not the claimed owner) — see contractor/detail.php's
// $isAdmin gate on the "change photo" button; a claimed owner's own
// self-service upload may follow later. The target contractor is passed
// via ?contractor_id= on the endpoint URL since createUploadHandler's
// FormData only ever carries the image blobs.

declare(strict_types=1);

use App\Models\Contractor;
use Src\Service\AuthService;
use Src\Service\ImageUploadService;

header('Content-Type: application/json');

if (!AuthService::isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Only an admin can update a contractor photo right now.']);
    exit;
}

$contractorId = (int) ($_GET['contractor_id'] ?? 0);
$contractor = $contractorId ? Contractor::find($contractorId) : null;

if (!$contractor) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Contractor not found.']);
    exit;
}

if (empty($_FILES['images']) || empty($_FILES['images']['tmp_name'][0])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No image found.']);
    exit;
}

$uploadDir = realpath(__DIR__ . '/../../public/images/uploads/') . '/contractors/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// HARD single-file enforcement — a contractor has exactly one profile photo.
$singleFile = [
    'name'     => [$_FILES['images']['name'][0]],
    'type'     => [$_FILES['images']['type'][0]],
    'tmp_name' => [$_FILES['images']['tmp_name'][0]],
    'error'    => [$_FILES['images']['error'][0]],
    'size'     => [$_FILES['images']['size'][0]],
];

$service = new ImageUploadService($uploadDir);
$relativePrefix = 'images/uploads/contractors/';

$uploaded = $service->upload($singleFile, function (array $files) use ($relativePrefix) {
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

// Delete the old photo (if any) now that the new one is safely written.
if (!empty($contractor->avatar_url)) {
    $oldPath = realpath(__DIR__ . '/../../public/' . $relativePrefix . $contractor->avatar_url);
    if ($oldPath && str_starts_with($oldPath, realpath($uploadDir)) && file_exists($oldPath)) {
        unlink($oldPath);
    }
}

$contractor->avatar_url = basename($uploaded[0]['fileName']);
$contractor->save();

echo json_encode([
    'success' => true,
    'files' => [['url' => $uploaded[0]['url']]],
]);

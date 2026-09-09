<?php
// /server/api/listing-upload-video.php
//
// Chunked video upload for a listing (owner-only). Mirrors
// advert-upload-video.php's chunking protocol exactly (same
// VideoUploadService, same video-upload-modal.js/createVideoUploadHandler
// on the client). At most one video per listing — a successful upload here
// always replaces whichever video is already attached (see
// ListingsController::attachVideo()).

declare(strict_types=1);

use App\Utils\IdEncoder;
use Src\Controller\ListingsController;
use Src\Service\AuthService;
use Src\Service\VideoUploadService;

header('Content-Type: application/json; charset=UTF-8');

$userId = AuthService::userId();
if (!$userId) {
    json_response(['success' => false, 'message' => 'Authentication required'], 401);
}

if (empty($_FILES['video_chunk']) || empty($_FILES['video_chunk']['tmp_name'])) {
    json_response(['success' => false, 'message' => 'No video chunk found in request.'], 400);
}

$encodedId = (string) ($_POST['id'] ?? $_GET['id'] ?? '');
$id = ctype_digit($encodedId) ? (int) $encodedId : IdEncoder::decode($encodedId);

if (!$id) {
    json_response(['success' => false, 'message' => 'Listing not found.'], 404);
}

$chunkIndex = (int) ($_POST['chunk_index'] ?? 0);
$totalChunks = (int) ($_POST['total_chunks'] ?? 1);
$fileUuid = $_POST['file_uuid'] ?? '';
$originalName = $_POST['filename'] ?? 'video.mp4';

if (!$fileUuid) {
    json_response(['success' => false, 'message' => 'Missing upload session ID.'], 400);
}

try {
    $uploadDir = __DIR__ . '/../../public/videos/listings/';
    // Max 100MB — matches Adverts' cap for the same reason (short property clips, not full-length videos).
    $service = new VideoUploadService($uploadDir, 100);

    $result = $service->handleChunk($_FILES['video_chunk'], $fileUuid, $chunkIndex, $totalChunks, $originalName);

    if ($result['status'] !== 'completed') {
        json_response(['success' => true, 'message' => 'Chunk processed.', 'status' => 'uploading']);
    }

    $attach = ListingsController::attachVideo($encodedId, $userId, $result['fileName']);

    if (!$attach['success']) {
        // Roll back the just-uploaded file — the listing lookup/ownership failed.
        @unlink($uploadDir . $result['fileName']);
        json_response($attach, 403);
    }

    json_response([
        'success' => true,
        'message' => 'Video uploaded successfully.',
        'filename' => $result['fileName'],
        'url' => getAssetBase() . 'videos/listings/' . $result['fileName'],
        'files' => [['url' => getAssetBase() . 'videos/listings/' . $result['fileName']]],
    ]);
} catch (\Throwable $e) {
    json_response(['success' => false, 'message' => $e->getMessage()], 500);
}

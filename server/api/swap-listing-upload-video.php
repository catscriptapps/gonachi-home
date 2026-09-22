<?php
// /server/api/swap-listing-upload-video.php
//
// Chunked video upload for a Swap listing (owner-only). Mirrors Real Estate
// World's quotation-upload-video.php chunking protocol exactly (same
// VideoUploadService, same video-upload-modal.js/createVideoUploadHandler
// on the client). At most one video per listing — a successful upload here
// always replaces whichever video is already attached (see
// SwapListingsController::attachVideo()).

declare(strict_types=1);

use App\Utils\IdEncoder;
use Src\Controller\SwapListingsController;
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
    $uploadDir = __DIR__ . '/../../public/videos/swap-listings/';
    // Max 100MB — matches Quotations'/Adverts' cap (short item clips, not
    // full-length videos).
    $service = new VideoUploadService($uploadDir, 100);

    $result = $service->handleChunk($_FILES['video_chunk'], $fileUuid, $chunkIndex, $totalChunks, $originalName);

    if ($result['status'] !== 'completed') {
        json_response(['success' => true, 'message' => 'Chunk processed.', 'status' => 'uploading']);
    }

    $attach = SwapListingsController::attachVideo($encodedId, $userId, $result['fileName']);

    if (!$attach['success']) {
        // Roll back the just-uploaded file — the listing lookup/ownership failed.
        @unlink($uploadDir . $result['fileName']);
        json_response($attach, 403);
    }

    $videoUrl = getAssetBase() . 'videos/swap-listings/' . $result['fileName'];

    json_response([
        'success' => true,
        'message' => 'Video uploaded successfully.',
        'filename' => $result['fileName'],
        'url' => $videoUrl,
        'files' => [['url' => $videoUrl]],
        'cardHtml' => SwapListingsController::renderCard($attach['listing'], $userId),
    ]);
} catch (\Throwable $e) {
    json_response(['success' => false, 'message' => $e->getMessage()], 500);
}

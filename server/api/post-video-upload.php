<?php
// /server/api/post-video-upload.php

declare(strict_types=1);

use Src\Service\VideoUploadService;
use Src\Service\AuthService;

header('Content-Type: application/json; charset=UTF-8');

// 1. Auth Check
$userId = AuthService::userId() ?? $_SESSION['user_id'] ?? 1;
if (!$userId) {
    json_response(['success' => false, 'message' => 'Authentication required'], 401);
    exit;
}

// 2. Validate 'video_chunk' (Changed from 'video' to match JS)
if (empty($_FILES['video_chunk']) || empty($_FILES['video_chunk']['tmp_name'])) {
    json_response(['success' => false, 'message' => 'No video chunk found in request.'], 400);
    exit;
}

// 🍊 Get Chunk Metadata from JS
$chunkIndex = (int)($_POST['chunk_index'] ?? 0);
$totalChunks = (int)($_POST['total_chunks'] ?? 1);
$fileUuid = $_POST['file_uuid'] ?? '';
$originalName = $_POST['filename'] ?? 'video.mp4';

if (!$fileUuid) {
    json_response(['success' => false, 'message' => 'Missing upload session ID.'], 400);
    exit;
}

try {
    // 3. Resolve Directories
    $uploadDir = realpath(__DIR__ . '/../../public/videos/');
    if (!$uploadDir) {
        $uploadDir = __DIR__ . '/../../public/videos/';
    }

    $publicPathPrefix = '/videos/';

    // 4. Service Execution - Now handling chunks
    // Increased max size to 200MB in the constructor call
    $service = new VideoUploadService($uploadDir, 200);

    // Pass the file and the chunking metadata
    $result = $service->handleChunk(
        $_FILES['video_chunk'],
        $fileUuid,
        $chunkIndex,
        $totalChunks,
        $originalName
    );

    // 5. Success Response
    // If 'fileName' is present in result, it means the last chunk is finished.
    if ($result['status'] === 'completed') {
        json_response([
            'success'   => true,
            'message'   => 'Video uploaded successfully.',
            'filename'  => $result['fileName'],
            'url'       => $publicPathPrefix . $result['fileName'],
            'files'     => [
                ['url' => $publicPathPrefix . $result['fileName']]
            ]
        ]);
    } else {
        // Chunk received, but more are coming
        json_response([
            'success' => true,
            'message' => 'Chunk processed.',
            'status'  => 'uploading'
        ]);
    }
} catch (\Throwable $e) {
    // 6. Error Response
    json_response([
        'success' => false,
        'message' => $e->getMessage()
    ], 500);
}

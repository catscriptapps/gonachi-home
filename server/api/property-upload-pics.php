<?php
// /server/api/property-upload-pics.php

declare(strict_types=1);

use App\Models\Property;
use App\Models\PropertyPic;
use App\Utils\IdEncoder;
use Src\Controller\PropertiesController;
use Src\Service\AuthService;
use Src\Service\ImageUploadService;

header('Content-Type: application/json');

// Only landlords may manage their own property photos (matches properties.php save/delete gate).
$currentLandlord = AuthService::currentLandlord();
if (!$currentLandlord) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required to manage portfolio properties.']);
    exit;
}

$rawId = $_GET['id'] ?? null;
$propertyId = ($rawId !== null && !is_numeric($rawId)) ? IdEncoder::decode((string)$rawId) : (int)$rawId;

$property = $propertyId ? Property::find($propertyId) : null;

if (!$property) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Property not found.']);
    exit;
}

if ((int)$property->landlord_id !== (int)$currentLandlord->id) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'You do not have permission to modify this property.']);
    exit;
}

if (empty($_FILES['images']) || empty($_FILES['images']['tmp_name'][0])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No photos found in request.']);
    exit;
}

// Enforce the global media limit against the current picture count.
$existingCount = $property->pictures()->count();
$mediaLimit = getMediaLimit();
$incoming = count($_FILES['images']['tmp_name']);

if ($existingCount >= $mediaLimit) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => "You have reached the limit of {$mediaLimit} pictures."]);
    exit;
}

if ($existingCount + $incoming > $mediaLimit) {
    $allowed = $mediaLimit - $existingCount;
    foreach (['name', 'type', 'tmp_name', 'error', 'size'] as $field) {
        $_FILES['images'][$field] = array_slice($_FILES['images'][$field], 0, $allowed);
    }
}

$baseUploadDir = realpath(__DIR__ . '/../../public/images/uploads/');
$propertyUploadDir = $baseUploadDir . '/properties/';

$service = new ImageUploadService($propertyUploadDir, 2000, 90);
$relativePublicPathPrefix = 'images/uploads/properties/';

$uploaded = $service->upload($_FILES['images'], function (array $files) use ($relativePublicPathPrefix) {
    foreach ($files as $key => $fileInfo) {
        $files[$key]['fileUrl'] = '/' . $relativePublicPathPrefix . $fileInfo['fileName'];
        $files[$key]['resultUrl'] = $fileInfo['fileName'];
    }
    return $files;
});

if (empty($uploaded) || (isset($uploaded['success']) && $uploaded['success'] === false)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $uploaded['message'] ?? 'Upload failed.']);
    exit;
}

// Persist each uploaded file as a property picture record.
$nextPos = (int)($property->pictures()->max('pos_index') ?? -1) + 1;
$responseFiles = [];

foreach ($uploaded as $fileInfo) {
    PropertyPic::create([
        'property_id' => $property->id,
        'pic_name'    => $fileInfo['resultUrl'],
        'pos_index'   => $nextPos++,
    ]);
    $responseFiles[] = ['url' => $fileInfo['fileUrl']];
}

PropertiesController::logActivity("Added " . count($responseFiles) . " photo(s) to property: {$property->property_name}", 'Properties');

echo json_encode([
    'success' => true,
    'message' => 'Property photos uploaded successfully.',
    'files'   => $responseFiles,
]);

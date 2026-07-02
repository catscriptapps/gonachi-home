<?php
// /server/api/media-delete.php

declare(strict_types=1);

use Src\Service\AuthService;
use Src\Controller\UsersController;

header('Content-Type: application/json');

$userId = AuthService::userId();
$currentLandlord = AuthService::currentLandlord();

if (!$userId && !$currentLandlord) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$picId = $_POST['id'] ?? null;
$type  = $_POST['type'] ?? null;

try {
    // 1. Whitelist configuration
    switch ($type) {

        case 'listing':
            $modelClass = App\Models\ListingPic::class;
            $dirPrefix  = 'images/uploads/listings/';
            $logContext = "Listings";
            break;

        case 'property':
            $modelClass = App\Models\PropertyPic::class;
            $dirPrefix  = 'images/uploads/properties/';
            $logContext = "Properties";
            break;

        default:
            throw new InvalidArgumentException('Invalid media type.');
    }

    // 2. Fetch using the correct primary key 'entry_id'
    $picture = $modelClass::find($picId);

    if (!$picture) {
        throw new RuntimeException('Photo record not found.');
    }

    // 3. Perform the ownership "hop" check
    // Properties are owned by a landlord session; other media types fall back to the user session.
    $isOwner = $type === 'property'
        ? ($currentLandlord && $picture->isOwnedBy((int)$currentLandlord->id))
        : ($userId && $picture->isOwnedBy((int)$userId));

    if (!$isOwner) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this photo.']);
        exit;
    }

    // 4. File system cleanup (Using your avatar-delete logic)
    $fullPath = $dirPrefix . $picture->pic_name;
    $absolutePath = realpath(__DIR__ . '/../../public/' . $fullPath);
    $baseUploadDir = realpath(__DIR__ . '/../../public/images/uploads/');

    if ($absolutePath && strpos($absolutePath, $baseUploadDir) === 0 && file_exists($absolutePath)) {
        if (!unlink($absolutePath)) {
            throw new RuntimeException('File could not be deleted from server.');
        }
    }

    // 5. Database Cleanup
    if ($picture->delete()) {
        UsersController::logActivity("Removed {$type} photo (ID: {$picId})", $logContext);
        echo json_encode(['success' => true, 'message' => 'Photo deleted successfully.']);
    }
} catch (\Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

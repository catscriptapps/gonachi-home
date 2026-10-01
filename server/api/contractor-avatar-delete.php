<?php
// /server/api/contractor-avatar-delete.php
//
// Removes a contractor's uploaded profile photo, reverting their card/detail
// page back to the generated initials placeholder (Src\Utils\ContractorAvatar).
// Admin-only for now, same as contractor-avatar-upload.php. JSON in/out.

declare(strict_types=1);

use App\Models\Contractor;
use Src\Service\AuthService;

header('Content-Type: application/json');

if (!AuthService::isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Only an admin can remove a contractor photo right now.']);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$contractorId = (int) ($input['contractor_id'] ?? 0);
$contractor = $contractorId ? Contractor::find($contractorId) : null;

if (!$contractor) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Contractor not found.']);
    exit;
}

if (empty($contractor->avatar_url)) {
    echo json_encode(['success' => true, 'message' => 'No photo to remove.']);
    exit;
}

$uploadDir = realpath(__DIR__ . '/../../public/images/uploads/contractors/');
$oldPath = $uploadDir ? realpath($uploadDir . '/' . $contractor->avatar_url) : false;

if ($oldPath && str_starts_with($oldPath, $uploadDir) && file_exists($oldPath)) {
    unlink($oldPath);
}

$contractor->avatar_url = null;
$contractor->save();

echo json_encode(['success' => true, 'message' => 'Photo removed.']);

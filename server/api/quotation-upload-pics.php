<?php
// /server/api/quotation-upload-pics.php
//
// Uploads one or more project photos to a quotation (owner-only, capped at
// getMediaLimit()). Called by the shared upload-modal.js from inside the
// view-quotation modal's "Add Photo" button.

declare(strict_types=1);

use Src\Controller\QuotationPicturesController;
use Src\Service\AuthService;
use App\Utils\IdEncoder;

header('Content-Type: application/json');

$userId = AuthService::userId();

if (!$userId) {
    json_response(['success' => false, 'message' => 'Please sign in.'], 401);
}

if (empty($_FILES['images']) || empty($_FILES['images']['tmp_name'][0])) {
    json_response(['success' => false, 'message' => 'No images found.'], 400);
}

// id arrives via the query string (createUploadHandler's endpointUrl), not
// the multipart body.
$encodedId = (string) ($_GET['id'] ?? '');
$id = ctype_digit($encodedId) ? (int) $encodedId : IdEncoder::decode($encodedId);

if (!$id) {
    json_response(['success' => false, 'message' => 'Quotation not found.'], 404);
}

$result = QuotationPicturesController::store($id, $userId, $_FILES['images']);

json_response($result, $result['success'] ? 200 : 422);

<?php
// /server/api/quotation-pic-delete.php
//
// Removes one project photo from a quotation (owner-only) — DB row +
// physical file.

declare(strict_types=1);

use Src\Controller\QuotationPicturesController;
use Src\Service\AuthService;

header('Content-Type: application/json');

$userId = AuthService::userId();

if (!$userId) {
    json_response(['success' => false, 'message' => 'Please sign in.'], 401);
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$picId = (int) ($input['entry_id'] ?? 0);

$result = QuotationPicturesController::delete($picId, $userId);

json_response($result, $result['success'] ? 200 : 403);

<?php
// /server/api/advert-status.php
//
// Admin-only moderation action: approve (-> active), deactivate (-> inactive),
// or reject (-> rejected) an advert. Returns the freshly-rendered admin
// table row so the client can swap it in place.

declare(strict_types=1);

use Src\Controller\AdvertsController;
use Src\Service\AuthService;

header('Content-Type: application/json');

if (!AuthService::isAdmin()) {
    json_response(['success' => false, 'message' => 'Forbidden.'], 403);
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$encodedId = (string) ($input['id'] ?? '');
$status = (string) ($input['status'] ?? '');

$result = AdvertsController::updateStatus($encodedId, $status, AuthService::userId());

if (!$result['success']) {
    json_response($result, 422);
}

json_response([
    'success' => true,
    'status' => $result['status'],
    'rowHtml' => AdvertsController::renderAdminRow($result['advert']),
]);

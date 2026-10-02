<?php
// /server/api/review-moderation.php
//
// Admin-only uphold/dismiss actions for the review-disputes queue — see
// resources/js/utils/review-queue.js, the shared handler across all admin
// moderation queues (reused as-is, action values are just 'uphold'/
// 'dismiss' instead of 'approve'/'reject'). Replaces landlord-report-review.php
// + tenant-report-review.php, which moderated pre-publish reports; this
// moderates POST-publish disputes instead (Src\Controller\ReviewModerationController).

declare(strict_types=1);

use Src\Controller\ReviewModerationController;
use Src\Service\AuthService;

header('Content-Type: application/json');

if (!AuthService::isAdmin()) {
    json_response(['success' => false, 'messages' => ['Forbidden.']], 403);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    json_response(['success' => false, 'messages' => ['Method not allowed.']], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$id = (int) ($input['id'] ?? 0);
$action = $input['action'] ?? '';

if ($id <= 0 || !in_array($action, ['uphold', 'dismiss'], true)) {
    json_response(['success' => false, 'messages' => ['Invalid request.']], 400);
}

$adminUserId = AuthService::userId();

$success = $action === 'uphold'
    ? ReviewModerationController::uphold($id, $adminUserId)
    : ReviewModerationController::dismiss($id, $adminUserId);

if (!$success) {
    json_response(['success' => false, 'messages' => ['That report is no longer pending review.']], 404);
}

json_response([
    'success' => true,
    'messages' => [$action === 'uphold' ? 'Report upheld — the review has been removed from public view.' : 'Report dismissed.'],
]);

<?php
// /server/api/lead-review.php
//
// Admin-only approve/reject actions for the lead review queue. JSON in/out
// via fetch — see resources/js/utils/review-queue.js, the shared handler
// across all three admin moderation queues (lead-review,
// landlord-report-review, contractor-claims-review). Previously this was a
// plain form POST + redirect (full page reload on every click).

declare(strict_types=1);

use Src\Controller\LeadReviewController;
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

if ($id <= 0 || !in_array($action, ['approve', 'reject'], true)) {
    json_response(['success' => false, 'messages' => ['Invalid request.']], 400);
}

$success = $action === 'approve'
    ? LeadReviewController::approve($id)
    : LeadReviewController::reject($id);

if (!$success) {
    json_response(['success' => false, 'messages' => ['That lead is no longer pending review.']], 404);
}

json_response([
    'success' => true,
    'messages' => [$action === 'approve' ? 'Lead approved and published.' : 'Lead rejected.'],
]);

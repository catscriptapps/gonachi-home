<?php
// /server/api/lead-review.php
//
// Admin-only approve/reject actions for the lead review queue. Plain form
// POST + redirect (not JSON) since the review page has no client-side JS
// wired up yet — this keeps the first version dependency-free.

declare(strict_types=1);

use Src\Controller\LeadReviewController;
use Src\Service\AuthService;

if (!AuthService::isAdmin()) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed';
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';
$page = (int) ($_POST['page'] ?? 1);

if ($id > 0) {
    match ($action) {
        'approve' => LeadReviewController::approve($id),
        'reject' => LeadReviewController::reject($id),
        default => null,
    };
}

$redirectUrl = getAssetBase() . 'lead-review' . ($page > 1 ? '?page=' . $page : '');
header('Location: ' . $redirectUrl);
exit;

<?php
// /server/api/report-landlord.php
//
// Handles the Report A Landlord contribution form. Plain POST + redirect
// (not JSON/AJAX) — same dependency-free shape as server/api/lead-review.php.
// Every submitted report starts pending_review; see landlord-report-review.php.

declare(strict_types=1);

use Src\Controller\LandlordDirectoryController;
use Src\Service\AuthService;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed';
    exit;
}

$userId = AuthService::userId();

if (!$userId) {
    header('Location: ' . getAssetBase() . 'report-landlord?error=' . rawurlencode('Please sign in to submit a report.'));
    exit;
}

$result = LandlordDirectoryController::submitReport($_POST, $_FILES, $userId);

if (!$result['success']) {
    header('Location: ' . getAssetBase() . 'report-landlord?error=' . rawurlencode(implode(' ', $result['errors'])));
    exit;
}

header('Location: ' . getAssetBase() . 'report-landlord?submitted=1');
exit;

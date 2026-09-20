<?php
// /server/api/report-tenant.php
//
// Handles the Report A Tenant contribution form — the mirror-image of
// report-landlord.php: landlords reporting problematic tenants instead of
// tenants reporting landlords. JSON in/out via fetch, no page reload. Every
// submitted report starts pending_review; see tenant-report-review.php.

declare(strict_types=1);

use Src\Controller\TenantDirectoryController;
use Src\Service\AuthService;

header('Content-Type: application/json');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'messages' => ['Method not allowed']]);
    exit;
}

$userId = AuthService::userId();

if (!$userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'messages' => ['Please sign in to submit a report.']]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];

$result = TenantDirectoryController::submitReport($input, $userId);

if (!$result['success']) {
    echo json_encode(['success' => false, 'messages' => $result['errors']]);
    exit;
}

echo json_encode(['success' => true, 'messages' => ["Thank you — it's in the review queue and will appear on the tenant's public record once approved."]]);

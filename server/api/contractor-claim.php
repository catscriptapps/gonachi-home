<?php
// /server/api/contractor-claim.php
//
// Handles "Claim This Profile" on the Contractor Discovery directory. JSON
// in/out via fetch — no page reload, matching job-requests.php's convention.
// Creates a pending claim; an admin approves/rejects it via
// contractor-claim-review.php (see ContractorClaimController).

declare(strict_types=1);

use Src\Controller\ContractorClaimController;
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
    echo json_encode(['success' => false, 'messages' => ['Please sign in to claim a profile.']]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$contractorId = (int) ($input['contractor_id'] ?? 0);

if ($contractorId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'messages' => ['Invalid contractor.']]);
    exit;
}

$result = ContractorClaimController::submit($contractorId, $userId, $input);

if (!$result['success']) {
    echo json_encode(['success' => false, 'messages' => $result['errors']]);
    exit;
}

echo json_encode(['success' => true, 'messages' => ["Claim submitted — we'll verify and get back to you shortly."]]);

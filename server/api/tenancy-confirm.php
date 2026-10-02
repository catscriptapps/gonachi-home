<?php
// /server/api/tenancy-confirm.php
//
// The counterpart confirming a tenancy another user claimed about them
// (e.g. a tenant confirming "yes, this person was my landlord at this
// property") — the mutual-confirmation signal behind "✓ Verified Tenancy"
// (landlord_and_tenant_validation.pdf §5, see Src\Service\TenancyService
// ::recomputeVerification()). role=landlord|tenant identifies which side
// the CONFIRMING user is on.

declare(strict_types=1);

use App\Models\Tenancy;
use Src\Service\AuthService;
use Src\Service\TenancyService;

header('Content-Type: application/json');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'messages' => ['Method not allowed']]);
    exit;
}

$userId = AuthService::userId();

if (!$userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'messages' => ['Please sign in to confirm a tenancy.']]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$tenancyId = (int) ($input['tenancy_id'] ?? 0);
$role = (string) ($input['role'] ?? '');

if ($tenancyId <= 0 || !in_array($role, ['landlord', 'tenant'], true)) {
    echo json_encode(['success' => false, 'messages' => ['Invalid request.']]);
    exit;
}

$tenancy = Tenancy::find($tenancyId);

if (!$tenancy) {
    echo json_encode(['success' => false, 'messages' => ['Tenancy not found.']]);
    exit;
}

TenancyService::confirm($tenancy, $userId, $role);

echo json_encode(['success' => true, 'messages' => ['Tenancy confirmed. Thank you.']]);

<?php
// /server/api/tenant-unlock.php
//
// "Unlock Contact" on a tenant search result — spends 1 credit from the
// same Src\Service\LandlordCreditService wallet as
// landlord-contact-unlock.php (landlord_and_tenant_validation.pdf's Step 8
// Paid Plan lists both "Landlord Records" and "Tenant Records" as
// credit-gated unlocks from the same plan).

declare(strict_types=1);

use App\Models\TenantRecord;
use Src\Service\AuthService;
use Src\Service\LandlordCreditService;
use Src\Utils\ContactMasker;

header('Content-Type: application/json');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$userId = AuthService::userId();

if (!$userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please sign in to unlock this contact.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$tenantId = (int) ($input['tenant_id'] ?? 0);

$tenant = $tenantId ? TenantRecord::find($tenantId) : null;
if (!$tenant) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Tenant not found.']);
    exit;
}

$result = LandlordCreditService::unlockTenantReport($userId, $tenant);

if (!$result['success']) {
    json_response($result, 422);
}

$isAdmin = AuthService::isAdmin();
$phoneDisplay = $isAdmin ? $tenant->reference_phone : ContactMasker::mask($tenant->reference_phone);

json_response([
    'success' => true,
    'message' => $result['message'],
    'balance' => $result['balance'],
    'phone' => $phoneDisplay,
]);

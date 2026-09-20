<?php
// /server/api/landlord-contact-unlock.php
//
// "Unlock Contact" on the Landlord & Tenant Validation search results
// (landlord_and_tenant_validation.pdf's Step 8: "Contact Request" — clicking
// Show Contact spends 1 credit from the signed-in user's own trial/paid
// balance). See Src\Service\LandlordCreditService.

declare(strict_types=1);

use App\Models\LandlordRecord;
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
$landlordId = (int) ($input['landlord_id'] ?? 0);

$landlord = $landlordId ? LandlordRecord::find($landlordId) : null;
if (!$landlord) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Landlord not found.']);
    exit;
}

$result = LandlordCreditService::unlockContact($userId, $landlord);

if (!$result['success']) {
    json_response($result, 422);
}

// Same masking as every other credit-gated contact reveal in this app
// (Real Estate Leads, Contractor Discovery) — never the raw number for a
// non-admin, even once "unlocked".
$isAdmin = AuthService::isAdmin();
$phoneDisplay = $isAdmin ? $landlord->phone : ContactMasker::mask($landlord->phone);

json_response([
    'success' => true,
    'message' => $result['message'],
    'balance' => $result['balance'],
    'phone' => $phoneDisplay,
]);

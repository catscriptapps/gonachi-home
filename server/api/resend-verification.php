<?php
// /server/api/resend-verification.php
//
// Re-sends the account activation email — the login modal's "Resend
// Activation Link?" flow (shown when a login attempt fails because the
// account isn't verified yet, see AuthService::login()'s `unverified`
// response). See Src\Controller\VerificationController::resend().

declare(strict_types=1);

use Src\Controller\VerificationController;

header('Content-Type: application/json');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    json_response(['success' => false, 'messages' => ['Method not allowed.']], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];

$result = VerificationController::resend(
    (string) ($input['email'] ?? ''),
    trim((string) ($input['resume_url'] ?? '')) ?: null
);

json_response($result, $result['success'] ? 200 : 422);

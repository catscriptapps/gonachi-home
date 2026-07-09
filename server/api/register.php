<?php
// /server/api/register.php
//
// Public self-registration. Plain POST + redirect (not JSON) — same
// dependency-free shape as server/api/report-landlord.php. New accounts
// auto-activate only when APP_ENV=local (see AuthController::register());
// otherwise an activation email is sent and the user completes signup via
// the /verify-account link.

declare(strict_types=1);

use Src\Controller\AuthController;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed';
    exit;
}

$redirect = sanitizeRedirectTarget($_POST['redirect'] ?? '');
$result = AuthController::register($_POST);

if (!$result['success']) {
    $query = http_build_query([
        'error' => implode(' ', $result['messages']),
        'redirect' => $redirect,
    ]);
    header('Location: ' . getAssetBase() . 'signup?' . $query);
    exit;
}

if ($result['is_registration']) {
    // Not local: activation email sent, account not active yet.
    header('Location: ' . getAssetBase() . 'signup?email_sent=1');
    exit;
}

// Local: instantly active but intentionally NOT auto-logged-in — send them
// back to where they started and prompt them to sign in via the existing
// login modal.
header('Location: ' . getAssetBase() . $redirect . '?registered=1');
exit;

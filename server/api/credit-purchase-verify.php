<?php
// /server/api/credit-purchase-verify.php
//
// POST {reference}: verifies the just-completed Paystack popup checkout and,
// only on a genuine success + matching amount, credits the account. See
// CreditService::confirmPurchase().

declare(strict_types=1);

use Src\Service\AuthService;
use Src\Service\CreditService;

header('Content-Type: application/json');

$userId = AuthService::userId();

if (!$userId) {
    json_response(['success' => false, 'message' => 'Please sign in.'], 401);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $reference = trim((string) ($input['reference'] ?? ''));

    if ($reference === '') {
        json_response(['success' => false, 'message' => 'Missing reference.'], 400);
    }

    $result = CreditService::confirmPurchase($userId, $reference);

    json_response($result, $result['success'] ? 200 : 422);
}

json_response(['success' => false, 'message' => 'Method not allowed'], 405);

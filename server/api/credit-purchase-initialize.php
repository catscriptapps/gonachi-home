<?php
// /server/api/credit-purchase-initialize.php
//
// POST {pack_id}: mints a pending CreditPurchase + Paystack reference for
// the client's inline popup checkout. See CreditService::initiatePurchase().

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
    $packId = (int) ($input['pack_id'] ?? 0);

    $result = CreditService::initiatePurchase($userId, $packId);

    json_response($result, $result['success'] ? 200 : 422);
}

json_response(['success' => false, 'message' => 'Method not allowed'], 405);

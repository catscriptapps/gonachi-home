<?php
// /server/api/quotation-responses.php
//
// The "Connect"/bid handshake: GET ?id= lists the responses on one of the
// caller's own quotations (owner-only). POST sends a new bid by default, or
// accepts/declines one via {action: 'accept'|'decline', response_id} —
// legacy routes this through its Notification system; here it's driven
// straight off the response id since there's no notification layer.

declare(strict_types=1);

use Src\Controller\QuotationResponsesController;
use Src\Service\AuthService;

header('Content-Type: application/json');

$userId = AuthService::userId();

if (!$userId) {
    json_response(['success' => false, 'message' => 'Please sign in.'], 401);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $encodedId = (string) ($_GET['id'] ?? '');
    $result = QuotationResponsesController::listForQuotation($encodedId, $userId);
    json_response($result, $result['success'] ? 200 : 403);
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $action = $input['action'] ?? null;

    if ($action === 'accept' || $action === 'decline') {
        $responseId = (int) ($input['response_id'] ?? 0);
        $result = $action === 'accept'
            ? QuotationResponsesController::accept($responseId, $userId)
            : QuotationResponsesController::decline($responseId, $userId);

        json_response($result, $result['success'] ? 200 : 403);
    }

    $result = QuotationResponsesController::send($input, $userId);
    json_response($result, $result['success'] ? 200 : 422);
}

json_response(['success' => false, 'message' => 'Method not allowed'], 405);

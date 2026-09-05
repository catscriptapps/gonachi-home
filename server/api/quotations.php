<?php
// /server/api/quotations.php
//
// GET: the public "Browse Quotations" feed (?all=1, Active only) or "My
// Quotations" (default, owner's own requests, every status) — search (?q=)
// supported, matching the legacy platform's single index() dispatch.
// POST: create/update (save), or delete via {_method: 'DELETE'} — same
// convention as adverts.php's sibling endpoint in this codebase.

declare(strict_types=1);

use Src\Controller\QuotationsController;
use Src\Service\AuthService;

header('Content-Type: application/json');

$userId = AuthService::userId();

if (!$userId) {
    json_response(['success' => false, 'message' => 'Please sign in.'], 401);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $search = trim((string) ($_GET['q'] ?? '')) ?: null;
    $browsePublic = !empty($_GET['all']);

    $result = $browsePublic
        ? QuotationsController::browse($search, $userId)
        : QuotationsController::mine($search, $userId);

    json_response([
        'success' => true,
        'html' => $result['html'],
        'total' => $result['count'],
    ]);
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];

    if (($input['_method'] ?? '') === 'DELETE') {
        $result = QuotationsController::delete((string) ($input['encoded_id'] ?? ''), $userId);
        json_response($result, $result['success'] ? 200 : 403);
    }

    if (isset($input['intent']) && in_array($input['intent'], ['deactivate', 'reactivate'], true)) {
        $statusId = $input['intent'] === 'reactivate' ? 1 : 2;
        $result = QuotationsController::setStatus((string) ($input['encoded_id'] ?? ''), $statusId, $userId);

        if (!$result['success']) {
            json_response($result, 403);
        }

        json_response([
            'success' => true,
            'cardHtml' => QuotationsController::renderCard($result['quotation'], $userId),
        ]);
    }

    $result = QuotationsController::save($input, $userId);

    if (!$result['success']) {
        json_response(['success' => false, 'messages' => $result['errors']], 422);
    }

    json_response([
        'success' => true,
        'cardHtml' => QuotationsController::renderCard($result['quotation'], $userId),
        'encoded_id' => \App\Utils\IdEncoder::encode((int) $result['quotation']->quotation_id),
    ]);
}

json_response(['success' => false, 'message' => 'Method not allowed'], 405);

<?php
// /server/api/listings.php
//
// GET: the public "Browse Listings" feed (?all=1, Active only, every user)
// or "My Listings" (default, owner's own listings, every status) — search
// (?q=) and pagination (?page=) supported for infinite scroll, matching the
// legacy platform's paginated Listings feed (unlike Quotations/Mentors/
// Adverts here, which render everything in one go).
// POST: create/update (save), delete via {_method: 'DELETE'}, or toggle
// status via {intent: 'deactivate'|'reactivate'} — same convention as
// quotations.php's sibling endpoint in this codebase.

declare(strict_types=1);

use Src\Controller\ListingsController;
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
    $page = max(1, (int) ($_GET['page'] ?? 1));

    $result = $browsePublic
        ? ListingsController::browse($search, $userId, $page)
        : ListingsController::mine($search, $userId, $page);

    json_response([
        'success' => true,
        'html' => $result['html'],
        'total' => $result['total'],
        'hasMore' => $result['hasMore'],
    ]);
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];

    if (($input['_method'] ?? '') === 'DELETE') {
        $result = ListingsController::delete((string) ($input['encoded_id'] ?? ''), $userId);
        json_response($result, $result['success'] ? 200 : 403);
    }

    if (isset($input['intent']) && in_array($input['intent'], ['deactivate', 'reactivate'], true)) {
        $statusId = $input['intent'] === 'reactivate' ? 1 : 2;
        $result = ListingsController::setStatus((string) ($input['encoded_id'] ?? ''), $statusId, $userId);

        if (!$result['success']) {
            json_response($result, 403);
        }

        json_response([
            'success' => true,
            'cardHtml' => ListingsController::renderCard($result['listing'], $userId),
        ]);
    }

    $result = ListingsController::save($input, $userId);

    if (!$result['success']) {
        json_response(['success' => false, 'messages' => $result['errors']], 422);
    }

    json_response([
        'success' => true,
        'cardHtml' => ListingsController::renderCard($result['listing'], $userId),
        'encoded_id' => \App\Utils\IdEncoder::encode((int) $result['listing']->listing_id),
    ]);
}

json_response(['success' => false, 'message' => 'Method not allowed'], 405);

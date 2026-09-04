<?php
// /server/api/adverts.php
//
// GET: the public "Browse Adverts" feed (?all=1, active + targeting-filtered)
// or "My Adverts" (default, owner's own ads, every status) — search (?q=)
// and pagination (?page=) both supported, matching the legacy platform's
// single index() dispatch. POST: create/update (save), or delete via
// {_method: 'DELETE'} — same convention as job-requests.php's sibling
// endpoints in this codebase.

declare(strict_types=1);

use Src\Controller\AdvertsController;
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

    $adverts = $browsePublic
        ? AdvertsController::browse($search, $userId)
        : AdvertsController::mine($search, $userId);

    $html = '';
    foreach ($adverts as $advert) {
        $html .= AdvertsController::renderCard($advert, $userId);
    }

    json_response([
        'success' => true,
        'html' => $html,
        'total' => $adverts->total(),
        'hasMore' => $adverts->hasMorePages(),
    ]);
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];

    if (($input['_method'] ?? '') === 'DELETE') {
        $result = AdvertsController::delete((string) ($input['encoded_id'] ?? ''), $userId);
        json_response($result, $result['success'] ? 200 : 403);
    }

    $result = AdvertsController::save($input, $userId);

    if (!$result['success']) {
        json_response(['success' => false, 'messages' => $result['errors']], 422);
    }

    json_response([
        'success' => true,
        'cardHtml' => AdvertsController::renderCard($result['advert'], $userId),
        'encoded_id' => \App\Utils\IdEncoder::encode((int) $result['advert']->id),
    ]);
}

json_response(['success' => false, 'message' => 'Method not allowed'], 405);

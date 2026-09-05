<?php
// /server/api/mentors.php
//
// GET: the shared mentor directory feed — search (?q=) and target-type
// filter (?target_type=) supported, matching the legacy platform's single
// index() dispatch (no "mine vs all" split — everyone's active mentor
// profiles, including your own, show in the same feed). POST: create/update
// (save), or delete via {_method: 'DELETE'} — same convention as
// adverts.php/quotations.php's sibling endpoints in this codebase.

declare(strict_types=1);

use Src\Controller\MentorsController;
use Src\Service\AuthService;

header('Content-Type: application/json');

$userId = AuthService::userId();

if (!$userId) {
    json_response(['success' => false, 'message' => 'Please sign in.'], 401);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $search = trim((string) ($_GET['q'] ?? '')) ?: null;
    $targetType = (int) ($_GET['target_type'] ?? 0) ?: null;

    $result = MentorsController::browse($search, $targetType, $userId);

    json_response([
        'success' => true,
        'html' => $result['html'],
        'total' => $result['count'],
    ]);
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];

    if (($input['_method'] ?? '') === 'DELETE') {
        $result = MentorsController::delete((string) ($input['encoded_id'] ?? ''), $userId);
        json_response($result, $result['success'] ? 200 : 403);
    }

    $result = MentorsController::save($input, $userId);

    if (!$result['success']) {
        json_response(['success' => false, 'messages' => $result['errors']], 422);
    }

    json_response([
        'success' => true,
        'cardHtml' => MentorsController::renderCard($result['mentor'], $userId),
        'encoded_id' => \App\Utils\IdEncoder::encode((int) $result['mentor']->id),
    ]);
}

json_response(['success' => false, 'message' => 'Method not allowed'], 405);

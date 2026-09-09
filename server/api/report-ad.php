<?php
// /server/api/report-ad.php
//
// POST: report the sponsored-ad slot's current advert. Logs to the shared
// activity feed for admin review (see AdvertsController::report()).

declare(strict_types=1);

use Src\Controller\AdvertsController;
use Src\Service\AuthService;

header('Content-Type: application/json');

$userId = AuthService::userId();

if (!$userId) {
    json_response(['success' => false, 'message' => 'Please sign in.'], 401);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $encodedId = trim((string) ($input['id'] ?? ''));

    if ($encodedId === '') {
        json_response(['success' => false, 'message' => 'Missing ad id.'], 400);
    }

    json_response(AdvertsController::report($encodedId, $userId));
}

json_response(['success' => false, 'message' => 'Method not allowed'], 405);

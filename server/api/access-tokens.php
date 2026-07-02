<?php
// /server/api/access-tokens.php

declare(strict_types=1);

use Src\Controller\AccessTokensController;
use Src\Service\AuthService;

header('Content-Type: application/json; charset=UTF-8');

$input  = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    $controller = new AccessTokensController();

    // 1. HANDLE SEARCH / FETCH (GET) -> ALWAYS REQUIRES AUTH
    if ($method === 'GET') {
        if (!AuthService::currentLandlord()) {
            json_response(['success' => false, 'messages' => ['Authentication required']], 401);
        }
        $controller->index();
        exit;
    }

    // 2. HANDLE CREATE / REVOKE (POST)
    if ($method === 'POST') {
        if (!AuthService::currentLandlord()) {
            json_response(['success' => false, 'messages' => ['Authentication required']], 401);
        }

        $override = strtoupper($input['_method'] ?? '');
        $result = $override === 'REVOKE'
            ? $controller->revoke($input['id'] ?? null)
            : $controller->save($input);

        if (!empty($result['rowHtml'])) {
            $result['rowHtml'] = mb_convert_encoding($result['rowHtml'], 'UTF-8', 'UTF-8');
        }

        json_response($result);
    } else {
        json_response(['success' => false, 'messages' => ['Method not supported']], 405);
    }
} catch (\Throwable $e) {
    json_response(['success' => false, 'messages' => [$e->getMessage()]], 500);
}

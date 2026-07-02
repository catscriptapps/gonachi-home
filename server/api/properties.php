<?php
// /server/api/properties.php

declare(strict_types=1);

use Src\Controller\PropertiesController;
use Src\Service\AuthService;

header('Content-Type: application/json; charset=UTF-8');

$input  = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    $controller = new PropertiesController();
    $override   = strtoupper($input['_method'] ?? '');
    $currentLandlord = AuthService::currentLandlord();

    // 1. HANDLE SEARCH / FETCH (GET) -> ALWAYS REQUIRES AUTH
    if ($method === 'GET') {
        if (!$currentLandlord) {
            json_response(['success' => false, 'messages' => ['Authentication required']], 401);
            exit;
        }
        $controller->index();
        exit;
    }

    // 2. HANDLE SAVE/DELETE (POST)
    if ($method === 'POST') {

        // --- AUTHENTICATION LOGIC GATE ---
        $isDelete = ($override === 'DELETE');
        $isUpdate = ($override === 'PUT' || !empty($input['encoded_id']));

        // Asset modifications or tracking always require an authenticated landlord context session.
        if (!$currentLandlord) {
            json_response(['success' => false, 'messages' => ['Authentication required to modify portfolio properties']], 401);
            exit;
        }

        if ($isDelete) {
            $result = $controller->delete($input['id'] ?? 0);
        } else {
            // Controller manages routing payload between Asset Creation and Updates
            $result = $controller->save($input);
        }

        // Final UTF-8 Clean for JSON safety
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

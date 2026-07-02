<?php
// /server/api/tenants.php

declare(strict_types=1);

use Src\Controller\TenantsController;

header('Content-Type: application/json; charset=UTF-8');

$input  = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    // Guest registration only for now — no auth required to create a tenant account.
    if ($method === 'POST') {
        $controller = new TenantsController();
        json_response($controller->save($input));
    } else {
        json_response(['success' => false, 'messages' => ['Method not supported']], 405);
    }
} catch (\Throwable $e) {
    json_response(['success' => false, 'messages' => [$e->getMessage()]], 500);
}

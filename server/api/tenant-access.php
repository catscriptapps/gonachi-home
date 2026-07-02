<?php
// /server/api/tenant-access.php

declare(strict_types=1);

use Src\Controller\TenantPortalController;

header('Content-Type: application/json; charset=UTF-8');

$input  = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    if ($method !== 'POST') {
        json_response(['success' => false, 'messages' => ['Method not supported']], 405);
    }

    $controller = new TenantPortalController();
    json_response($controller->verifyToken($input));
} catch (\Throwable $e) {
    json_response(['success' => false, 'messages' => [$e->getMessage()]], 500);
}

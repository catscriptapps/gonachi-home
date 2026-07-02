<?php
// /server/api/landlord-services.php

declare(strict_types=1);

use Src\Controller\LandlordServicesController;
use Src\Service\AuthService;

header('Content-Type: application/json; charset=UTF-8');

$input  = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    if ($method !== 'POST') {
        json_response(['success' => false, 'messages' => ['Method not supported']], 405);
    }

    if (!AuthService::currentLandlord()) {
        json_response(['success' => false, 'messages' => ['Authentication required']], 401);
    }

    $controller = new LandlordServicesController();
    $result = $controller->save($input);

    json_response($result);
} catch (\Throwable $e) {
    json_response(['success' => false, 'messages' => [$e->getMessage()]], 500);
}

<?php
// /server/api/views-increment.php

declare(strict_types=1);

use Src\Controller\ListingsController;
use Src\Controller\QuotationsController;

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $type = $input['type'] ?? null;
        $id = $input['id'] ?? null;

        if (!$type || !$id) {
            json_response(['success' => false, 'messages' => ['Missing data']], 400);
        }

        $newCount = null;

        // 💎 THE SWITCH: Route to the existing controllers
        switch ($type) {
            case 'listing':
                $newCount = ListingsController::incrementView((string)$id);
                break;
            default:
                json_response(['success' => false, 'messages' => ['Invalid type']], 400);
        }

        // Check if the increment was successful
        if ($newCount !== null) {
            json_response([
                'success' => true,
                'newCount' => $newCount
            ]);
        }

        json_response([
            'success' => false,
            'messages' => ['Unable to update view count']
        ], 400);
    }

    json_response(['success' => false, 'messages' => ['Method not allowed']], 405);
} catch (Throwable $e) {
    json_response(['success' => false, 'messages' => [$e->getMessage()]], 500);
}

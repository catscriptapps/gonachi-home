<?php
// /server/api/swap-listing-responses.php
//
// "Connect with Owner": GET ?id= lists the responses on one of the caller's
// own Swap Marketplace listings (owner-only). POST sends a new message to a
// listing's owner by default, or accepts/declines one via
// {action: 'accept'|'decline', response_id}. Mirrors Real Estate World's
// quotation-responses.php.

declare(strict_types=1);

use Src\Controller\SwapListingResponsesController;
use Src\Service\AuthService;

header('Content-Type: application/json');

$userId = AuthService::userId();

if (!$userId) {
    json_response(['success' => false, 'message' => 'Please sign in.'], 401);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $encodedId = (string) ($_GET['id'] ?? '');

    // ?mine=1: the caller's own read-only conversation with the owner.
    $result = !empty($_GET['mine'])
        ? SwapListingResponsesController::myThread($encodedId, $userId)
        : SwapListingResponsesController::listForListing($encodedId, $userId);
    json_response($result, $result['success'] ? 200 : 403);
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $action = $input['action'] ?? null;

    if ($action === 'accept' || $action === 'decline') {
        $responseId = (int) ($input['response_id'] ?? 0);
        $result = $action === 'accept'
            ? SwapListingResponsesController::accept($responseId, $userId)
            : SwapListingResponsesController::decline($responseId, $userId);

        json_response($result, $result['success'] ? 200 : 403);
    }

    $result = SwapListingResponsesController::send($input, $userId);
    json_response($result, $result['success'] ? 200 : 422);
}

json_response(['success' => false, 'message' => 'Method not allowed'], 405);

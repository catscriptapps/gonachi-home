<?php
// /server/api/listing-pic-reorder.php
//
// Saves a new picture order for a listing (owner-only). Body:
// { id: <encoded listing id>, order: [<entry_id>, ...] } — the full set of the
// listing's picture entry_ids in the desired order.

declare(strict_types=1);

use Src\Controller\ListingPicturesController;
use Src\Service\AuthService;
use App\Utils\IdEncoder;

header('Content-Type: application/json');

$userId = AuthService::userId();

if (!$userId) {
    json_response(['success' => false, 'message' => 'Please sign in.'], 401);
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$encodedId = (string) ($input['id'] ?? '');
$id = ctype_digit($encodedId) ? (int) $encodedId : IdEncoder::decode($encodedId);
$order = is_array($input['order'] ?? null) ? $input['order'] : [];

if (!$id) {
    json_response(['success' => false, 'message' => 'Listing not found.'], 404);
}

$result = ListingPicturesController::reorder($id, $order, $userId);

json_response($result, $result['success'] ? 200 : 403);

<?php
// /server/api/listing-pictures.php
//
// Lists the photos attached to one listing (view modal's media grid).

declare(strict_types=1);

use Src\Controller\ListingPicturesController;
use Src\Service\AuthService;
use App\Utils\IdEncoder;

header('Content-Type: application/json');

if (!AuthService::userId()) {
    json_response(['success' => false, 'message' => 'Please sign in.'], 401);
}

$encodedId = (string) ($_GET['id'] ?? '');
$id = ctype_digit($encodedId) ? (int) $encodedId : IdEncoder::decode($encodedId);

if (!$id) {
    json_response(['success' => false, 'message' => 'Listing not found.'], 404);
}

json_response(['success' => true, 'pictures' => ListingPicturesController::list($id)]);

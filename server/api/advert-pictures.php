<?php
// /server/api/advert-pictures.php
//
// Lists the pictures attached to one advert (view-advert modal's media grid).

declare(strict_types=1);

use Src\Controller\AdvertsPicturesController;
use Src\Service\AuthService;
use App\Utils\IdEncoder;

header('Content-Type: application/json');

if (!AuthService::userId()) {
    json_response(['success' => false, 'message' => 'Please sign in.'], 401);
}

$encodedId = (string) ($_GET['id'] ?? '');
$id = ctype_digit($encodedId) ? (int) $encodedId : IdEncoder::decode($encodedId);

if (!$id) {
    json_response(['success' => false, 'message' => 'Advert not found.'], 404);
}

json_response(['success' => true, 'pictures' => AdvertsPicturesController::list($id)]);

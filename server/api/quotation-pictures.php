<?php
// /server/api/quotation-pictures.php
//
// Lists the project photos attached to one quotation (view modal's media grid).

declare(strict_types=1);

use Src\Controller\QuotationPicturesController;
use Src\Service\AuthService;
use App\Utils\IdEncoder;

header('Content-Type: application/json');

if (!AuthService::userId()) {
    json_response(['success' => false, 'message' => 'Please sign in.'], 401);
}

$encodedId = (string) ($_GET['id'] ?? '');
$id = ctype_digit($encodedId) ? (int) $encodedId : IdEncoder::decode($encodedId);

if (!$id) {
    json_response(['success' => false, 'message' => 'Quotation not found.'], 404);
}

json_response(['success' => true, 'pictures' => QuotationPicturesController::list($id)]);

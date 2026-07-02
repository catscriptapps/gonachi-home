<?php
// /server/api/property-pictures.php

declare(strict_types=1);

use App\Models\Property;
use App\Utils\IdEncoder;
use Src\Service\AuthService;

header('Content-Type: application/json');

$userId = AuthService::userId();
$currentLandlord = AuthService::currentLandlord();

if (!$userId && !$currentLandlord) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

$rawId = $_GET['id'] ?? null;
$propertyId = ($rawId !== null && !is_numeric($rawId)) ? IdEncoder::decode((string)$rawId) : (int)$rawId;

$property = $propertyId ? Property::find($propertyId) : null;

if (!$property) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Property not found.']);
    exit;
}

// Landlords may only view pictures for their own portfolio properties.
if ($currentLandlord && (int)$property->landlord_id !== (int)$currentLandlord->id) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'You do not have permission to view these photos.']);
    exit;
}

$pictures = $property->pictures()
    ->orderBy('pos_index', 'asc')
    ->get(['entry_id', 'pic_name']);

echo json_encode(['success' => true, 'pictures' => $pictures]);

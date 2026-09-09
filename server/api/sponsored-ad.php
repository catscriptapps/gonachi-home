<?php
// /server/api/sponsored-ad.php
//
// GET: one active, targeting-matched advert for the layout's "Sponsored
// Advertisement" slot (see resources/js/components/sponsored-ad.js). Guests
// always get {ad: null} — Adverts targeting can't be evaluated without a
// viewer, same convention as adverts.php's own Browse feed (login-required).

declare(strict_types=1);

use Src\Controller\AdvertsController;
use Src\Service\AuthService;

header('Content-Type: application/json');

$userId = AuthService::userId();

if (!$userId) {
    json_response(['success' => true, 'ad' => null]);
}

json_response(['success' => true, 'ad' => AdvertsController::sponsoredFor($userId)]);

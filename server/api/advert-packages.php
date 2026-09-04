<?php
// /server/api/advert-packages.php
//
// Lists all package tiers for the create/edit advert form's visual picker.
// No auth required — same as legacy.

declare(strict_types=1);

use App\Models\AdvertPackage;

header('Content-Type: application/json');

$packages = AdvertPackage::orderBy('package_order')->get();

json_response(['success' => true, 'packages' => $packages]);

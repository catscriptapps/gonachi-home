<?php
// /server/api/advert-ctas.php
//
// Lists all call-to-action presets for the create/edit advert form's
// dropdown. No auth required — same as legacy (it's just a static lookup).

declare(strict_types=1);

use App\Models\AdvertCta;

header('Content-Type: application/json');

$ctas = AdvertCta::orderBy('label')->get(['id', 'label']);

json_response(['success' => true, 'ctas' => $ctas]);

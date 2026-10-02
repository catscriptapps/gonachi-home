<?php
// /scripts/backfill-country-scoping.php
//
// One-off, standalone backfill for the country-scoping rollout (see
// Src\Utils\CountryScope, /real-estate-leads/{cc}, /contractor-discovery/{cc},
// /landlord-tenant-validation/{cc}). A full reset (scripts/setup-database.php
// or server/api/reset.php's db-reset icon) now calls this same logic
// automatically — see scripts/reset/backfill-country-ids.php — so this file
// is only needed for an already-deployed install that got the new code
// without going through a reset at all.
//
// Safe to re-run — only touches rows where country_id is still null.
//   php scripts/backfill-country-scoping.php

declare(strict_types=1);

require_once __DIR__ . '/../server/bootstrap.php';
require_once __DIR__ . '/../server/helpers.php';
require_once __DIR__ . '/reset/backfill-country-ids.php';

$messages = backfillCountryIds();

if (!$messages) {
    echo "Nothing to backfill — every row already has a country_id.\n";
    exit;
}

foreach ($messages as $message) {
    echo "{$message}\n";
}

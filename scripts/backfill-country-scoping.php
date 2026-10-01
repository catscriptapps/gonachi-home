<?php
// /scripts/backfill-country-scoping.php
//
// One-off backfill for the country-scoping rollout (see
// Src\Utils\CountryScope, /real-estate-leads/{cc}, /contractor-discovery/{cc},
// /landlord-tenant-validation/{cc}). An already-deployed install gets the new
// country_id columns lazily (via each model's ensure*Columns() method) but
// those columns start out NULL on every existing row — without this script,
// real production data would silently vanish from /ng the moment the column
// exists but is unpopulated. Every existing row here predates the country
// expansion, so it's unambiguously Nigerian.
//
// Safe to re-run — only touches rows where country_id is still null.
//   php scripts/backfill-country-scoping.php

declare(strict_types=1);

require_once __DIR__ . '/../server/bootstrap.php';
require_once __DIR__ . '/../server/helpers.php';

use App\Models\Contractor;
use App\Models\Location;
use App\Models\PropertyRecord;
use App\Models\TenantReport;
use Src\Utils\CountryScope;

Contractor::ensureContractorColumns();
Location::ensureLocationColumns();
PropertyRecord::ensurePropertyColumns();
TenantReport::ensureTenantReportColumns();

$nigeria = CountryScope::IDS['ng'];

$contractorsUpdated = Contractor::whereNull('country_id')->update(['country_id' => $nigeria]);
echo "contractors: backfilled {$contractorsUpdated} row(s) to Nigeria.\n";

// Only the Nigeria root location row — states/areas inherit country via
// parent_id, they never carry their own country_id (see rel-seed.php).
$nigeriaRoot = Location::whereNull('parent_id')->where('name', 'Nigeria')->first();
if ($nigeriaRoot && $nigeriaRoot->country_id === null) {
    $nigeriaRoot->update(['country_id' => $nigeria]);
    echo "locations: backfilled the Nigeria root location.\n";
} else {
    echo "locations: nothing to backfill (Nigeria root not found or already set).\n";
}

$propertiesUpdated = PropertyRecord::whereNull('country_id')->update(['country_id' => $nigeria]);
echo "properties: backfilled {$propertiesUpdated} row(s) to Nigeria.\n";

$tenantReportsUpdated = TenantReport::whereNull('country_id')->update(['country_id' => $nigeria]);
echo "tenant reports: backfilled {$tenantReportsUpdated} row(s) to Nigeria.\n";

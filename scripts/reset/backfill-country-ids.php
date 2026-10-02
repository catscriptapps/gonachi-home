<?php
// /scripts/reset/backfill-country-ids.php
//
// Call AFTER restoreScrapedData() in a full reset. A reset recreates
// cde_contractors/rel_locations/ltv_properties from the current schema
// (which now includes country_id), but restoreScrapedData()
// re-inserts leads/contractors that were backed up from the OLD schema —
// their backed-up row never had a country_id key at all, so they come back
// with it NULL. Every row that predates the country expansion is
// unambiguously Nigerian, so this backfills those NULLs to Nigeria (161).
// Also covers the lazier case of an already-deployed install whose tables
// simply never had the column until just now (see each model's
// ensure*Columns()). Safe to re-run — only touches rows where country_id is
// still null.

declare(strict_types=1);

use App\Models\Contractor;
use App\Models\Location;
use App\Models\PropertyRecord;
use App\Models\Review;
use App\Models\Tenancy;
use Src\Utils\CountryScope;

function backfillCountryIds(): array
{
    $messages = [];
    $nigeria = CountryScope::IDS['ng'];

    Contractor::ensureContractorColumns();
    Location::ensureLocationColumns();
    PropertyRecord::ensurePropertyColumns();

    $contractorsUpdated = Contractor::whereNull('country_id')->update(['country_id' => $nigeria]);
    if ($contractorsUpdated > 0) {
        $messages[] = "backfilled country_id to Nigeria on {$contractorsUpdated} restored contractor(s)";
    }

    // Only the Nigeria root location row — states/areas inherit country via
    // parent_id, they never carry their own country_id (see rel-seed.php).
    $nigeriaRoot = Location::whereNull('parent_id')->where('name', 'Nigeria')->first();
    if ($nigeriaRoot && $nigeriaRoot->country_id === null) {
        $nigeriaRoot->update(['country_id' => $nigeria]);
        $messages[] = "backfilled country_id to Nigeria on the Nigeria root location";
    }

    $propertiesUpdated = PropertyRecord::whereNull('country_id')->update(['country_id' => $nigeria]);
    if ($propertiesUpdated > 0) {
        $messages[] = "backfilled country_id to Nigeria on {$propertiesUpdated} property record(s)";
    }

    $tenanciesUpdated = Tenancy::whereNull('country_id')->update(['country_id' => $nigeria]);
    if ($tenanciesUpdated > 0) {
        $messages[] = "backfilled country_id to Nigeria on {$tenanciesUpdated} tenancy/tenancies";
    }

    $reviewsUpdated = Review::whereNull('country_id')->update(['country_id' => $nigeria]);
    if ($reviewsUpdated > 0) {
        $messages[] = "backfilled country_id to Nigeria on {$reviewsUpdated} review(s)";
    }

    return $messages;
}

<?php
// /scripts/reset/preserve-scraped-data.php
//
// A database reset drops and recreates every table, including the parent
// lookup tables (rel_lead_sources, rel_lead_categories, rel_locations,
// cde_contractor_sources) that rel_leads/cde_contractors point to by ID.
// Simply "not dropping" the leads/contractors tables would leave their
// foreign keys pointing at whatever IDs those parent tables happen to get
// reassigned on reseed — silently corrupting the data instead of protecting
// it. Instead: back up the real (cron-discovered) rows before the reset
// runs, resolving each foreign key to a stable slug/name instead of its raw
// ID, let the full reset proceed exactly as before, then re-insert the
// backed-up rows afterward with their foreign keys re-resolved against the
// freshly reseeded parent tables. This also means future schema changes to
// these tables still deploy normally via a reset — only the precious rows
// get carried through untouched.
//
// Excludes the admin-curated baseline/demo rows seeded by rel-seed.php and
// cde-seed.php — those get recreated fresh by the normal seed step anyway;
// backing them up too would duplicate them. Auto-discovered rows are
// distinguished by having a non-null lead_source_id / contractor_source_id
// pointing at a real cron source (rel-seed.php never creates Lead rows
// itself, so every rel_leads row already qualifies; cde-seed.php's baseline
// contractors never set contractor_source_id, so filtering on it excludes
// them).

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Call BEFORE the drop phase. Snapshots real leads/contractors with their
 * foreign keys resolved to slugs, so they can be re-attached to whatever
 * IDs the reseeded parent tables get.
 *
 * @return array{leads: array<int, array<string, mixed>>, contractors: array<int, array<string, mixed>>}
 */
function backupScrapedData(): array
{
    $backup = ['leads' => [], 'contractors' => []];

    if (Capsule::schema()->hasTable('rel_leads')) {
        $backup['leads'] = Capsule::table('rel_leads')
            ->leftJoin('rel_lead_sources', 'rel_leads.lead_source_id', '=', 'rel_lead_sources.id')
            ->leftJoin('rel_lead_categories', 'rel_leads.category_id', '=', 'rel_lead_categories.id')
            ->leftJoin('rel_locations', 'rel_leads.location_id', '=', 'rel_locations.id')
            ->select(
                'rel_leads.*',
                'rel_lead_sources.slug as _source_slug',
                'rel_lead_categories.slug as _category_slug',
                'rel_locations.slug as _location_slug'
            )
            ->get()
            ->map(fn($row) => (array) $row)
            ->all();
    }

    if (Capsule::schema()->hasTable('cde_contractors')) {
        $backup['contractors'] = Capsule::table('cde_contractors')
            ->join('cde_contractor_sources', 'cde_contractors.contractor_source_id', '=', 'cde_contractor_sources.id')
            ->select(
                'cde_contractors.*',
                'cde_contractor_sources.slug as _source_slug'
            )
            ->get()
            ->map(fn($row) => (array) $row)
            ->all();
    }

    return $backup;
}

/**
 * Call AFTER every table has been recreated and reseeded. Re-inserts the
 * backed-up rows, remapping their foreign keys to the fresh parent-table
 * IDs by slug.
 *
 * @param array{leads: array<int, array<string, mixed>>, contractors: array<int, array<string, mixed>>} $backup
 * @return string[]
 */
function restoreScrapedData(array $backup): array
{
    $messages = [];

    $leadRows = $backup['leads'] ?? [];
    if ($leadRows) {
        $sourceIdBySlug = Capsule::table('rel_lead_sources')->pluck('id', 'slug');
        $categoryIdBySlug = Capsule::table('rel_lead_categories')->pluck('id', 'slug');
        $locationIdBySlug = Capsule::table('rel_locations')->pluck('id', 'slug');

        $inserted = 0;
        $skipped = 0;

        foreach ($leadRows as $row) {
            $sourceSlug = $row['_source_slug'] ?? null;
            $categorySlug = $row['_category_slug'] ?? null;
            $locationSlug = $row['_location_slug'] ?? null;
            unset($row['_source_slug'], $row['_category_slug'], $row['_location_slug'], $row['id']);

            // No dedup key without a source — skip rather than insert an
            // orphaned row that a future cron run could never recognize.
            if (!$sourceSlug || !isset($sourceIdBySlug[$sourceSlug])) {
                $skipped++;
                continue;
            }

            $row['lead_source_id'] = $sourceIdBySlug[$sourceSlug];
            $row['category_id'] = $categorySlug ? ($categoryIdBySlug[$categorySlug] ?? null) : null;
            $row['location_id'] = $locationSlug ? ($locationIdBySlug[$locationSlug] ?? null) : null;

            Capsule::table('rel_leads')->insert($row);
            $inserted++;
        }

        $messages[] = "restored {$inserted} previously-scraped lead(s) from before the reset"
            . ($skipped ? " ({$skipped} skipped — their source no longer exists)" : '');
    }

    $contractorRows = $backup['contractors'] ?? [];
    if ($contractorRows) {
        $sourceIdBySlug = Capsule::table('cde_contractor_sources')->pluck('id', 'slug');

        $inserted = 0;
        $skipped = 0;

        foreach ($contractorRows as $row) {
            $sourceSlug = $row['_source_slug'] ?? null;
            unset($row['_source_slug'], $row['id']);

            if (!$sourceSlug || !isset($sourceIdBySlug[$sourceSlug])) {
                $skipped++;
                continue;
            }

            $row['contractor_source_id'] = $sourceIdBySlug[$sourceSlug];

            // The claiming user's account may not exist after the reset
            // (the users table is fully reseeded too) — a stale
            // claimed_by_user_id would be worse than losing the claim, so
            // restored contractors always come back unclaimed.
            $row['claimed_by_user_id'] = null;
            $row['claim_status'] = 'unclaimed';

            Capsule::table('cde_contractors')->insert($row);
            $inserted++;
        }

        $messages[] = "restored {$inserted} previously-discovered contractor(s) from before the reset (claims reset to unclaimed)"
            . ($skipped ? " ({$skipped} skipped — their source no longer exists)" : '');
    }

    return $messages;
}

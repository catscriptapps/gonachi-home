<?php
// /scripts/backfill-lead-slugs.php
//
// One-off backfill: generates a slug (LeadsController::buildUniqueSlug())
// for existing rel_leads rows that have a property_type and a specific
// (depth-2) location but no slug yet — i.e. leads ingested before this
// feature shipped. A row missing property_type/location is left with
// slug = null, same as buildUniqueSlug() itself would leave it.
//
// Safe to re-run — only touches rows where slug is still null.
//   php scripts/backfill-lead-slugs.php

declare(strict_types=1);

require_once __DIR__ . '/../server/bootstrap.php';
require_once __DIR__ . '/../server/helpers.php';

use App\Models\Lead;
use Src\Controller\LeadsController;

Lead::ensureLeadColumns();

$candidates = Lead::with(['location.parent'])->whereNull('slug')->get();
$updated = 0;

foreach ($candidates as $lead) {
    $slug = LeadsController::buildUniqueSlug($lead);

    if ($slug) {
        $lead->update(['slug' => $slug]);
        $updated++;
    }
}

echo "Checked {$candidates->count()} lead(s) missing a slug, generated {$updated}.\n";

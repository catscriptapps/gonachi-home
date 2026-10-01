<?php
// /scripts/backfill-contractor-slugs.php
//
// One-off backfill: generates a slug (ContractorController::buildUniqueSlug())
// for existing cde_contractors rows that don't have one yet — i.e.
// contractors created before this feature shipped. Mirrors
// scripts/backfill-lead-slugs.php exactly.
//
// Safe to re-run — only touches rows where slug is still null.
//   php scripts/backfill-contractor-slugs.php

declare(strict_types=1);

require_once __DIR__ . '/../server/bootstrap.php';
require_once __DIR__ . '/../server/helpers.php';

use App\Models\Contractor;
use Src\Controller\ContractorController;

Contractor::ensureContractorColumns();

$candidates = Contractor::whereNull('slug')->get();
$updated = 0;

foreach ($candidates as $contractor) {
    $slug = ContractorController::buildUniqueSlug($contractor);

    if ($slug) {
        $contractor->update(['slug' => $slug]);
        $updated++;
    }
}

echo "Checked {$candidates->count()} contractor(s) missing a slug, generated {$updated}.\n";

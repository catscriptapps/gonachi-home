<?php
// /scripts/backfill-lead-contact-info.php
//
// One-off backfill: parses phone/email out of existing rel_leads rows'
// raw_text (+ contact_info_raw, when present) for leads scraped before
// LeadIngestionService started populating these columns at ingest time.
// Safe to re-run — only touches rows where phone or email is still null,
// and never deletes/overwrites anything.
//
// Run once after deploying this change (and again any time a connector's
// scraped text format changes enough that old rows might now parse):
//   php scripts/backfill-lead-contact-info.php

declare(strict_types=1);

require_once __DIR__ . '/../server/bootstrap.php';
require_once __DIR__ . '/../server/helpers.php';

use App\Models\Lead;
use Src\Service\ContactInfoParser;

Lead::ensureContactColumns();

$candidates = Lead::where(function ($q) {
    $q->whereNull('phone')->orWhereNull('email');
})->get();

$updated = 0;

foreach ($candidates as $lead) {
    $text = trim(($lead->contact_info_raw ?? '') . ' ' . $lead->raw_text);

    $phone = $lead->phone ?? ContactInfoParser::extractPhone($text);
    $email = $lead->email ?? ContactInfoParser::extractEmail($text);

    if ($phone !== $lead->phone || $email !== $lead->email) {
        $lead->phone = $phone;
        $lead->email = $email;
        $lead->save();
        $updated++;
    }
}

echo "Checked {$candidates->count()} lead(s) missing phone/email, updated {$updated}.\n";

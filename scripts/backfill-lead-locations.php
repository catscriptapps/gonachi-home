<?php
// /scripts/backfill-lead-locations.php
//
// Re-runs ONLY location detection (LeadIntentClassifier::findBestLocationName())
// against every existing lead's stored raw_text, using the new depth-aware
// matcher (LeadIngestionService::locationIndex()) — the original ingest-time
// pass used a plain "first/longest name found" heuristic that picked the
// country ("Nigeria") over a genuinely specific area mentioned in the same
// text almost every time, since property listings routinely name the
// country alongside the actual area. Does NOT touch request_type,
// property_type, budget, or anything else already reviewed — location only.
//
// Safe to re-run — always overwrites location_id/location_raw with the
// current best match (deterministic, so re-running finds the same result
// once the location tree itself stops changing).
//   php scripts/backfill-lead-locations.php

declare(strict_types=1);

require_once __DIR__ . '/../server/bootstrap.php';
require_once __DIR__ . '/../server/helpers.php';

use App\Models\Lead;
use App\Models\Location;
use Src\Service\LeadIntentClassifier;
use Src\Service\LeadIngestionService;

$classifier = new LeadIntentClassifier(LeadIngestionService::locationIndex());

$leads = Lead::all();
$changed = 0;

foreach ($leads as $lead) {
    $bestName = $classifier->findBestLocationName((string) $lead->raw_text);
    $newLocation = $bestName ? Location::where('name', $bestName)->first() : null;

    $newLocationId = $newLocation?->id;
    $newLocationRaw = $bestName;

    if ($newLocationId !== $lead->location_id || $newLocationRaw !== $lead->location_raw) {
        $lead->location_id = $newLocationId;
        $lead->location_raw = $newLocationRaw;
        $lead->save();
        $changed++;
    }
}

echo "Checked {$leads->count()} lead(s), updated location on {$changed}.\n";

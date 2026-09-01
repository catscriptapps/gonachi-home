<?php
// /scripts/cron/run-contractor-discovery.php
//
// Entry point for the scheduled contractor-discovery run (see
// contractor_discovery.pdf's Phase 1 "Data Collection"). Not wired to any
// in-process scheduler (none exists in this app) — trigger it externally,
// e.g. Windows Task Scheduler or a crontab entry:
//   php scripts/cron/run-contractor-discovery.php
//
// Polls every active cde_contractor_sources row that is due (per its own
// poll_interval_minutes), runs its connector, and stores new contractor
// profiles directly (no review step — see ContractorIngestionService),
// logging a cde_contractor_discovery_runs row per source per run.

declare(strict_types=1);

require_once __DIR__ . '/../../server/bootstrap.php';

use App\Models\ContractorDiscoveryRun;
use App\Models\ContractorSource;
use Carbon\Carbon;
use Src\Service\ContractorIngestionService;

// File-based lock so overlapping cron fires can't run concurrently.
$lockFile = __DIR__ . '/.contractor-discovery.lock';
$lockHandle = fopen($lockFile, 'c');
if (!$lockHandle || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
    fwrite(STDERR, "Another contractor discovery run is already in progress. Exiting.\n");
    exit(1);
}

try {
    $sources = ContractorSource::where('is_active', true)
        ->get()
        ->filter(fn (ContractorSource $source) => $source->isDueForPoll());

    if ($sources->isEmpty()) {
        echo "No contractor sources are due for polling.\n";
        exit(0);
    }

    $ingestionService = new ContractorIngestionService();

    foreach ($sources as $source) {
        echo "Polling source: {$source->name}\n";

        $run = ContractorDiscoveryRun::create([
            'contractor_source_id' => $source->id,
            'started_at' => Carbon::now(),
            'status' => 'running',
        ]);

        try {
            $connectorClass = $source->connector_class;

            if (!class_exists($connectorClass)) {
                throw new \RuntimeException("Connector class not found: {$connectorClass}");
            }

            $connector = new $connectorClass();
            $candidates = $connector->fetchCandidates($source->config ?? []);

            $stats = $ingestionService->ingest($source, $candidates);

            $run->update([
                'finished_at' => Carbon::now(),
                'items_found' => $stats['found'],
                'items_new' => $stats['new'],
                'items_duplicate' => $stats['duplicate'],
                'items_rejected' => $stats['rejected'],
                'status' => 'success',
            ]);

            $source->update(['last_polled_at' => Carbon::now()]);

            echo "  found={$stats['found']} new={$stats['new']} duplicate={$stats['duplicate']} rejected={$stats['rejected']}\n";
        } catch (\Throwable $e) {
            $run->update([
                'finished_at' => Carbon::now(),
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            fwrite(STDERR, "  Source '{$source->name}' failed: {$e->getMessage()}\n");
        }
    }
} finally {
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
}

<?php
// /scripts/cron/sweep-pending-uploads.php
//
// Safety-net for orphaned compose-modal photo uploads
// (resources/js/utils/compose-photo-strip.js): the client discards an
// upload the moment it's removed from the Photos strip or the compose
// modal is dismissed unsaved, but nothing client-side can run if the tab
// crashes, the browser is killed, or the network drops mid-flow. This
// sweeps anything that slipped through — a pending_photo_uploads row older
// than the grace period that still isn't attached to any real record — see
// Src\Service\PendingUploadTracker::sweep().
//
// Not wired to any in-process scheduler (none exists in this app) — trigger
// it externally, e.g. Windows Task Scheduler or a crontab entry:
//   php scripts/cron/sweep-pending-uploads.php

declare(strict_types=1);

require_once __DIR__ . '/../../server/bootstrap.php';

use Src\Service\PendingUploadTracker;

// File-based lock so overlapping cron fires can't run concurrently.
$lockFile = __DIR__ . '/.sweep-pending-uploads.lock';
$lockHandle = fopen($lockFile, 'c');
if (!$lockHandle || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
    fwrite(STDERR, "Another pending-uploads sweep is already in progress. Exiting.\n");
    exit(1);
}

try {
    // One hour is comfortably longer than any real compose session — long
    // enough that this can never race a save that's still in flight for a
    // just-uploaded file.
    $result = PendingUploadTracker::sweep(3600);

    echo "Checked {$result['checked']} stale pending upload(s), deleted {$result['deleted']}.\n";
} finally {
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
}

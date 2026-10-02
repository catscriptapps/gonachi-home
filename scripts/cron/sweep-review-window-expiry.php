<?php
// /scripts/cron/sweep-review-window-expiry.php
//
// Releases any review still `pending` whose tenancy's 30-day double-blind
// window has closed without a counterpart ever showing up — see
// Src\Service\ReviewService::maybeRelease(). The submission-time check in
// ReviewService::submit() handles the "both parties submitted" release path
// immediately; this sweep is the ONLY path that can release a LONE review.
//
// Not wired to any in-process scheduler (none exists in this app) — trigger
// it externally, e.g. Windows Task Scheduler or a crontab entry:
//   php scripts/cron/sweep-review-window-expiry.php

declare(strict_types=1);

require_once __DIR__ . '/../../server/bootstrap.php';

use App\Models\Tenancy;
use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager as Capsule;
use Src\Service\ReviewService;

$lockFile = __DIR__ . '/.sweep-review-window-expiry.lock';
$lockHandle = fopen($lockFile, 'c');
if (!$lockHandle || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
    fwrite(STDERR, "Another review-window-expiry sweep is already in progress. Exiting.\n");
    exit(1);
}

try {
    $tenancyIds = Tenancy::whereNotNull('review_window_closes_at')
        ->where('review_window_closes_at', '<=', Carbon::now())
        ->whereHas('reviews', fn ($q) => $q->where('release_status', 'pending'))
        ->pluck('id');

    $releasedCount = 0;

    foreach ($tenancyIds as $tenancyId) {
        Capsule::connection()->transaction(function () use ($tenancyId, &$releasedCount) {
            $tenancy = Tenancy::where('id', $tenancyId)->lockForUpdate()->first();
            if (!$tenancy) {
                return;
            }

            $pendingBefore = $tenancy->reviews()->where('release_status', 'pending')->count();
            ReviewService::maybeRelease($tenancy);
            $releasedCount += $pendingBefore;
        });
    }

    echo "Checked " . $tenancyIds->count() . " expired tenancy window(s), released {$releasedCount} review(s).\n";
} finally {
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
}

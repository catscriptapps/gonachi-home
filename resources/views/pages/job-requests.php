<?php
// /resources/views/pages/job-requests.php

declare(strict_types=1);

/**
 * Gonachi Contractor Discovery Engine - Job Request Feed
 *
 * UI-first pass matching contractor_discovery.pdf's "Job Request Engine"
 * (Phase 2): homeowners post a job, contractors browse and respond. No
 * backend yet — content below is illustrative.
 */

$jobRequests = [
    [
        'service' => 'Plumber Needed — Burst Pipe Repair',
        'category' => 'Plumbing',
        'location' => 'Lekki, Lagos',
        'budget' => '₦150,000',
        'posted' => '2 Hours Ago',
        'status' => 'Open',
        'description' => 'Kitchen sink pipe burst and is leaking under the cabinet. Need someone available today or tomorrow morning.',
        'bids' => 3,
    ],
    [
        'service' => 'Electrician — Rewire 3 Bedroom Flat',
        'category' => 'Electrical',
        'location' => 'Ikeja, Lagos',
        'budget' => '₦300,000',
        'posted' => '5 Hours Ago',
        'status' => 'Open',
        'description' => 'Old wiring needs replacing across the flat ahead of a repaint. Certified electrician preferred.',
        'bids' => 1,
    ],
    [
        'service' => 'General Contractor — Fence Wall Repair',
        'category' => 'General Contracting',
        'location' => 'Yaba, Lagos',
        'budget' => '₦450,000',
        'posted' => '1 Day Ago',
        'status' => 'Closed',
        'description' => 'Perimeter fence wall collapsed after heavy rain. Needs rebuilding, roughly 20 meters.',
        'bids' => 6,
    ],
];
?>
<div class="space-y-6">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Job Requests</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Homeowners posting service needs, matched to your category and location.</p>
        </div>
        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-secondary-50 dark:bg-secondary-950/40 text-secondary-600 dark:text-secondary-400 whitespace-nowrap">
            <?= count(array_filter($jobRequests, fn($j) => $j['status'] === 'Open')) ?> Open Requests
        </span>
    </div>

    <div class="space-y-4">
        <?php foreach ($jobRequests as $job): ?>
            <?php $isOpen = $job['status'] === 'Open'; ?>
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 shadow-sm <?= $isOpen ? 'hover:border-secondary-500/50 transition-all' : 'opacity-70' ?>">
                <div class="flex items-start justify-between gap-4 mb-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-secondary-100 text-secondary-800 dark:bg-secondary-950 dark:text-secondary-400">
                                <?= htmlspecialchars($job['category']) ?>
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?= $isOpen ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400' ?>">
                                <?= htmlspecialchars($job['status']) ?>
                            </span>
                        </div>
                        <h4 class="text-base font-bold text-gray-900 dark:text-white mt-2"><?= htmlspecialchars($job['service']) ?></h4>
                    </div>
                    <span class="text-xs font-medium text-gray-400 whitespace-nowrap"><?= htmlspecialchars($job['posted']) ?></span>
                </div>

                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4"><?= htmlspecialchars($job['description']) ?></p>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 my-4 text-sm border-t border-b border-gray-100 dark:border-gray-800/80 py-3">
                    <div>
                        <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Location</span>
                        <span class="font-medium text-gray-700 dark:text-gray-300"><?= htmlspecialchars($job['location']) ?></span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Budget</span>
                        <span class="font-medium text-gray-700 dark:text-gray-300"><?= htmlspecialchars($job['budget']) ?></span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Bids So Far</span>
                        <span class="font-medium text-gray-700 dark:text-gray-300"><?= $job['bids'] ?></span>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button <?= $isOpen ? '' : 'disabled' ?> title="<?= $isOpen ? '' : 'Coming soon' ?>" class="inline-flex items-center px-4 py-2 <?= $isOpen ? 'bg-gray-900 hover:bg-gray-800 dark:bg-secondary-600 dark:hover:bg-secondary-500 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-800 text-gray-400 cursor-not-allowed' ?> font-bold text-xs rounded-lg transition-colors tracking-wide">
                        <?= $isOpen ? 'Submit A Quote' : 'Request Closed' ?>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="bg-secondary-50 dark:bg-secondary-950/40 rounded-xl p-5 border border-secondary-100 dark:border-secondary-900/30 flex items-center justify-between gap-4 flex-wrap">
        <div>
            <h4 class="text-sm font-bold text-secondary-900 dark:text-secondary-300">Free tier: limited bids per month</h4>
            <p class="text-xs text-secondary-700 dark:text-secondary-400 mt-1">Upgrade for unlimited bids, priority placement, and instant lead notifications.</p>
        </div>
        <button disabled title="Coming soon" class="px-4 py-2 bg-white dark:bg-gray-900 text-secondary-600 dark:text-secondary-400 border border-secondary-200 dark:border-secondary-900/50 font-bold text-xs rounded-lg cursor-not-allowed whitespace-nowrap">
            Go Premium
        </button>
    </div>
</div>

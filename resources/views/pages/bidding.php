<?php
// /resources/views/pages/bidding.php

declare(strict_types=1);

/**
 * Gonachi Contractor Discovery Engine - Bidding & Quotes
 *
 * UI-first pass matching contractor_discovery.pdf's "Bidding System":
 * a contractor's own submitted quotes/proposals and their status. No
 * backend yet — content below is illustrative.
 */

$bids = [
    [
        'job' => 'Plumber Needed — Burst Pipe Repair',
        'location' => 'Lekki, Lagos',
        'quote' => '₦140,000',
        'submitted' => '1 Hour Ago',
        'status' => 'Pending',
    ],
    [
        'job' => 'Bathroom Retile — 2 Bathrooms',
        'location' => 'Ajah, Lagos',
        'quote' => '₦220,000',
        'submitted' => '1 Day Ago',
        'status' => 'Accepted',
    ],
    [
        'job' => 'Roof Leak Inspection',
        'location' => 'Victoria Island, Lagos',
        'quote' => '₦60,000',
        'submitted' => '3 Days Ago',
        'status' => 'Declined',
    ],
];

$statusStyles = [
    'Pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400',
    'Accepted' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400',
    'Declined' => 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400',
];
?>
<div class="space-y-6">

    <div>
        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Bidding & Quotes</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Track every quote you've submitted and its response status.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 text-center shadow-sm">
            <span class="block text-2xl font-bold text-amber-600 dark:text-amber-400"><?= count(array_filter($bids, fn($b) => $b['status'] === 'Pending')) ?></span>
            <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Awaiting Response</span>
        </div>
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 text-center shadow-sm">
            <span class="block text-2xl font-bold text-emerald-600 dark:text-emerald-400"><?= count(array_filter($bids, fn($b) => $b['status'] === 'Accepted')) ?></span>
            <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Accepted</span>
        </div>
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 text-center shadow-sm">
            <span class="block text-2xl font-bold text-secondary-600 dark:text-secondary-400"><?= count($bids) ?></span>
            <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Total Bids This Month</span>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl divide-y divide-gray-100 dark:divide-gray-800 overflow-hidden shadow-sm">
        <?php foreach ($bids as $bid): ?>
            <div class="flex items-center justify-between gap-4 p-5 flex-wrap">
                <div>
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($bid['job']) ?></h4>
                    <p class="text-xs text-gray-400 mt-1"><?= htmlspecialchars($bid['location']) ?> &middot; Submitted <?= htmlspecialchars($bid['submitted']) ?></p>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm font-bold text-gray-700 dark:text-gray-300"><?= htmlspecialchars($bid['quote']) ?></span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?= $statusStyles[$bid['status']] ?>">
                        <?= htmlspecialchars($bid['status']) ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="bg-secondary-50 dark:bg-secondary-950/40 rounded-xl p-5 border border-secondary-100 dark:border-secondary-900/30 flex items-center justify-between gap-4 flex-wrap">
        <div>
            <h4 class="text-sm font-bold text-secondary-900 dark:text-secondary-300">Want more visibility on new job requests?</h4>
            <p class="text-xs text-secondary-700 dark:text-secondary-400 mt-1">Premium contractors get a Verified Badge and priority placement in search results.</p>
        </div>
        <button disabled title="Coming soon" class="px-4 py-2 bg-white dark:bg-gray-900 text-secondary-600 dark:text-secondary-400 border border-secondary-200 dark:border-secondary-900/50 font-bold text-xs rounded-lg cursor-not-allowed whitespace-nowrap">
            Go Premium
        </button>
    </div>
</div>

<?php
// /resources/views/pages/bidding.php

declare(strict_types=1);

/**
 * Gonachi Contractor Discovery Engine - Bidding & Quotes
 *
 * A contractor's own submitted quotes ("Submit A Quote" on /job-requests)
 * and their response status — real data via
 * Src\Controller\JobRequestResponsesController::myBids(), mirroring how
 * Real Estate World's Quotations module surfaces bid/response status
 * inline without a notification system.
 *
 * @var bool $isLoggedIn
 * @var string $baseUrl
 */

use Src\Controller\JobRequestController;
use Src\Controller\JobRequestResponsesController;
use Src\Service\AuthService;

if (!$isLoggedIn) {
    include __DIR__ . '/auth-required.php';
    return;
}

$currentUserId = AuthService::userId();
$categoryLabels = JobRequestController::categoryLabels();
$bids = JobRequestResponsesController::myBids($currentUserId);

$awaitingCount = count(array_filter($bids, fn ($b) => $b['status'] === 'pending'));
$acceptedCount = count(array_filter($bids, fn ($b) => $b['status'] === 'accepted'));
$thisMonthCount = count(array_filter($bids, fn ($b) => $b['created_at'] && $b['created_at']->isCurrentMonth()));

$statusStyles = [
    'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400',
    'accepted' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400',
    'declined' => 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400',
];
?>
<div class="space-y-6">

    <?php
    $breadcrumbs = [
        ['label' => 'Contractor Discovery', 'href' => $baseUrl . 'contractor-discovery'],
        ['label' => 'Bidding & Quotes'],
    ];
    $breadcrumbAccent = 'secondary';
    include __DIR__ . '/../components/breadcrumbs.php';
    ?>

    <div>
        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Bidding & Quotes</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Track every quote you've submitted and its response status.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 text-center shadow-sm">
            <span class="block text-2xl font-bold text-amber-600 dark:text-amber-400"><?= $awaitingCount ?></span>
            <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Awaiting Response</span>
        </div>
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 text-center shadow-sm">
            <span class="block text-2xl font-bold text-emerald-600 dark:text-emerald-400"><?= $acceptedCount ?></span>
            <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Accepted</span>
        </div>
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 text-center shadow-sm">
            <span class="block text-2xl font-bold text-secondary-600 dark:text-secondary-400"><?= $thisMonthCount ?></span>
            <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Total Bids This Month</span>
        </div>
    </div>

    <?php if (empty($bids)): ?>
        <div class="bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-800 rounded-xl p-10 text-center">
            <svg class="h-8 w-8 text-gray-300 dark:text-gray-700 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" /></svg>
            <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300">You haven't submitted any quotes yet</h4>
            <p class="text-xs text-gray-400 dark:text-gray-500 max-w-sm mx-auto mt-1">Head over to <a href="<?= $baseUrl ?>job-requests" data-partial class="font-semibold text-secondary-600 hover:underline">Job Requests</a> and click "Submit A Quote" on one that fits.</p>
        </div>
    <?php else: ?>
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl divide-y divide-gray-100 dark:divide-gray-800 overflow-hidden shadow-sm">
            <?php foreach ($bids as $bid): ?>
                <div class="flex items-center justify-between gap-4 p-5 flex-wrap">
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($categoryLabels[$bid['service_category']] ?? ucfirst($bid['service_category'])) ?></h4>
                        <p class="text-xs text-gray-400 mt-1"><?= htmlspecialchars($bid['location']) ?> &middot; Submitted <?= htmlspecialchars($bid['created_at']?->diffForHumans() ?? '') ?></p>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-sm font-bold text-gray-700 dark:text-gray-300"><?= $bid['quote_amount'] !== null ? '&#8358;' . htmlspecialchars($bid['quote_amount']) : 'No amount given' ?></span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?= $statusStyles[$bid['status']] ?? $statusStyles['pending'] ?>">
                            <?= htmlspecialchars(ucfirst($bid['status'])) ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

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

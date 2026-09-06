<?php
// /resources/views/pages/quotations.php

declare(strict_types=1);

/**
 * Real Estate World - Quotations (public "Browse Quotations" feed)
 *
 * Ported from the legacy gonachi/ platform. Guests see marketing copy only
 * (they never see the request grid — matches legacy exactly); logged-in
 * users see every Active quotation request, searchable.
 *
 * @var bool $isLoggedIn
 * @var string $baseUrl
 * @var string $assetBase
 */

use Src\Controller\QuotationsController;
use Src\Service\AuthService;

$breadcrumbs = [['label' => 'Quotations']];
$breadcrumbAccent = 'teal';

$viewerId = $isLoggedIn ? AuthService::userId() : null;
$feedHtml = '';
$totalActive = QuotationsController::totalActiveCount();

if ($isLoggedIn) {
    $result = QuotationsController::browse(null, $viewerId);
    $feedHtml = $result['html'];
}
?>
<div class="space-y-6">
    <?php include __DIR__ . '/../components/breadcrumbs.php'; ?>

    <section class="relative overflow-hidden rounded-3xl shadow-sm bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-6 p-6 sm:p-10">
            <div>
                <span class="inline-block text-xs font-semibold tracking-[0.2em] text-teal-600 dark:text-teal-400 uppercase mb-2">Quotations</span>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Smart Contractor Quotes</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 max-w-md">Describe your project, upload photos, and receive competitive bids from contractors — free to post.</p>

                <div class="flex flex-wrap items-center gap-3 mt-5">
                    <?php if ($isLoggedIn): ?>
                        <a href="<?= $baseUrl ?>my-quotations" data-partial class="inline-flex items-center px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm rounded-lg transition-colors shadow-sm">
                            Request a Quotation
                        </a>
                    <?php else: ?>
                        <a href="<?= $baseUrl ?>login" data-login-button class="inline-flex items-center px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm rounded-lg transition-colors shadow-sm">
                            Request a Quotation
                        </a>
                        <button type="button" class="register-btn inline-flex items-center px-6 py-2.5 border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300 font-bold text-sm rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/60 transition-colors">
                            Register Now
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex items-center space-x-4 bg-gray-50 dark:bg-gray-800/60 p-2 rounded-xl border border-gray-200 dark:border-gray-800 flex-shrink-0">
                <div class="px-4 py-2 text-center">
                    <span class="block text-2xl font-bold text-teal-600 dark:text-teal-400"><?= $totalActive ?></span>
                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Active Requests</span>
                </div>
                <div class="px-4 py-2 border-l border-gray-200 dark:border-gray-800 text-center">
                    <span class="block text-2xl font-bold text-primary-600 dark:text-primary-400">Free</span>
                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">To Post</span>
                </div>
            </div>
        </div>
    </section>

    <?php if (!$isLoggedIn): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <?php
            $tiles = [
                ['Describe Need', 'Detail your project in our smart form.'],
                ['Upload Media', 'Add photos for more accurate bids.'],
                ['Receive Bids', 'Get competitive pricing from contractors.'],
                ['Hire & Track', 'Choose the best fit and get to work.'],
            ];
            foreach ($tiles as $i => $tile): ?>
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5">
                    <div class="w-10 h-10 rounded-xl bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 flex items-center justify-center mb-3">
                        <span class="text-sm font-black"><?= $i + 1 ?></span>
                    </div>
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-1"><?= $tile[0] ?></h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400"><?= $tile[1] ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="flex items-center justify-between gap-4">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Browse Quotations</h3>
            <div class="relative w-full max-w-xs">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" id="quote-search-input" placeholder="Search quotations..." class="w-full pl-9 pr-3 py-2 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none text-gray-900 dark:text-white" />
            </div>
        </div>

        <div id="quotes-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 <?= $feedHtml === '' ? 'hidden' : '' ?>">
            <?= $feedHtml ?>
        </div>

        <div id="empty-quotes-state" class="<?= $feedHtml !== '' ? 'hidden' : '' ?> bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-800 rounded-2xl p-10 text-center">
            <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300">No quotations to show yet</h4>
            <p class="text-xs text-gray-400 dark:text-gray-500 max-w-sm mx-auto mt-1">Active quotation requests will appear here.</p>
        </div>

        <?php include __DIR__ . '/../components/quotations/view-quotation-modal.php'; ?>
    <?php endif; ?>
</div>

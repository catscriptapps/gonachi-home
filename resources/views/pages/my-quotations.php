<?php
// /resources/views/pages/my-quotations.php

declare(strict_types=1);

/**
 * Real Estate World - My Quotations
 *
 * The signed-in user's own quotation requests, every status (active/
 * archived) — full create/edit/delete control, plus the bid "Responses"
 * list surfaced in the view modal. Ported from the legacy gonachi/
 * platform's my-quotations.php.
 *
 * @var bool $isLoggedIn
 * @var string $baseUrl
 * @var string $assetBase
 */

if (!$isLoggedIn) {
    include __DIR__ . '/auth-required.php';
    return;
}

use Src\Controller\QuotationsController;
use Src\Service\AuthService;

$breadcrumbs = [['label' => 'Quotations', 'href' => $baseUrl . 'quotations'], ['label' => 'My Quotations']];
$breadcrumbAccent = 'teal';

$viewerId = AuthService::userId();
$result = QuotationsController::mine(null, $viewerId);
$feedHtml = $result['html'];
?>
<div class="space-y-6">
    <?php include __DIR__ . '/../components/breadcrumbs.php'; ?>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <a href="<?= $baseUrl ?>quotations" data-partial class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-teal-600 dark:hover:text-teal-400 transition-colors mb-2">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Go Back
            </a>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white font-sans tracking-tight">My Quotations</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 font-medium">Post a quotation request and let contractors come to you with their best offers.</p>
        </div>
        <button type="button" id="create-quote-btn" class="create-quote-trigger inline-flex items-center justify-center rounded-xl bg-teal-600 px-6 py-2.5 text-sm font-black text-white shadow-lg shadow-teal-600/20 hover:bg-teal-700 transition-all active:scale-95">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
            New Quotation
        </button>
    </div>

    <div class="relative max-w-xs">
        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </span>
        <input type="text" id="quote-search-input" placeholder="Search my quotations..." class="w-full pl-9 pr-3 py-2 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none text-gray-900 dark:text-white" />
    </div>

    <div id="quotes-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 <?= $feedHtml === '' ? 'hidden' : '' ?>">
        <?= $feedHtml ?>
    </div>

    <div id="empty-quotes-state" class="<?= $feedHtml !== '' ? 'hidden' : '' ?> bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-800 rounded-2xl p-10 text-center">
        <svg class="h-8 w-8 text-gray-300 dark:text-gray-700 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" /></svg>
        <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300">You haven't posted any quotation requests yet</h4>
        <p class="text-xs text-gray-400 dark:text-gray-500 max-w-sm mx-auto mt-1">Click "New Quotation" to get started.</p>
    </div>

    <?php include __DIR__ . '/../components/quotations/view-quotation-modal.php'; ?>
</div>

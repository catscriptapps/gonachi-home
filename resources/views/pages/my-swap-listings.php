<?php
// /resources/views/pages/my-swap-listings.php

declare(strict_types=1);

/**
 * Gonachi Swap - My Listings
 *
 * Owner-only management page: every status (not just posted), each card
 * showing Edit/Delete + a Mark As Completed/Reactivate toggle — see
 * Src\Controller\SwapListingsController::mine()/save()/delete()/setStatus()
 * and resources/views/components/swap-listings/data-card.php. Mirrors Real
 * Estate World's my-listings.php shape (own listings, "Post a Listing"
 * trigger) simplified to Swap's smaller field set.
 *
 * @var bool $isLoggedIn
 * @var string $baseUrl
 * @var string $assetBase
 */

use Src\Controller\SwapListingsController;
use Src\Service\AuthService;

if (!$isLoggedIn) {
    include __DIR__ . '/auth-required.php';
    return;
}

$viewerId = AuthService::userId();
$search = trim($_GET['q'] ?? '');

$listings = SwapListingsController::mine($viewerId, $search ?: null)->appends(['q' => $search]);
$categories = SwapListingsController::categories();
?>
<div class="space-y-6">

    <?php
    $breadcrumbs = [
        ['label' => 'Swap Marketplace', 'href' => $baseUrl . 'swap'],
        ['label' => 'My Listings'],
    ];
    $breadcrumbAccent = 'purple';
    include __DIR__ . '/../components/breadcrumbs.php';
    ?>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">My Listings</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage everything you've posted — edit, mark as completed, or delete.</p>
        </div>
        <button type="button" class="create-swap-listing-trigger inline-flex items-center justify-center px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-bold text-sm rounded-lg transition-colors shadow-sm whitespace-nowrap">
            + Post A Listing
        </button>
    </div>

    <form method="GET" action="<?= $baseUrl ?>my-swap-listings" data-partial class="w-full sm:w-96 relative">
        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </span>
        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search my listings..." class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none text-gray-900 dark:text-white" />
    </form>

    <?php if ($listings->isEmpty()): ?>
        <div class="bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-800 rounded-xl p-8 text-center">
            <p class="text-sm text-gray-400 dark:text-gray-500">
                <?= $search ? 'No listings match that search.' : "You haven't posted any listings yet." ?>
            </p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5" id="swap-listings-grid">
            <?php foreach ($listings as $listing): ?>
                <?= SwapListingsController::renderCard($listing, $viewerId) ?>
            <?php endforeach; ?>
        </div>

        <?php if ($listings->lastPage() > 1): ?>
            <div class="flex items-center justify-between pt-2">
                <?php if ($listings->previousPageUrl()): ?>
                    <a href="<?= htmlspecialchars($listings->previousPageUrl()) ?>" data-partial class="text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-purple-600 dark:hover:text-purple-400">&larr; Previous</a>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>

                <span class="text-xs text-gray-400">Page <?= $listings->currentPage() ?> of <?= $listings->lastPage() ?></span>

                <?php if ($listings->nextPageUrl()): ?>
                    <a href="<?= htmlspecialchars($listings->nextPageUrl()) ?>" data-partial class="text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-purple-600 dark:hover:text-purple-400">Next &rarr;</a>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <script type="application/json" id="swap-listing-lookups"><?= json_encode([
        'categories' => $categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values(),
        'types' => SwapListingsController::TYPE_LABELS,
        'conditions' => SwapListingsController::CONDITION_LABELS,
    ]) ?></script>

    <?php include __DIR__ . '/../components/swap-listings/view-swap-listing-modal.php'; ?>
</div>

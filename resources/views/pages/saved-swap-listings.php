<?php
// /resources/views/pages/saved-swap-listings.php

declare(strict_types=1);

/**
 * Gonachi Swap - Saved Listings
 *
 * The "Saved" tab from the legacy gonachi-swap app's nav (dangling there —
 * no page ever backed it), built for real here: listings the signed-in
 * user has bookmarked via the save/unsave toggle on the browse grid — see
 * Src\Controller\SwapListingsController::saved()/toggleSave() and
 * server/api/swap-listing-save.php.
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
$listings = SwapListingsController::saved($viewerId);
?>
<div class="space-y-6" id="saved-swap-listings-page-marker">

    <?php
    $breadcrumbs = [
        ['label' => 'Swap', 'href' => $baseUrl . 'swap'],
        ['label' => 'Saved'],
    ];
    $breadcrumbAccent = 'purple';
    include __DIR__ . '/../components/breadcrumbs.php';
    ?>

    <div>
        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Saved Listings</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Listings you've bookmarked to come back to later.</p>
    </div>

    <?php if ($listings->isEmpty()): ?>
        <div class="bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-800 rounded-xl p-8 text-center">
            <p class="text-sm text-gray-400 dark:text-gray-500">Nothing saved yet — tap the bookmark icon on any listing to save it here.</p>
            <a href="<?= $baseUrl ?>swap" data-partial class="inline-flex items-center mt-3 text-xs font-bold text-purple-600 hover:underline">Browse Listings &rarr;</a>
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
</div>

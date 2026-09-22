<?php
// /resources/views/pages/swap.php

declare(strict_types=1);

/**
 * Gonachi Swap - Main Discovery Viewport
 *
 * Ported from the standalone gonachi-swap app (its static marketing
 * home.php + real /listings browse page) into gonachi-home's own project
 * home-page convention — hero + live counters + search/filter bar + browse
 * grid + a right-hand sidebar column (spotlight photo + category quick
 * links), matching real-estate-leads.php / contractor-discovery.php /
 * landlord-tenant-validation.php exactly. Backed by real data via
 * Src\Controller\SwapListingsController (swp_ prefixed tables).
 *
 * @var bool $isLoggedIn
 * @var string $baseUrl
 * @var string $assetBase
 */

use Src\Controller\SwapListingsController;
use Src\Service\AuthService;
use Src\Utils\CuratedPhotos;

$slideshowImages = CuratedPhotos::fromHomeFolder($assetBase);
$spotlightPhoto = $slideshowImages[0] ?? null;

$viewerId = $isLoggedIn ? AuthService::userId() : null;

$search = trim($_GET['q'] ?? '');
$categorySlug = trim($_GET['category'] ?? '');
$type = trim($_GET['type'] ?? '');

$listings = SwapListingsController::browse($search ?: null, $categorySlug ?: null, $type ?: null)
    ->appends(['q' => $search, 'category' => $categorySlug, 'type' => $type]);

$totalListings = SwapListingsController::totalListings();
$categories = SwapListingsController::categories();
?>
<div class="space-y-6">

    <?php
    $breadcrumbs = [['label' => 'Swap']];
    $breadcrumbAccent = 'purple';
    include __DIR__ . '/../components/breadcrumbs.php';
    ?>

    <!-- Hero Banner -->
    <section class="relative overflow-hidden rounded-3xl shadow-sm">
        <?php include __DIR__ . '/../components/hero-slideshow.php'; ?>
        <?php if (!empty($slideshowImages)): ?>
            <div class="absolute inset-0 bg-gray-50/85 dark:bg-gray-950/85"></div>
        <?php else: ?>
            <div class="absolute inset-0 bg-white dark:bg-gray-900"></div>
        <?php endif; ?>

        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-6 p-6 sm:p-10">
            <div>
                <span class="inline-block text-xs font-semibold tracking-[0.2em] text-purple-600 dark:text-purple-400 uppercase mb-2">Swap</span>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Don't Throw It. Swap It.</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 max-w-md">Exchange, sell, or gift items within your local community. No cash? No problem.</p>
            </div>

            <!-- Live Counters -->
            <div class="flex items-center space-x-4 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md p-2 rounded-xl border border-gray-200 dark:border-gray-800 flex-shrink-0">
                <div class="px-4 py-2 border-r border-gray-200 dark:border-gray-800 text-center">
                    <span class="block text-2xl font-bold text-purple-600 dark:text-purple-400"><?= $totalListings ?></span>
                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Active Listings</span>
                </div>
                <div class="px-4 py-2 text-center">
                    <span class="block text-2xl font-bold text-gray-900 dark:text-white"><?= $categories->count() ?></span>
                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Categories</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Search & Filter Bar -->
    <form method="GET" action="<?= $baseUrl ?>swap" id="swap-search" data-partial class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col md:flex-row gap-4 items-center">
        <div class="w-full md:flex-1 relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search listings by title, description, or city..." class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none text-gray-900 dark:text-white" />
        </div>
        <div class="w-full md:w-44">
            <select name="type" class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none text-gray-700 dark:text-gray-300">
                <option value="">All Types</option>
                <?php foreach (SwapListingsController::TYPE_LABELS as $value => $label): ?>
                    <option value="<?= $value ?>" <?= $type === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="w-full md:w-56">
            <select name="category" class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none text-gray-700 dark:text-gray-300">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat->slug) ?>" <?= $categorySlug === $cat->slug ? 'selected' : '' ?>><?= htmlspecialchars($cat->name) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="w-full md:w-auto px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-bold text-sm rounded-lg transition-colors shadow-sm whitespace-nowrap">
            Search
        </button>
        <?php if ($search || $categorySlug || $type): ?>
            <a href="<?= $baseUrl ?>swap" data-partial class="text-xs font-semibold text-gray-500 hover:text-purple-600 whitespace-nowrap">Clear</a>
        <?php endif; ?>
    </form>

    <!-- Listings + Category Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Primary Listings Column -->
        <div class="lg:col-span-2 space-y-4" id="swap-listings-grid">
            <div class="flex items-center justify-between gap-3">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white"><?= ($search || $categorySlug || $type) ? 'Search Results' : 'Recent Listings' ?></h3>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-purple-600 bg-purple-50 dark:bg-purple-950/40 px-2 py-1 rounded font-medium whitespace-nowrap"><?= $listings->total() ?> Listed</span>
                    <?php if ($isLoggedIn): ?>
                        <button type="button" class="create-swap-listing-trigger inline-flex items-center px-3.5 py-1.5 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-lg transition-colors shadow-sm whitespace-nowrap">
                            + Post A Listing
                        </button>
                    <?php else: ?>
                        <button type="button" class="auth-gate-btn inline-flex items-center px-3.5 py-1.5 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-lg transition-colors shadow-sm whitespace-nowrap">
                            + Post A Listing
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($listings->isEmpty()): ?>
                <div class="bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-800 rounded-xl p-8 text-center">
                    <p class="text-sm text-gray-400 dark:text-gray-500">No listings match that search yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($listings as $listing): ?>
                    <?= SwapListingsController::renderCard($listing, $viewerId) ?>
                <?php endforeach; ?>
            <?php endif; ?>

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
        </div>

        <!-- Spotlight + Category Sidebar Column -->
        <div class="space-y-4">
            <?php if ($spotlightPhoto): ?>
                <!-- Spotlight Card -->
                <div class="relative rounded-2xl overflow-hidden shadow-sm h-40"
                    style="background-image:url('<?= htmlspecialchars($spotlightPhoto) ?>'); background-size:cover; background-position:center;">
                    <div class="absolute inset-0 bg-gradient-to-t from-gray-900/90 via-gray-900/40 to-transparent"></div>
                    <div class="relative h-full flex flex-col justify-end p-4">
                        <span class="text-xs font-semibold text-purple-300 uppercase tracking-wider">Spotlight</span>
                        <h4 class="text-white font-bold text-sm mt-1">Real Value. Zero Cash.</h4>
                        <p class="text-gray-200 text-xs mt-0.5">Turn your unused items into the things you actually want.</p>
                    </div>
                </div>
            <?php endif; ?>

            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Browse By Category</h3>
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl divide-y divide-gray-100 dark:divide-gray-800 overflow-hidden shadow-sm">
                <?php foreach ($categories as $cat): ?>
                    <a href="<?= $baseUrl ?>swap?category=<?= urlencode($cat->slug) ?>" data-partial class="flex items-center justify-between p-3.5 hover:bg-gray-50 dark:hover:bg-gray-800/40 text-sm group transition-colors">
                        <span class="font-medium text-gray-700 dark:text-gray-300 group-hover:text-purple-600"><?= htmlspecialchars($cat->name) ?></span>
                        <span class="text-xs font-bold text-purple-600 bg-purple-50 dark:bg-purple-950/40 dark:text-purple-400 px-2 py-0.5 rounded-full"><?= $cat->posted_listings_count ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <script type="application/json" id="swap-listing-lookups"><?= json_encode([
        'categories' => $categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values(),
        'types' => SwapListingsController::TYPE_LABELS,
        'conditions' => SwapListingsController::CONDITION_LABELS,
    ]) ?></script>
</div>

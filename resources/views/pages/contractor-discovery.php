<?php
// /resources/views/pages/contractor-discovery.php

declare(strict_types=1);

/**
 * Gonachi Contractor Discovery & Opportunity Engine - Main Discovery Viewport
 *
 * Real directory backed by Src\Controller\ContractorController — admin-
 * curated contractor records (see scripts/reset/cde-seed.php), searchable
 * and filterable by category/location. No auto-collection pipeline yet
 * (contractor_discovery.pdf's Phase 1 "Data Collection" is future scope);
 * an admin adds/edits records directly for now.
 *
 * "Claim This Profile" is a real (basic) flow: a logged-in user submits a
 * claim (server/api/contractor-claim.php), which lands as claim_status =
 * pending until an admin approves it at /contractor-claims-review — see
 * ContractorClaimController. Claim submission itself is pure AJAX, wired in
 * resources/js/pages/contractor-discovery-page.js.
 *
 * @var bool $isLoggedIn
 * @var string $baseUrl
 * @var string $assetBase
 */

use Src\Controller\ContractorController;
use Src\Controller\JobRequestController;
use Src\Service\AuthService;
use Src\Utils\CuratedPhotos;

$currentUserId = $isLoggedIn ? AuthService::userId() : null;

$slideshowImages = CuratedPhotos::fromHomeFolder($assetBase);

$category = trim($_GET['category'] ?? '');
$location = trim($_GET['location'] ?? '');
$search = trim($_GET['search'] ?? '');

$contractors = ContractorController::browse($category ?: null, $location ?: null, $search ?: null)
    ->appends(['category' => $category, 'location' => $location, 'search' => $search]);

$totalContractors = ContractorController::totalCount();
$totalOpenRequests = JobRequestController::totalOpenCount();

$categoryLabels = ContractorController::CATEGORY_LABELS;
?>
<div class="space-y-6">

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
                <span class="inline-block text-xs font-semibold tracking-[0.2em] text-secondary-600 dark:text-secondary-400 uppercase mb-2">Contractor Discovery</span>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Find A Trusted Contractor</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 max-w-md">Search verified plumbers, electricians, builders, and more across active service networks.</p>
            </div>

            <!-- Live Counters -->
            <div class="flex items-center space-x-4 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md p-2 rounded-xl border border-gray-200 dark:border-gray-800 flex-shrink-0">
                <div class="px-4 py-2 border-r border-gray-200 dark:border-gray-800 text-center">
                    <span class="block text-2xl font-bold text-secondary-600 dark:text-secondary-400"><?= $totalContractors ?></span>
                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Contractors</span>
                </div>
                <div class="px-4 py-2 text-center">
                    <span class="block text-2xl font-bold text-primary-600 dark:text-primary-400"><?= $totalOpenRequests ?></span>
                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Open Job Requests</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Search & Category Filtering Bar -->
    <form method="GET" action="<?= $baseUrl ?>contractor-discovery" id="contractor-search" class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col md:flex-row gap-4 items-center">
        <div class="w-full md:flex-1 relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search for a business name..." class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-secondary-500 focus:outline-none text-gray-900 dark:text-white" />
        </div>
        <div class="w-full md:w-48">
            <select name="category" class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-secondary-500 focus:outline-none text-gray-700 dark:text-gray-300">
                <option value="">All Categories</option>
                <?php foreach ($categoryLabels as $value => $label): ?>
                    <option value="<?= $value ?>" <?= $category === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="w-full md:w-56">
            <input type="text" name="location" value="<?= htmlspecialchars($location) ?>" placeholder="Location (e.g. Lekki)" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-secondary-500 focus:outline-none text-gray-900 dark:text-white" />
        </div>
        <button type="submit" class="w-full md:w-auto px-6 py-2.5 bg-secondary-600 hover:bg-secondary-700 text-white font-bold text-sm rounded-lg transition-colors shadow-sm whitespace-nowrap">
            Search
        </button>
        <?php if ($category || $location || $search): ?>
            <a href="<?= $baseUrl ?>contractor-discovery" data-partial class="text-xs font-semibold text-gray-500 hover:text-secondary-600 whitespace-nowrap">Clear</a>
        <?php endif; ?>
        <a href="<?= $baseUrl ?>job-requests" data-partial class="w-full md:w-auto px-6 py-2.5 bg-gray-900 hover:bg-gray-800 dark:bg-primary-600 dark:hover:bg-primary-500 text-white text-center font-bold text-sm rounded-lg transition-colors shadow-sm whitespace-nowrap">
            Get Quotes
        </a>
    </form>

    <div id="contractor-claim-message"></div>

    <!-- Directory + Category Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Primary Directory Listing Column -->
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Contractors</h3>
                <span class="text-xs text-secondary-600 bg-secondary-50 dark:bg-secondary-950/40 px-2 py-1 rounded font-medium"><?= $contractors->total() ?> Listed</span>
            </div>

            <?php if ($contractors->isEmpty()): ?>
                <div class="bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-800 rounded-xl p-8 text-center">
                    <p class="text-sm text-gray-400 dark:text-gray-500">No contractors match that search yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($contractors as $contractor): ?>
                    <?php $isClaimed = $contractor->claim_status === 'claimed'; ?>
                    <div class="bg-white dark:bg-gray-900 border <?= $isClaimed ? 'border-gray-200 dark:border-gray-800' : 'border-dashed border-gray-300 dark:border-gray-700' ?> rounded-xl p-5 hover:border-secondary-500/50 transition-all shadow-sm group">
                        <div class="flex items-start justify-between gap-4 mb-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-secondary-100 text-secondary-800 dark:bg-secondary-950 dark:text-secondary-400">
                                        <?= htmlspecialchars($categoryLabels[$contractor->service_category] ?? ucfirst($contractor->service_category)) ?>
                                    </span>
                                    <?php if ($isClaimed): ?>
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            Verified
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-gray-400">Unclaimed Profile</span>
                                    <?php endif; ?>
                                </div>
                                <h4 class="text-base font-bold text-gray-900 dark:text-white mt-2 group-hover:text-secondary-600 transition-colors">
                                    <?= htmlspecialchars($contractor->business_name) ?>
                                </h4>
                            </div>
                            <?php if ($contractor->rating !== null): ?>
                                <span class="text-xs font-medium text-amber-500 whitespace-nowrap">&#9733; <?= number_format((float) $contractor->rating, 1) ?> (<?= $contractor->review_count ?>)</span>
                            <?php endif; ?>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 my-4 text-sm border-t border-b border-gray-100 dark:border-gray-800/80 py-3">
                            <div>
                                <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Location</span>
                                <span class="font-medium text-gray-700 dark:text-gray-300"><?= htmlspecialchars($contractor->location) ?></span>
                            </div>
                            <?php if ($contractor->operating_areas): ?>
                                <div>
                                    <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Operating Areas</span>
                                    <span class="font-medium text-gray-700 dark:text-gray-300"><?= htmlspecialchars($contractor->operating_areas) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="flex items-center justify-between pt-2 gap-3 flex-wrap">
                            <span class="text-xs text-gray-400">Contact details unlock with a full profile view.</span>
                            <div class="flex items-center gap-2">
                                <a href="<?= $baseUrl ?>contractor/<?= $contractor->id ?>" data-partial class="inline-flex items-center px-4 py-2 bg-gray-900 hover:bg-gray-800 dark:bg-secondary-600 dark:hover:bg-secondary-500 text-white font-bold text-xs rounded-lg transition-colors shadow-sm tracking-wide">
                                    View Profile
                                </a>
                                <?php if (!$isClaimed): ?>
                                    <?php if ($contractor->claim_status === 'pending'): ?>
                                        <button disabled title="Awaiting admin review" class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-400 cursor-not-allowed font-bold text-xs rounded-lg transition-colors tracking-wide">
                                            Claim Pending
                                        </button>
                                    <?php elseif (!$currentUserId): ?>
                                        <a href="<?= $baseUrl ?>login" data-login-button class="inline-flex items-center px-4 py-2 bg-secondary-600 hover:bg-secondary-700 text-white font-bold text-xs rounded-lg transition-colors shadow-sm tracking-wide">
                                            Claim This Profile
                                        </a>
                                    <?php else: ?>
                                        <button type="button" data-claim-contractor="<?= $contractor->id ?>" class="inline-flex items-center px-4 py-2 bg-secondary-600 hover:bg-secondary-700 text-white font-bold text-xs rounded-lg transition-colors shadow-sm tracking-wide">
                                            Claim This Profile
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if ($contractors->lastPage() > 1): ?>
                <div class="flex items-center justify-between pt-2">
                    <?php if ($contractors->previousPageUrl()): ?>
                        <a href="<?= htmlspecialchars($contractors->previousPageUrl()) ?>" data-partial class="text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-secondary-600 dark:hover:text-secondary-400">&larr; Previous</a>
                    <?php else: ?>
                        <span></span>
                    <?php endif; ?>

                    <span class="text-xs text-gray-400">Page <?= $contractors->currentPage() ?> of <?= $contractors->lastPage() ?></span>

                    <?php if ($contractors->nextPageUrl()): ?>
                        <a href="<?= htmlspecialchars($contractors->nextPageUrl()) ?>" data-partial class="text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-secondary-600 dark:hover:text-secondary-400">Next &rarr;</a>
                    <?php else: ?>
                        <span></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- SEO/Scalable Category Sidebar Column -->
        <div class="space-y-4">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Popular Searches</h3>
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl divide-y divide-gray-100 dark:divide-gray-800 overflow-hidden shadow-sm">
                <?php
                $popularSearches = [
                    ['label' => 'Plumbers in Lagos', 'category' => 'plumbing', 'location' => 'Lagos'],
                    ['label' => 'Electricians in Ikeja', 'category' => 'electrical', 'location' => 'Ikeja'],
                    ['label' => 'Builders in Lekki', 'category' => 'building_construction', 'location' => 'Lekki'],
                    ['label' => 'Renovation Contractors in Port Harcourt', 'category' => 'renovation', 'location' => 'Port Harcourt'],
                ];
                ?>
                <?php foreach ($popularSearches as $item): ?>
                    <a href="<?= $baseUrl ?>contractor-discovery?category=<?= urlencode($item['category']) ?>&location=<?= urlencode($item['location']) ?>" data-partial class="flex items-center justify-between p-3.5 hover:bg-gray-50 dark:hover:bg-gray-800/40 text-sm group transition-colors">
                        <span class="font-medium text-gray-700 dark:text-gray-300 group-hover:text-secondary-600"><?= htmlspecialchars($item['label']) ?></span>
                        <svg class="h-4 w-4 text-gray-400 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="bg-secondary-50 dark:bg-secondary-950/40 rounded-xl p-4 border border-secondary-100 dark:border-secondary-900/30">
                <h4 class="text-sm font-bold text-secondary-900 dark:text-secondary-300">Own A Contractor Business?</h4>
                <p class="text-xs text-secondary-700 dark:text-secondary-400 mt-1">Find your listing above and claim your free profile to add photos, certifications, and start receiving job alerts.</p>
                <a href="#contractor-search" class="mt-3 block text-center w-full px-4 py-2 bg-secondary-600 hover:bg-secondary-700 text-white font-bold text-xs rounded-lg transition-colors shadow-sm">
                    Find My Business
                </a>
            </div>
        </div>

    </div>
</div>

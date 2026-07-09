<?php
// /resources/views/pages/landlord-tenant-validation.php

declare(strict_types=1);

/**
 * Gonachi Landlord & Tenant Validation Engine - Main Discovery Viewport
 *
 * Report-a-landlord contribution loop, confidence engine, and search are
 * backed by real data via Src\Controller\LandlordDirectoryController — see
 * report-landlord.php (submission) and landlord-report-review.php
 * (moderation). The rental opportunity feed below stays illustrative; that's
 * rental-opportunities.php's concern, a separate future piece.
 *
 * @var string $baseUrl
 */

use Src\Controller\LandlordDirectoryController;
use Src\Utils\CuratedPhotos;

$slideshowImages = CuratedPhotos::fromHomeFolder($assetBase);

$opportunities = [
    ['area' => 'Lekki', 'count' => 50],
    ['area' => 'Yaba', 'count' => 30],
    ['area' => 'Ikeja', 'count' => 20],
];

$searchQuery = trim($_GET['q'] ?? '');
// ->appends() keeps `q` on the Next/Previous links — the app's Paginator::
// currentPathResolver() strips the query string entirely, so without this
// paginating would silently drop the search term.
$searchResults = $searchQuery !== '' ? LandlordDirectoryController::search($searchQuery)->appends(['q' => $searchQuery]) : null;

$totalProperties = LandlordDirectoryController::totalPublishedProperties();
$totalReports = LandlordDirectoryController::totalPublishedReports();

$recentRecord = LandlordDirectoryController::recentPublished(1)->first();
$recentConfidence = $recentRecord ? LandlordDirectoryController::confidenceScore($recentRecord) : 0;
?>
<div class="max-w-5xl mx-auto space-y-12">

    <!-- Hero Banner -->
    <section class="relative overflow-hidden rounded-3xl shadow-sm">
        <?php include __DIR__ . '/../components/hero-slideshow.php'; ?>
        <?php if (!empty($slideshowImages)): ?>
            <div class="absolute inset-0 bg-gray-50/85 dark:bg-gray-950/85"></div>
        <?php else: ?>
            <div class="absolute inset-0 bg-white dark:bg-gray-900"></div>
        <?php endif; ?>

        <div class="relative text-center max-w-2xl mx-auto px-6 py-14 sm:py-20">
            <span class="inline-block text-xs font-semibold tracking-[0.2em] text-indigo-600 dark:text-indigo-400 uppercase mb-3">Landlord & Tenant Validation</span>
            <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 dark:text-white">
                Check If Your Landlord Has Previous Complaints — Before Renting
            </h1>
            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                A searchable record of landlords and tenants in Nigeria. Report a problem, help the next renter, and unlock the rental opportunity feed.
            </p>

            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="<?= $baseUrl ?>report-landlord" data-partial class="inline-flex items-center justify-center w-full sm:w-auto px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition-colors shadow-sm">
                    Report A Landlord
                </a>
                <form method="GET" action="<?= $baseUrl ?>landlord-tenant-validation" class="w-full sm:w-80 relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Search a landlord or address..." class="w-full pl-10 pr-4 py-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-900 dark:text-white" />
                </form>
            </div>
        </div>
    </section>

    <!-- Live Counters -->
    <div class="flex items-center justify-center gap-10">
        <div class="text-center">
            <span class="block text-3xl font-bold text-indigo-600"><?= $totalProperties ?></span>
            <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Property Records</span>
        </div>
        <div class="h-10 w-px bg-gray-200 dark:bg-gray-800"></div>
        <div class="text-center">
            <span class="block text-3xl font-bold text-gray-900 dark:text-white"><?= $totalReports ?></span>
            <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Landlord Reports</span>
        </div>
    </div>

    <?php if ($searchResults !== null): ?>

        <!-- Search Results -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider">
                    Search Results for &ldquo;<?= htmlspecialchars($searchQuery) ?>&rdquo;
                </h3>
                <a href="<?= $baseUrl ?>landlord-tenant-validation" data-partial class="text-xs font-semibold text-indigo-600 hover:underline">Clear Search</a>
            </div>

            <?php if ($searchResults->isEmpty()): ?>
                <div class="bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-800 rounded-2xl p-8 text-center">
                    <p class="text-sm text-gray-400 dark:text-gray-500">No published records match that search yet.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <?php foreach ($searchResults as $property): ?>
                        <?php $score = LandlordDirectoryController::confidenceScore($property); ?>
                        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 shadow-sm hover:border-indigo-500/50 transition-all">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                <?= $property->published_reports_count ?> report<?= $property->published_reports_count === 1 ? '' : 's' ?>
                            </span>
                            <h4 class="text-base font-bold text-gray-900 dark:text-white mt-2"><?= htmlspecialchars($property->address) ?></h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Landlord: <?= htmlspecialchars($property->landlord->name ?? 'Unknown') ?></p>

                            <div class="mt-4">
                                <div class="flex items-center justify-between text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5">
                                    <span>Verification Confidence</span>
                                    <span><?= $score ?>%</span>
                                </div>
                                <div class="h-1.5 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                                    <div class="h-full rounded-full bg-indigo-500" style="width: <?= $score ?>%"></div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end pt-4">
                                <button disabled title="Coming soon" class="inline-flex items-center px-3.5 py-2 bg-gray-100 dark:bg-gray-800 text-gray-400 font-bold text-xs rounded-lg cursor-not-allowed whitespace-nowrap">
                                    Unlock Contact
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($searchResults->lastPage() > 1): ?>
                    <div class="flex items-center justify-between pt-2">
                        <?php if ($searchResults->previousPageUrl()): ?>
                            <a href="<?= htmlspecialchars($searchResults->previousPageUrl()) ?>" data-partial class="text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">&larr; Previous</a>
                        <?php else: ?>
                            <span></span>
                        <?php endif; ?>

                        <span class="text-xs text-gray-400">Page <?= $searchResults->currentPage() ?> of <?= $searchResults->lastPage() ?></span>

                        <?php if ($searchResults->nextPageUrl()): ?>
                            <a href="<?= htmlspecialchars($searchResults->nextPageUrl()) ?>" data-partial class="text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">Next &rarr;</a>
                        <?php else: ?>
                            <span></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

    <?php else: ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Confidence Engine Showcase -->
            <div class="lg:col-span-2 space-y-3">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider">Recently Added Property Record</h3>

                <?php if (!$recentRecord): ?>
                    <div class="bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-800 rounded-2xl p-8 text-center">
                        <p class="text-sm text-gray-400 dark:text-gray-500">No published records yet — be the first to report a landlord.</p>
                    </div>
                <?php else: ?>
                    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                    <?= $recentConfidence >= 70 ? 'Verified' : 'Unverified' ?>
                                </span>
                                <h4 class="text-lg font-bold text-gray-900 dark:text-white mt-2"><?= htmlspecialchars($recentRecord->address) ?></h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Landlord: <?= htmlspecialchars($recentRecord->landlord->name ?? 'Unknown') ?> &middot; <?= $recentRecord->published_reports_count ?> report<?= $recentRecord->published_reports_count === 1 ? '' : 's' ?></p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <div class="flex items-center justify-between text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5">
                                <span>Verification Confidence</span>
                                <span><?= $recentConfidence ?>%</span>
                            </div>
                            <div class="h-2 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                                <div class="h-full rounded-full bg-indigo-500" style="width: <?= $recentConfidence ?>%"></div>
                            </div>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">
                                Confidence grows as tenants confirm details, ownership documents are uploaded, and reviews come in — this record could reach 95%.
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Rental Opportunities -->
            <div class="space-y-3">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider">Rental Opportunities</h3>
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl divide-y divide-gray-100 dark:divide-gray-800 overflow-hidden shadow-sm">
                    <?php foreach ($opportunities as $opportunity): ?>
                        <a href="<?= $baseUrl ?>rental-opportunities" data-partial class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-800/40 text-sm group transition-colors">
                            <span class="font-medium text-gray-700 dark:text-gray-300 group-hover:text-indigo-600">New Listings in <?= htmlspecialchars($opportunity['area']) ?></span>
                            <span class="text-xs font-bold text-indigo-600 bg-indigo-50 dark:bg-indigo-950/40 dark:text-indigo-400 px-2 py-0.5 rounded-full"><?= $opportunity['count'] ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
                <p class="text-xs text-gray-400 dark:text-gray-500 px-1">Unlocked after your first contribution.</p>
            </div>

        </div>

    <?php endif; ?>
</div>

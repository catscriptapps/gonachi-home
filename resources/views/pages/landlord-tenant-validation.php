<?php
// /resources/views/pages/landlord-tenant-validation.php

declare(strict_types=1);

/**
 * Gonachi Landlord & Tenant Validation Engine - Main Discovery Viewport
 *
 * Weighted-review system per landlord_and_tenant_validation.pdf, backed by
 * Src\Controller\ReviewProfileController (read side) — see review-landlord.php
 * /review-tenant.php (submission, double-blind release via
 * Src\Service\ReviewService) and review-moderation.php (disputes). The
 * Rental Opportunities teaser below is unchanged, backed by
 * Src\Controller\RentalListingController. "Unlock Contact" on each search
 * result still spends 1 credit from Src\Service\LandlordCreditService's own
 * wallet — unrelated to the rebuilt review system, kept exactly as-is.
 *
 * @var bool $isLoggedIn
 * @var string $baseUrl
 */

use Src\Controller\RentalListingController;
use Src\Controller\ReviewProfileController;
use Src\Service\AuthService;
use Src\Service\LandlordCreditService;
use Src\Utils\ContactMasker;
use Src\Utils\CountryScope;
use Src\Utils\CuratedPhotos;

$countryCode = $GLOBALS['countryCode'] ?? 'ng';
$countryId = CountryScope::idFor($countryCode);

$slideshowImages = CuratedPhotos::fromHomeFolder($assetBase);
$spotlightPhoto = $slideshowImages[0] ?? null;

$opportunities = RentalListingController::countsByArea(3, $countryId);

$currentUserId = $isLoggedIn ? AuthService::userId() : null;
$isAdmin = $currentUserId ? AuthService::isAdmin() : false;

$searchQuery = trim($_GET['q'] ?? '');
$searchResults = $searchQuery !== '' ? ReviewProfileController::searchLandlords($searchQuery, 12, $countryId)->appends(['q' => $searchQuery]) : null;
$tenantSearchResults = $searchQuery !== '' ? ReviewProfileController::searchTenants($searchQuery, 12, $countryId)->appends(['q' => $searchQuery]) : null;

$totalLandlords = ReviewProfileController::totalReviewedLandlords($countryId);
$totalLandlordReviews = ReviewProfileController::totalPublishedLandlordReviews($countryId);
$totalTenants = ReviewProfileController::totalReviewedTenants($countryId);
$totalTenantReviews = ReviewProfileController::totalPublishedTenantReviews($countryId);

$recentLandlord = ReviewProfileController::recentlyReviewedLandlord($countryId);
$recentLandlordAggregate = $recentLandlord ? \Src\Service\ReviewAggregationService::profileAggregate('landlord', $recentLandlord->id) : null;

$recentTenant = ReviewProfileController::recentlyReviewedTenant($countryId);
$recentTenantAggregate = $recentTenant ? \Src\Service\ReviewAggregationService::profileAggregate('tenant', $recentTenant->id) : null;
?>
<div class="max-w-5xl mx-auto space-y-12">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <?php
        $breadcrumbs = [['label' => 'Landlord & Tenant Validation']];
        $breadcrumbAccent = 'indigo';
        include __DIR__ . '/../components/breadcrumbs.php';
        ?>
        <?php
        $countrySwitcherProject = 'landlord-tenant-validation';
        $countrySwitcherCurrent = $countryCode;
        include __DIR__ . '/../components/country-switcher.php';
        ?>
    </div>

    <!-- Hero Banner -->
    <section class="relative overflow-hidden rounded-3xl shadow-sm">
        <?php include __DIR__ . '/../components/hero-slideshow.php'; ?>
        <?php if (!empty($slideshowImages)): ?>
            <div class="absolute inset-0 bg-gray-50/85 dark:bg-gray-950/85"></div>
        <?php else: ?>
            <div class="absolute inset-0 bg-white dark:bg-gray-900"></div>
        <?php endif; ?>

        <div class="relative text-center max-w-2xl mx-auto px-6 py-14 sm:py-20">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-4 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
            </div>
            <span class="inline-block text-xs font-semibold tracking-[0.2em] text-indigo-600 dark:text-indigo-400 uppercase mb-3">Landlord & Tenant Validation</span>
            <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 dark:text-white">
                Verified Landlord & Tenant Reviews — Before You Sign
            </h1>
            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                Weighted, verified reviews of landlords and tenants in <?= htmlspecialchars(CountryScope::nameFor($countryCode)) ?>. Review your own tenancy, help the next renter, and unlock the rental opportunity feed.
            </p>

            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="<?= $baseUrl ?>review-landlord?country=<?= $countryCode ?>" data-partial class="inline-flex items-center justify-center w-full sm:w-auto px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition-colors shadow-sm">
                    Review A Landlord
                </a>
                <a href="<?= $baseUrl ?>review-tenant?country=<?= $countryCode ?>" data-partial class="inline-flex items-center justify-center w-full sm:w-auto px-6 py-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 hover:border-indigo-400 text-gray-700 dark:text-gray-300 font-bold text-sm rounded-xl transition-colors shadow-sm">
                    Review A Tenant
                </a>
                <form method="GET" action="<?= $baseUrl ?>landlord-tenant-validation/<?= $countryCode ?>" data-partial class="w-full sm:w-80 relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Search a landlord or tenant name..." class="w-full pl-10 pr-4 py-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-900 dark:text-white" />
                </form>
            </div>
        </div>
    </section>

    <!-- Live Counters -->
    <div class="flex flex-wrap items-center justify-center gap-x-10 gap-y-4">
        <div class="text-center">
            <span class="block text-3xl font-bold text-indigo-600"><?= $totalLandlords ?></span>
            <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Landlords Reviewed</span>
        </div>
        <div class="h-10 w-px bg-gray-200 dark:bg-gray-800"></div>
        <div class="text-center">
            <span class="block text-3xl font-bold text-gray-900 dark:text-white"><?= $totalLandlordReviews ?></span>
            <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Landlord Reviews</span>
        </div>
        <div class="h-10 w-px bg-gray-200 dark:bg-gray-800"></div>
        <div class="text-center">
            <span class="block text-3xl font-bold text-indigo-600"><?= $totalTenants ?></span>
            <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Tenants Reviewed</span>
        </div>
        <div class="h-10 w-px bg-gray-200 dark:bg-gray-800"></div>
        <div class="text-center">
            <span class="block text-3xl font-bold text-gray-900 dark:text-white"><?= $totalTenantReviews ?></span>
            <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Tenant Reviews</span>
        </div>
    </div>

    <?php if ($searchResults !== null): ?>

        <!-- Landlord Search Results -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider">
                    Landlord Results for &ldquo;<?= htmlspecialchars($searchQuery) ?>&rdquo;
                </h3>
                <a href="<?= $baseUrl ?>landlord-tenant-validation/<?= $countryCode ?>" data-partial class="text-xs font-semibold text-indigo-600 hover:underline">Clear Search</a>
            </div>

            <?php if ($searchResults->isEmpty()): ?>
                <div class="bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-800 rounded-2xl p-8 text-center">
                    <p class="text-sm text-gray-400 dark:text-gray-500">No reviewed landlords match that search yet.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <?php foreach ($searchResults as $landlord): ?>
                        <?php
                        $aggregate = \Src\Service\ReviewAggregationService::profileAggregate('landlord', $landlord->id);
                        $hasPhone = (bool) $landlord->phone;
                        $isUnlocked = $currentUserId && $hasPhone && LandlordCreditService::hasUnlocked($currentUserId, $landlord->id);
                        ?>
                        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 shadow-sm hover:border-indigo-500/50 transition-all">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-500 dark:bg-amber-950/30 dark:text-amber-400" title="<?= $aggregate['reviewCount'] ?> review(s)">
                                <?= ReviewProfileController::starHtml($aggregate['overall']) ?>
                            </span>
                            <h4 class="text-base font-bold text-gray-900 dark:text-white mt-2">
                                <a href="<?= $baseUrl ?>landlords/<?= $landlord->id ?>" data-partial class="hover:text-indigo-600"><?= htmlspecialchars($landlord->name) ?></a>
                            </h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400"><?= $aggregate['reviewCount'] ?> review<?= $aggregate['reviewCount'] === 1 ? '' : 's' ?><?= $aggregate['tier'] === 'limited' ? ' · Limited rating history' : '' ?></p>

                            <div class="flex items-center justify-between pt-4">
                                <a href="<?= $baseUrl ?>landlords/<?= $landlord->id ?>" data-partial class="text-xs font-semibold text-indigo-600 hover:underline">View Profile</a>
                                <?php if (!$hasPhone): ?>
                                    <button disabled title="No phone on file for this landlord yet" class="inline-flex items-center px-3.5 py-2 bg-gray-100 dark:bg-gray-800 text-gray-400 font-bold text-xs rounded-lg cursor-not-allowed whitespace-nowrap">No Contact On File</button>
                                <?php elseif ($isUnlocked): ?>
                                    <span class="text-sm font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($isAdmin ? $landlord->phone : ContactMasker::mask($landlord->phone)) ?></span>
                                <?php elseif (!$currentUserId): ?>
                                    <button type="button" class="auth-gate-btn inline-flex items-center px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg transition-colors whitespace-nowrap">Unlock Contact</button>
                                <?php else: ?>
                                    <button type="button" class="unlock-contact-btn inline-flex items-center px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg transition-colors whitespace-nowrap" data-landlord-id="<?= $landlord->id ?>">Unlock Contact</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($searchResults->lastPage() > 1): ?>
                    <div class="flex items-center justify-between pt-2">
                        <?php if ($searchResults->previousPageUrl()): ?><a href="<?= htmlspecialchars($searchResults->previousPageUrl()) ?>" data-partial class="text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">&larr; Previous</a><?php else: ?><span></span><?php endif; ?>
                        <span class="text-xs text-gray-400">Page <?= $searchResults->currentPage() ?> of <?= $searchResults->lastPage() ?></span>
                        <?php if ($searchResults->nextPageUrl()): ?><a href="<?= htmlspecialchars($searchResults->nextPageUrl()) ?>" data-partial class="text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">Next &rarr;</a><?php else: ?><span></span><?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Tenant Search Results -->
        <div class="space-y-4">
            <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider">
                Tenant Results for &ldquo;<?= htmlspecialchars($searchQuery) ?>&rdquo;
            </h3>

            <?php if ($tenantSearchResults->isEmpty()): ?>
                <div class="bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-800 rounded-2xl p-8 text-center">
                    <p class="text-sm text-gray-400 dark:text-gray-500">No reviewed tenants match that search yet.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <?php foreach ($tenantSearchResults as $tenant): ?>
                        <?php
                        $tAggregate = \Src\Service\ReviewAggregationService::profileAggregate('tenant', $tenant->id);
                        $hasReference = (bool) $tenant->reference_phone;
                        $isTenantUnlocked = $currentUserId && $hasReference && LandlordCreditService::hasUnlockedTenant($currentUserId, $tenant->id);
                        ?>
                        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 shadow-sm hover:border-indigo-500/50 transition-all">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-500 dark:bg-amber-950/30 dark:text-amber-400" title="<?= $tAggregate['reviewCount'] ?> review(s)">
                                <?= ReviewProfileController::starHtml($tAggregate['overall']) ?>
                            </span>
                            <h4 class="text-base font-bold text-gray-900 dark:text-white mt-2">
                                <a href="<?= $baseUrl ?>tenants/<?= $tenant->id ?>" data-partial class="hover:text-indigo-600"><?= htmlspecialchars($tenant->name) ?></a>
                            </h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400"><?= $tAggregate['reviewCount'] ?> review<?= $tAggregate['reviewCount'] === 1 ? '' : 's' ?><?= $tAggregate['tier'] === 'limited' ? ' · Limited rating history' : '' ?></p>

                            <div class="flex items-center justify-between pt-4">
                                <a href="<?= $baseUrl ?>tenants/<?= $tenant->id ?>" data-partial class="text-xs font-semibold text-indigo-600 hover:underline">View Profile</a>
                                <?php if (!$hasReference): ?>
                                    <button disabled title="No reference contact on file for this tenant yet" class="inline-flex items-center px-3.5 py-2 bg-gray-100 dark:bg-gray-800 text-gray-400 font-bold text-xs rounded-lg cursor-not-allowed whitespace-nowrap">No Contact On File</button>
                                <?php elseif ($isTenantUnlocked): ?>
                                    <span class="text-sm font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($isAdmin ? $tenant->reference_phone : ContactMasker::mask($tenant->reference_phone)) ?></span>
                                <?php elseif (!$currentUserId): ?>
                                    <button type="button" class="auth-gate-btn inline-flex items-center px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg transition-colors whitespace-nowrap">Unlock Contact</button>
                                <?php else: ?>
                                    <button type="button" class="unlock-tenant-btn inline-flex items-center px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg transition-colors whitespace-nowrap" data-tenant-id="<?= $tenant->id ?>">Unlock Contact</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($tenantSearchResults->lastPage() > 1): ?>
                    <div class="flex items-center justify-between pt-2">
                        <?php if ($tenantSearchResults->previousPageUrl()): ?><a href="<?= htmlspecialchars($tenantSearchResults->previousPageUrl()) ?>" data-partial class="text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">&larr; Previous</a><?php else: ?><span></span><?php endif; ?>
                        <span class="text-xs text-gray-400">Page <?= $tenantSearchResults->currentPage() ?> of <?= $tenantSearchResults->lastPage() ?></span>
                        <?php if ($tenantSearchResults->nextPageUrl()): ?><a href="<?= htmlspecialchars($tenantSearchResults->nextPageUrl()) ?>" data-partial class="text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">Next &rarr;</a><?php else: ?><span></span><?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

    <?php else: ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Recently Reviewed Landlord -->
            <div class="lg:col-span-2 space-y-3">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider">Recently Reviewed Landlord</h3>

                <?php if (!$recentLandlord): ?>
                    <div class="bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-800 rounded-2xl p-8 text-center">
                        <p class="text-sm text-gray-400 dark:text-gray-500">No published reviews yet — be the first to review a landlord.</p>
                    </div>
                <?php else: ?>
                    <a href="<?= $baseUrl ?>landlords/<?= $recentLandlord->id ?>" data-partial class="block bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm hover:border-indigo-500/50 transition-all">
                        <div class="flex items-start justify-between">
                            <div>
                                <h4 class="text-lg font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($recentLandlord->name) ?></h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    <span class="text-amber-500 dark:text-amber-400"><?= ReviewProfileController::starHtml($recentLandlordAggregate['overall']) ?></span>
                                    <?= $recentLandlordAggregate['overall'] !== null ? number_format($recentLandlordAggregate['overall'], 1) . ' / 5' : '' ?>
                                    &middot; <?= $recentLandlordAggregate['reviewCount'] ?> review<?= $recentLandlordAggregate['reviewCount'] === 1 ? '' : 's' ?>
                                </p>
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-4">View full profile — weighted category breakdown, tags, and recent reviews.</p>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Rental Opportunities -->
            <div class="space-y-3">
                <?php if ($spotlightPhoto): ?>
                    <div class="relative rounded-2xl overflow-hidden shadow-sm h-40" style="background-image:url('<?= htmlspecialchars($spotlightPhoto) ?>'); background-size:cover; background-position:center;">
                        <div class="absolute inset-0 bg-gradient-to-t from-gray-900/90 via-gray-900/40 to-transparent"></div>
                        <div class="relative h-full flex flex-col justify-end p-4">
                            <span class="text-xs font-semibold text-indigo-300 uppercase tracking-wider">Spotlight</span>
                            <h4 class="text-white font-bold text-sm mt-1">Rent With Confidence</h4>
                            <p class="text-gray-200 text-xs mt-0.5">New reviewed landlords and tenants are added every day.</p>
                        </div>
                    </div>
                <?php endif; ?>

                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider">Rental Opportunities</h3>
                <?php if (empty($opportunities)): ?>
                    <div class="bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-800 rounded-2xl p-6 text-center">
                        <p class="text-sm text-gray-400 dark:text-gray-500">No published listings yet.</p>
                        <a href="<?= $baseUrl ?>list-rental-property" data-partial class="inline-flex items-center mt-3 text-xs font-bold text-indigo-600 hover:underline">List Your Property &rarr;</a>
                    </div>
                <?php else: ?>
                    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl divide-y divide-gray-100 dark:divide-gray-800 overflow-hidden shadow-sm">
                        <?php foreach ($opportunities as $opportunity): ?>
                            <a href="<?= $baseUrl ?>rental-opportunities?area=<?= urlencode($opportunity['area']) ?>" data-partial class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-800/40 text-sm group transition-colors">
                                <span class="font-medium text-gray-700 dark:text-gray-300 group-hover:text-indigo-600">New Listings in <?= htmlspecialchars($opportunity['area']) ?></span>
                                <span class="text-xs font-bold text-indigo-600 bg-indigo-50 dark:bg-indigo-950/40 dark:text-indigo-400 px-2 py-0.5 rounded-full"><?= $opportunity['count'] ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <p class="text-xs text-gray-400 dark:text-gray-500 px-1">
                    <a href="<?= $baseUrl ?>rental-opportunities" data-partial class="hover:text-indigo-600 dark:hover:text-indigo-400">View all rental opportunities &rarr;</a>
                </p>
            </div>

        </div>

        <!-- Recently Reviewed Tenant -->
        <div class="space-y-3">
            <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider">Recently Reviewed Tenant</h3>

            <?php if (!$recentTenant): ?>
                <div class="bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-800 rounded-2xl p-8 text-center">
                    <p class="text-sm text-gray-400 dark:text-gray-500">No published reviews yet — be the first to review a tenant.</p>
                </div>
            <?php else: ?>
                <a href="<?= $baseUrl ?>tenants/<?= $recentTenant->id ?>" data-partial class="block bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm hover:border-indigo-500/50 transition-all">
                    <div class="flex items-start justify-between">
                        <div>
                            <h4 class="text-lg font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($recentTenant->name) ?></h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                <span class="text-amber-500 dark:text-amber-400"><?= ReviewProfileController::starHtml($recentTenantAggregate['overall']) ?></span>
                                <?= $recentTenantAggregate['overall'] !== null ? number_format($recentTenantAggregate['overall'], 1) . ' / 5' : '' ?>
                                &middot; <?= $recentTenantAggregate['reviewCount'] ?> review<?= $recentTenantAggregate['reviewCount'] === 1 ? '' : 's' ?>
                            </p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-4">View full profile — weighted category breakdown, tags, and recent reviews.</p>
                </a>
            <?php endif; ?>
        </div>

    <?php endif; ?>
</div>

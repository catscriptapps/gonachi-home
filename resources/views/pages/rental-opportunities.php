<?php
// /resources/views/pages/rental-opportunities.php

declare(strict_types=1);

/**
 * Gonachi Landlord & Tenant Validation Engine - Rental Opportunities Feed
 *
 * Backed by real data via Src\Controller\RentalListingController — listings
 * are submitted at /list-rental-property (mirrors report-landlord.php) and
 * moderated the same way landlord reports are, at /rental-listing-review.
 * A listing's "Verified/Unverified Landlord" badge reuses the real
 * confidence engine (LandlordDirectoryController::confidenceScore()) against
 * its shared PropertyRecord, so a property with landlord reports on file
 * shows a genuine score here too.
 *
 * @var string $baseUrl
 */

use Src\Controller\LandlordDirectoryController;
use Src\Controller\RentalListingController;

$areaFilter = trim((string) ($_GET['area'] ?? ''));
$listings = RentalListingController::browse($areaFilter !== '' ? $areaFilter : null, 9);
if ($areaFilter !== '') {
    $listings->appends(['area' => $areaFilter]);
}

$topAreas = RentalListingController::countsByArea(3);
?>
<div class="space-y-6">

    <?php
    $breadcrumbs = [
        ['label' => 'Landlord & Tenant Validation', 'href' => $baseUrl . 'landlord-tenant-validation'],
        ['label' => 'Rental Opportunities'],
    ];
    $breadcrumbAccent = 'indigo';
    include __DIR__ . '/../components/breadcrumbs.php';
    ?>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Rental Opportunities</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Live listings, cross-checked against our landlord verification records.</p>
        </div>
        <div class="flex items-center gap-3">
            <?php if (!empty($topAreas)): ?>
                <div class="flex items-center gap-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl px-4 py-2.5">
                    <?php foreach ($topAreas as $area): ?>
                        <a href="<?= $baseUrl ?>rental-opportunities?area=<?= urlencode($area['area']) ?>" data-partial class="text-center hover:opacity-75 transition-opacity">
                            <span class="block text-lg font-bold text-indigo-600 dark:text-indigo-400"><?= $area['count'] ?></span>
                            <span class="text-[10px] font-medium text-gray-400 uppercase tracking-wider"><?= htmlspecialchars($area['area']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <a href="<?= $baseUrl ?>list-rental-property" data-partial class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl transition-colors shadow-sm whitespace-nowrap">
                List Your Property
            </a>
        </div>
    </div>

    <?php if ($areaFilter !== ''): ?>
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider">Showing listings in &ldquo;<?= htmlspecialchars($areaFilter) ?>&rdquo;</h3>
            <a href="<?= $baseUrl ?>rental-opportunities" data-partial class="text-xs font-semibold text-indigo-600 hover:underline">Clear Filter</a>
        </div>
    <?php endif; ?>

    <?php if ($listings->isEmpty()): ?>
        <div class="bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-800 rounded-2xl p-8 text-center">
            <p class="text-sm text-gray-400 dark:text-gray-500">
                <?= $areaFilter !== '' ? 'No published listings in that area yet.' : 'No published listings yet — be the first to list a property.' ?>
            </p>
            <a href="<?= $baseUrl ?>list-rental-property" data-partial class="inline-flex items-center mt-4 px-4 py-2 bg-gray-900 hover:bg-gray-800 dark:bg-indigo-600 dark:hover:bg-indigo-500 text-white font-bold text-xs rounded-lg transition-colors">
                List Your Property
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php foreach ($listings as $listing): ?>
                <?php
                $score = $listing->property ? LandlordDirectoryController::confidenceScore($listing->property) : 0;
                $landlordStatus = $score >= 70 ? 'Verified' : 'Unverified';
                $statusStyle = $landlordStatus === 'Verified'
                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400'
                    : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400';
                $bedroomsLabel = $listing->bedrooms ? $listing->bedrooms . ' Bedroom' . ($listing->bedrooms === 1 ? '' : 's') : null;
                $typeLabels = [
                    'flat' => 'Flat / Apartment', 'duplex' => 'Duplex', 'bungalow' => 'Bungalow',
                    'self-contain' => 'Self Contain', 'commercial' => 'Commercial Space',
                ];
                $typeLabel = $typeLabels[$listing->property_type] ?? $listing->property_type;
                $typeDisplay = trim(($bedroomsLabel ? $bedroomsLabel . ' ' : '') . ($typeLabel ?: ''));
                ?>
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 shadow-sm hover:border-indigo-500/50 transition-all">
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-400">
                            <?= htmlspecialchars($typeDisplay !== '' ? $typeDisplay : $listing->area) ?>
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?= $statusStyle ?>">
                            <?= $landlordStatus ?> Landlord
                        </span>
                    </div>

                    <h4 class="text-base font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($listing->property->address ?? $listing->area) ?></h4>
                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mt-1">
                        <?= $listing->rent_amount !== null ? '&#8358;' . number_format((float) $listing->rent_amount) . ' / ' . $listing->rent_period : 'Rent on request' ?>
                    </p>

                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800/80">
                        <div class="flex items-center justify-between text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5">
                            <span>Review Score</span>
                            <span><?= $score ?>%</span>
                        </div>
                        <div class="h-1.5 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                            <div class="h-full rounded-full bg-indigo-500" style="width: <?= $score ?>%"></div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-4">
                        <span class="text-xs text-gray-400">Added <?= htmlspecialchars($listing->created_at->diffForHumans()) ?></span>
                        <button disabled title="Coming soon" class="inline-flex items-center px-3.5 py-2 bg-gray-100 dark:bg-gray-800 text-gray-400 font-bold text-xs rounded-lg cursor-not-allowed whitespace-nowrap">
                            Unlock Contact
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($listings->lastPage() > 1): ?>
            <div class="flex items-center justify-between pt-2">
                <?php if ($listings->previousPageUrl()): ?>
                    <a href="<?= htmlspecialchars($listings->previousPageUrl()) ?>" data-partial class="text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">&larr; Previous</a>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>

                <span class="text-xs text-gray-400">Page <?= $listings->currentPage() ?> of <?= $listings->lastPage() ?></span>

                <?php if ($listings->nextPageUrl()): ?>
                    <a href="<?= htmlspecialchars($listings->nextPageUrl()) ?>" data-partial class="text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">Next &rarr;</a>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="bg-indigo-50 dark:bg-indigo-950/40 rounded-xl p-5 border border-indigo-100 dark:border-indigo-900/30 flex items-center justify-between gap-4 flex-wrap">
        <div>
            <h4 class="text-sm font-bold text-indigo-900 dark:text-indigo-300">Free plan: 8 contact unlocks, 8 property views</h4>
            <p class="text-xs text-indigo-700 dark:text-indigo-400 mt-1">Upgrade for advanced search, full landlord records, and unlimited unlocks.</p>
        </div>
        <button disabled title="Coming soon" class="px-4 py-2 bg-white dark:bg-gray-900 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-900/50 font-bold text-xs rounded-lg cursor-not-allowed whitespace-nowrap">
            Go Premium
        </button>
    </div>
</div>

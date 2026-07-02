<?php
// /resources/views/pages/apply/detail.php

declare(strict_types=1);

use Src\Controller\TenantPortalController;
use Src\Service\AuthService;

/** @var string $baseUrl */
/** @var string $assetBase */

$tokenCode = $GLOBALS['encodedId'] ?? '';
$controller = new TenantPortalController();
$accessToken = $controller->loadByToken((string)$tokenCode);
$currentTenant = AuthService::currentTenant();

$isValid = $accessToken && $accessToken->isActive();

if (!$isValid) {
    $breadcrumbs = ['Tenant Portal' => ''];
    ?>
    <div class="max-w-2xl mx-auto py-16 px-4">
        <?php include __DIR__ . '/../../components/ui/breadcrumbs.php'; ?>

        <div class="flex flex-col items-center justify-center text-center space-y-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-3xl p-12 shadow-sm">
            <div class="text-red-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M4.93 4.93a10 10 0 0114.14 0 10 10 0 010 14.14 10 10 0 01-14.14 0 10 10 0 010-14.14z" />
                </svg>
            </div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white">Invalid or Expired Access Token</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md">
                We couldn't verify this link. The access token may have been mistyped, or it may have been revoked by the landlord. Please check the code and try again.
            </p>
            <a href="<?= $baseUrl ?>" data-partial data-title="Home" class="mt-2 inline-flex items-center justify-center px-6 py-3 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-black text-xs uppercase tracking-wider transition-all">
                Back to Home
            </a>
        </div>
    </div>
    <?php
    return;
}

$property = $accessToken->property;
$service  = $accessToken->service;

$propertyLabel = $property->portfolio_node_label ?? 'Property';
$fullAddress   = implode(', ', array_filter([
    $property->address_line1 ?? null,
    $property->city ?? null,
    $property->region->region ?? null,
    $property->postal_code ?? null,
    $property->country->country ?? null,
]));

$pictures = $property->pictures()->orderBy('pos_index', 'asc')->get();

$breadcrumbs = [
    'Rental Applications' => $baseUrl . 'rental-applications',
    $propertyLabel         => '',
];
?>

<div class="max-w-4xl mx-auto py-10 px-4 space-y-8">
    <?php include __DIR__ . '/../../components/ui/breadcrumbs.php'; ?>

    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-[2rem] shadow-sm overflow-hidden">

        <?php if ($pictures->isNotEmpty()):
            $visiblePicLimit = 3;
            $totalPics = $pictures->count();
            $hasMorePics = $totalPics > $visiblePicLimit;
        ?>
            <div id="property-pics-gallery">
                <div class="grid <?= $totalPics > 1 ? 'grid-cols-2 sm:grid-cols-3' : 'grid-cols-1' ?> gap-1 bg-gray-100 dark:bg-gray-950">
                    <?php foreach ($pictures as $index => $pic): ?>
                        <div class="aspect-video overflow-hidden <?= $index >= $visiblePicLimit ? 'hidden extra-property-pic' : '' ?>">
                            <img src="<?= $assetBase ?>images/uploads/properties/<?= htmlspecialchars($pic->pic_name) ?>"
                                alt="<?= htmlspecialchars($propertyLabel) ?>"
                                class="w-full h-full object-cover">
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($hasMorePics): ?>
                    <div class="flex justify-center py-3 bg-gray-100 dark:bg-gray-950 border-t border-gray-200 dark:border-gray-800">
                        <button type="button" id="toggle-property-pics-btn" data-expanded="false" data-total="<?= $totalPics ?>"
                            class="inline-flex items-center gap-1.5 text-xs font-black uppercase tracking-wider text-primary-600 dark:text-primary-400 hover:underline">
                            <span id="toggle-property-pics-label">View All Photos (<?= $totalPics ?>)</span>
                            <svg id="toggle-property-pics-icon" class="w-3.5 h-3.5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="h-40 bg-gray-50 dark:bg-gray-900/40 border-b border-dashed border-gray-200 dark:border-gray-800 flex items-center justify-center">
                <span class="text-[10px] font-black text-gray-300 dark:text-gray-700 uppercase tracking-widest">No Photos Available</span>
            </div>
        <?php endif; ?>

        <div class="p-8 space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 border border-primary-100 dark:border-primary-900/50">
                        <i class="fa-solid fa-key text-[10px]"></i>
                        <?= htmlspecialchars($service->name ?? 'Service') ?>
                    </span>
                    <h1 class="text-2xl font-black text-gray-900 dark:text-white mt-3"><?= htmlspecialchars($propertyLabel) ?></h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-1.5">
                        <i class="fa-solid fa-location-dot text-xs"></i>
                        <?= htmlspecialchars($fullAddress ?: 'Address unavailable') ?>
                    </p>
                </div>
            </div>

            <?php if ($currentTenant): ?>
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pt-2">
                    <div class="flex-1">
                        <h2 class="text-lg font-black text-gray-900 dark:text-white">Your Applications</h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Welcome back, <?= htmlspecialchars($currentTenant->first_name) ?>. Track and manage your submissions for this property.
                        </p>
                    </div>

                    <div class="mt-2 md:mt-0 flex flex-row gap-3 items-center">
                        <div class="relative flex-1 md:w-64">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" id="tenant-applications-search"
                                class="block w-full rounded-xl border-2 border-gray-400 dark:border-gray-600 bg-white dark:bg-gray-900 py-2 pl-10 pr-3 text-sm placeholder-gray-400 focus:border-primary-500 focus:ring-primary-500 text-gray-900 dark:text-white transition-all font-sans"
                                placeholder="Search applications...">
                        </div>

                        <button type="button" id="start-new-application-btn" data-tooltip="Start New Application"
                            class="shrink-0 flex items-center justify-center rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-bold text-white shadow-md hover:bg-primary-700 transition-all active:scale-95 focus:ring-2 focus:ring-primary-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                            <svg class="w-5 h-5 md:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span class="hidden xs:inline md:inline">Start New Application</span>
                        </button>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden">
                    <div class="w-full overflow-hidden">
                        <table class="w-full divide-y divide-gray-200 dark:divide-gray-800 table-fixed">
                            <thead class="bg-gray-50 dark:bg-gray-800/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-[40%]">Application</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell w-[25%]">Property</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell w-[20%]">Submitted</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-[15%]">Status</th>
                                </tr>
                            </thead>
                            <tbody id="tenant-applications-tbody" class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                        <div class="flex flex-col items-center">
                                            <svg class="h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <p class="font-medium font-sans">No applications found yet</p>
                                            <p class="text-xs text-gray-400 mt-1">Click "Start New Application" to begin.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30">
                        <p class="text-sm text-gray-600 dark:text-gray-400 font-medium font-sans">Showing 0 applications</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="rounded-2xl bg-gray-50 dark:bg-gray-950/40 border border-gray-100 dark:border-gray-800 p-5">
                    <h2 class="text-xs font-black text-primary-500 uppercase tracking-[0.2em] mb-2">Summary</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                        You've been granted access to <span class="font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($service->name ?? 'this service') ?></span>
                        for <span class="font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($propertyLabel) ?></span>.
                        Sign in or create an account to continue and complete your submission for this property.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 pt-2">
                    <a href="#" data-login-button
                        class="flex-1 inline-flex items-center justify-center px-6 py-3 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-black text-xs uppercase tracking-wider transition-all shadow-md shadow-primary-500/20">
                        Sign In
                    </a>
                    <a href="#" id="apply-create-account-btn" data-return-to="<?= htmlspecialchars('/apply/' . rawurlencode($tokenCode)) ?>"
                        class="flex-1 inline-flex items-center justify-center px-6 py-3 rounded-xl border-2 border-primary-200 dark:border-primary-900/50 text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-950/40 font-black text-xs uppercase tracking-wider transition-all">
                        Create Account
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

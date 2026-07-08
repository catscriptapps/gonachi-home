<?php
// /resources/views/pages/rental-opportunities.php

declare(strict_types=1);

/**
 * Gonachi Landlord & Tenant Validation Engine - Rental Opportunities Feed
 *
 * UI-first pass matching landlord_and_tenant_validation.pdf's "Rental
 * Opportunity Flow" and "Property Preview" fields. No backend yet — content
 * below is illustrative.
 */

$properties = [
    [
        'location' => 'Lekki Phase 1, Lagos',
        'rent' => '₦2,500,000 / year',
        'type' => '3 Bedroom Flat',
        'added' => '2 Days Ago',
        'landlordStatus' => 'Verified',
        'score' => 92,
    ],
    [
        'location' => 'Yaba, Lagos',
        'rent' => '₦900,000 / year',
        'type' => 'Self Contain',
        'added' => '4 Days Ago',
        'landlordStatus' => 'Unverified',
        'score' => 40,
    ],
    [
        'location' => 'Ikeja GRA, Lagos',
        'rent' => '₦4,000,000 / year',
        'type' => 'Duplex',
        'added' => '1 Week Ago',
        'landlordStatus' => 'Verified',
        'score' => 88,
    ],
];

$statusStyles = [
    'Verified' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400',
    'Unverified' => 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400',
];
?>
<div class="space-y-6">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Rental Opportunities</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Live listings, cross-checked against our landlord verification records.</p>
        </div>
        <div class="flex items-center gap-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl px-4 py-2.5">
            <div class="text-center">
                <span class="block text-lg font-bold text-indigo-600 dark:text-indigo-400">50</span>
                <span class="text-[10px] font-medium text-gray-400 uppercase tracking-wider">Lekki</span>
            </div>
            <div class="text-center">
                <span class="block text-lg font-bold text-indigo-600 dark:text-indigo-400">30</span>
                <span class="text-[10px] font-medium text-gray-400 uppercase tracking-wider">Yaba</span>
            </div>
            <div class="text-center">
                <span class="block text-lg font-bold text-indigo-600 dark:text-indigo-400">20</span>
                <span class="text-[10px] font-medium text-gray-400 uppercase tracking-wider">Ikeja</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($properties as $property): ?>
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 shadow-sm hover:border-indigo-500/50 transition-all">
                <div class="flex items-start justify-between gap-2 mb-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-400">
                        <?= htmlspecialchars($property['type']) ?>
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?= $statusStyles[$property['landlordStatus']] ?>">
                        <?= htmlspecialchars($property['landlordStatus']) ?> Landlord
                    </span>
                </div>

                <h4 class="text-base font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($property['location']) ?></h4>
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mt-1"><?= htmlspecialchars($property['rent']) ?></p>

                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800/80">
                    <div class="flex items-center justify-between text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5">
                        <span>Review Score</span>
                        <span><?= $property['score'] ?>%</span>
                    </div>
                    <div class="h-1.5 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                        <div class="h-full rounded-full bg-indigo-500" style="width: <?= $property['score'] ?>%"></div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4">
                    <span class="text-xs text-gray-400">Added <?= htmlspecialchars($property['added']) ?></span>
                    <button disabled title="Coming soon" class="inline-flex items-center px-3.5 py-2 bg-gray-100 dark:bg-gray-800 text-gray-400 font-bold text-xs rounded-lg cursor-not-allowed whitespace-nowrap">
                        Unlock Contact
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

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

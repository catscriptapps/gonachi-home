<?php
// /resources/views/pages/landlord-tenant-validation.php

declare(strict_types=1);

/**
 * Gonachi Landlord & Tenant Validation Engine - Main Discovery Viewport
 *
 * UI-first pass matching landlord_and_tenant_validation.pdf's vision
 * (report-a-landlord contribution loop, confidence engine, rental
 * opportunity feed). No backend yet — content below is illustrative,
 * mirroring how Real Estate Leads and Contractor Discovery both started.
 */

$opportunities = [
    ['area' => 'Lekki', 'count' => 50],
    ['area' => 'Yaba', 'count' => 30],
    ['area' => 'Ikeja', 'count' => 20],
];
?>
<div class="max-w-5xl mx-auto space-y-12">

    <!-- Hero -->
    <div class="text-center max-w-2xl mx-auto pt-4">
        <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 dark:text-white">
            Check If Your Landlord Has Previous Complaints — Before Renting
        </h1>
        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
            A searchable record of landlords and tenants in Nigeria. Report a problem, help the next renter, and unlock the rental opportunity feed.
        </p>

        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
            <button class="w-full sm:w-auto px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition-colors shadow-sm">
                Report A Landlord
            </button>
            <div class="w-full sm:w-80 relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" placeholder="Search a landlord or address..." class="w-full pl-10 pr-4 py-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-900 dark:text-white" />
            </div>
        </div>
    </div>

    <!-- Live Counters -->
    <div class="flex items-center justify-center gap-10">
        <div class="text-center">
            <span class="block text-3xl font-bold text-indigo-600">142</span>
            <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Property Records</span>
        </div>
        <div class="h-10 w-px bg-gray-200 dark:bg-gray-800"></div>
        <div class="text-center">
            <span class="block text-3xl font-bold text-gray-900 dark:text-white">38</span>
            <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Landlord Reports</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Confidence Engine Showcase -->
        <div class="lg:col-span-2 space-y-3">
            <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider">Recently Added Property Record</h3>

            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                            Unverified
                        </span>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white mt-2">House 14, Lekki</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Landlord: Mr X &middot; 1 report</p>
                    </div>
                    <span class="text-xs font-medium text-gray-400 whitespace-nowrap">Photos Available</span>
                </div>

                <div class="mt-6">
                    <div class="flex items-center justify-between text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5">
                        <span>Verification Confidence</span>
                        <span>40%</span>
                    </div>
                    <div class="h-2 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                        <div class="h-full rounded-full bg-indigo-500" style="width: 40%"></div>
                    </div>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">
                        Confidence grows as tenants confirm details, ownership documents are uploaded, and reviews come in — this record could reach 95%.
                    </p>
                </div>
            </div>
        </div>

        <!-- Rental Opportunities -->
        <div class="space-y-3">
            <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider">Rental Opportunities</h3>
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl divide-y divide-gray-100 dark:divide-gray-800 overflow-hidden shadow-sm">
                <?php foreach ($opportunities as $opportunity): ?>
                    <a href="<?= $baseUrl ?>rental-opportunities" class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-gray-800/40 text-sm group transition-colors">
                        <span class="font-medium text-gray-700 dark:text-gray-300 group-hover:text-indigo-600">New Listings in <?= htmlspecialchars($opportunity['area']) ?></span>
                        <span class="text-xs font-bold text-indigo-600 bg-indigo-50 dark:bg-indigo-950/40 dark:text-indigo-400 px-2 py-0.5 rounded-full"><?= $opportunity['count'] ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
            <p class="text-xs text-gray-400 dark:text-gray-500 px-1">Unlocked after your first contribution.</p>
        </div>

    </div>
</div>

<?php
// /resources/views/pages/landlord-tenant-validation.php

declare(strict_types=1);

/**
 * Coming Soon placeholder for the Landlord & Tenant Validation Engine.
 * Renders inside layouts/portal.php (no project sidebar yet — there's no
 * project behind this tab to have a sidebar for).
 */

$capabilities = ['Report a Landlord', 'Check Landlord History', 'Rental Opportunity Feed', 'Tenant References', 'Verification Confidence Score'];
?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-28 text-center">
    <div class="w-16 h-16 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mx-auto mb-6">
        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
        </svg>
    </div>

    <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-full bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">Coming Soon</span>

    <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 dark:text-white mt-4">Landlord & Tenant Validation Engine</h1>
    <p class="mt-4 text-base text-gray-500 dark:text-gray-400 max-w-xl mx-auto">
        A searchable database of landlord and tenant records in Nigeria — functioning like a credit bureau, but for renting. Tenants avoid problematic landlords, landlords avoid problematic tenants, and property history becomes transparent.
    </p>

    <div class="flex flex-wrap justify-center gap-2 mt-8">
        <?php foreach ($capabilities as $capability): ?>
            <span class="text-xs font-medium px-3 py-1.5 rounded-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-400"><?= htmlspecialchars($capability) ?></span>
        <?php endforeach; ?>
    </div>

    <a href="<?= $baseUrl ?>" class="inline-flex items-center mt-10 text-sm font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 transition-colors">
        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to Gonachi
    </a>
</div>

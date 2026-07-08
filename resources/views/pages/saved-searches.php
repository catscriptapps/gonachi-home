<?php
// /resources/views/pages/saved-searches.php

declare(strict_types=1);

/**
 * Gonachi Real Estate Lead Engine - Saved Alerts
 *
 * UI-first pass (no persistence yet) — matching how Contractor Discovery
 * and Landlord & Tenant Validation both started before their backends
 * landed. Per the requirements doc, saved searches unlock notifications
 * the moment a new lead matches a saved category + location.
 */
?>
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Saved Alerts</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Get notified the moment a new lead matches a search you care about.</p>
    </div>

    <!-- Empty State -->
    <div class="bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-800 rounded-xl p-10 text-center">
        <svg class="h-8 w-8 text-gray-300 dark:text-gray-700 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L7 21V5z" /></svg>
        <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300">No Saved Alerts Yet</h4>
        <p class="text-xs text-gray-400 dark:text-gray-500 max-w-sm mx-auto mt-1">
            Saving alerts from a category page (like Home Buyers in Lagos) isn't wired up yet — once it is, new matching leads will notify you here automatically.
        </p>
    </div>
</div>

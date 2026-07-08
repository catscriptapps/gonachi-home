<?php
// /resources/views/partials/layout-sidebar.php

declare(strict_types=1);

/**
 * Gonachi Real Estate Lead Engine - Left Navigation Sidebar
 */
?>
<aside
    class="fixed inset-y-0 left-0 z-50 flex flex-col w-64 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 transition-all duration-300 ease-in-out"
    :class="[$store.sidebar.expanded ? 'lg:w-64' : 'lg:w-24', mobileMenuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0']"
    x-cloak>

    <!-- Back to the Gonachi project hub -->
    <a href="<?= $baseUrl ?>" x-show="$store.sidebar.expanded || mobileMenuOpen" class="flex items-center gap-1.5 px-6 pt-4 text-xs font-semibold text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        All Projects
    </a>

    <!-- Sidebar Header: Identity & Brand Logo -->
    <div class="h-20 flex items-center px-6 border-b border-gray-200 dark:border-gray-800 justify-between">
        <a href="<?= $baseUrl ?>real-estate-leads" class="flex items-center space-x-3 overflow-hidden">
            <img src="<?= $assetBase ?>images/logo/favicon.png" alt="Gonachi Logo" class="h-16 w-16 flex-shrink-0" />
            <span
                class="font-bold text-2xl tracking-tight text-gray-900 dark:text-white transition-opacity duration-200"
                x-show="$store.sidebar.expanded || mobileMenuOpen"
                x-transition:enter="delay-100 duration-200">
                Gonachi
            </span>
        </a>

        <!-- Mobile Close Trigger -->
        <button @click="mobileMenuOpen = false" class="lg:hidden p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 focus:outline-none">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <?php $sidebarUserId = \Src\Service\AuthService::userId(); ?>
    <?php if ($sidebarUserId): ?>
        <!-- User Value Proposition Balance Tracker -->
        <div class="p-4 border-b border-gray-100 dark:border-gray-800/50" x-show="$store.sidebar.expanded || mobileMenuOpen">
            <a href="<?= $baseUrl ?>transactions" class="block bg-primary-50 dark:bg-primary-950/40 rounded-xl p-3 border border-primary-100 dark:border-primary-900/30 hover:border-primary-300 dark:hover:border-primary-800 transition-colors">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-primary-700 dark:text-primary-400 font-medium">Available Credits</span>
                    <span class="text-xs bg-primary-600 text-white font-bold px-2 py-0.5 rounded-full"><?= \Src\Service\CreditService::getBalance($sidebarUserId) ?></span>
                </div>
            </a>
        </div>
    <?php endif; ?>

    <!-- Navigation Directory -->
    <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
        <a href="<?= $baseUrl ?>real-estate-leads" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl bg-primary-500/10 text-primary-600 dark:text-primary-400 font-semibold group transition-colors">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            <span x-show="$store.sidebar.expanded || mobileMenuOpen" class="text-sm">Active Leads Engine</span>
        </a>

        <a href="<?= $baseUrl ?>saved-searches" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/60 font-medium transition-colors group">
            <svg class="h-5 w-5 text-gray-400 group-hover:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L7 21V5z" />
            </svg>
            <span x-show="$store.sidebar.expanded || mobileMenuOpen" class="text-sm">Saved Alerts</span>
        </a>

        <a href="<?= $baseUrl ?>transactions" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/60 font-medium transition-colors group">
            <svg class="h-5 w-5 text-gray-400 group-hover:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span x-show="$store.sidebar.expanded || mobileMenuOpen" class="text-sm">Billing & Credits</span>
        </a>

        <?php if (\Src\Service\AuthService::isAdmin()): ?>
            <a href="<?= $baseUrl ?>lead-review" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/60 font-medium transition-colors group">
                <svg class="h-5 w-5 text-gray-400 group-hover:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span x-show="$store.sidebar.expanded || mobileMenuOpen" class="text-sm">Lead Review Queue</span>
            </a>
        <?php endif; ?>

        <?php $currentProjectSlug = 'real-estate-leads'; ?>
        <?php include __DIR__ . '/project-switcher.php'; ?>
    </nav>

    <!-- Sidebar Collapsing Action Footer (desktop only — mobile has no icon-only collapsed state) -->
    <div class="hidden lg:flex p-4 border-t border-gray-200 dark:border-gray-800 justify-end">
        <button
            @click="$store.sidebar.expanded = !$store.sidebar.expanded"
            class="p-2 rounded-xl bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 focus:outline-none">
            <svg class="h-5 w-5 transform transition-transform duration-300" :class="!$store.sidebar.expanded && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
            </svg>
        </button>
    </div>
</aside>
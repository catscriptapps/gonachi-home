<?php
// /resources/views/partials/layout-sidebar.php

declare(strict_types=1);

/**
 * Gonachi Real Estate Lead Engine - Left Navigation Sidebar
 */
?>
<aside 
    class="fixed inset-y-0 left-0 z-50 flex flex-col bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 transition-all duration-300 ease-in-out"
    :class="$store.sidebar.expanded ? 'w-64' : 'w-24' "
    x-cloak>
    
    <!-- Sidebar Header: Identity & Brand Logo -->
    <div class="h-16 flex items-center px-6 border-b border-gray-200 dark:border-gray-800 justify-between">
        <a href="<?= $baseUrl ?>" class="flex items-center space-x-3 overflow-hidden">
            <img src="<?= $assetBase ?>images/logo/favicon.png" alt="Gonachi Logo" class="h-8 w-8 flex-shrink-0" />
            <span 
                class="font-bold text-xl tracking-tight text-gray-900 dark:text-white transition-opacity duration-200"
                x-show="$store.sidebar.expanded"
                x-transition:enter="delay-100 duration-200">
                Gonachi
            </span>
        </a>
    </div>

    <!-- User Value Proposition Balance Tracker -->
    <div class="p-4 border-b border-gray-100 dark:border-gray-800/50" x-show="$store.sidebar.expanded">
        <div class="bg-teal-50 dark:bg-teal-950/40 rounded-xl p-3 border border-teal-100 dark:border-teal-900/30">
            <div class="flex items-center justify-between">
                <span class="text-xs text-teal-700 dark:text-teal-400 font-medium">Available Credits</span>
                <span class="text-xs bg-teal-600 text-white font-bold px-2 py-0.5 rounded-full">12</span>
            </div>
        </div>
    </div>

    <!-- Navigation Directory -->
    <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
        <a href="<?= $baseUrl ?>" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 font-semibold group transition-colors">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            <span x-show="$store.sidebar.expanded" class="text-sm">Active Leads Engine</span>
        </a>

        <a href="<?= $baseUrl ?>saved-searches" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/60 font-medium transition-colors group">
            <svg class="h-5 w-5 text-gray-400 group-hover:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L7 21V5z" />
            </svg>
            <span x-show="$store.sidebar.expanded" class="text-sm">Saved Alerts</span>
        </a>

        <a href="<?= $baseUrl ?>transactions" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/60 font-medium transition-colors group">
            <svg class="h-5 w-5 text-gray-400 group-hover:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span x-show="$store.sidebar.expanded" class="text-sm">Billing & Credits</span>
        </a>
    </nav>

    <!-- Sidebar Collapsing Action Footer -->
    <div class="p-4 border-t border-gray-200 dark:border-gray-800 flex justify-end">
        <button 
            @click="$store.sidebar.expanded = !$store.sidebar.expanded" 
            class="p-2 rounded-xl bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 focus:outline-none">
            <svg class="h-5 w-5 transform transition-transform duration-300" :class="!$store.sidebar.expanded && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
            </svg>
        </button>
    </div>
</aside>
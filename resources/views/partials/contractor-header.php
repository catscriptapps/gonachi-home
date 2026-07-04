<?php
// /resources/views/partials/contractor-header.php

declare(strict_types=1);

/**
 * Gonachi Contractor Discovery & Opportunity Engine - Core Dynamic Topbar
 */
?>
<header class="h-20 border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 flex items-center justify-between px-4 sm:px-6 lg:px-8 sticky top-0 z-40 transition-colors duration-300">

    <!-- Structural Controls (Responsive Open/Close triggers) -->
    <div class="flex items-center space-x-4">
        <!-- Desktop Quick Expand -->
        <button
            @click="$store.sidebar.expanded = !$store.sidebar.expanded"
            class="hidden lg:block text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 focus:outline-none">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <!-- Target System Context Heading -->
        <span class="text-sm font-semibold text-gray-500 dark:text-gray-400 hidden sm:inline-block">
            Contractor Opportunity Network
        </span>
    </div>

    <!-- Profile Actions, Dark Mode Switcher Infrastructure -->
    <div class="flex items-center space-x-4">

        <!-- Dark Mode Toggle Trigger Component -->
        <button
            @click="$store.theme.isDark = !$store.theme.isDark; document.documentElement.classList.toggle('dark', $store.theme.isDark)"
            class="p-2 rounded-xl text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all focus:outline-none"
            aria-label="Toggle Dark Mode">
            <!-- Light icon -->
            <svg x-show="!$store.theme.isDark" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.344l-.707.707M12 5a7 7 0 100 14 7 7 0 000-14z" />
            </svg>
            <!-- Dark icon -->
            <svg x-show="$store.theme.isDark" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-cloak>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
        </button>

        <div class="h-6 w-px bg-gray-200 dark:bg-gray-800"></div>

        <!-- Account Meta Details -->
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 rounded-full bg-secondary-600 text-white font-bold flex items-center justify-center text-sm shadow-sm">
                R
            </div>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 hidden md:block">Client Workspace</span>
        </div>
    </div>
</header>

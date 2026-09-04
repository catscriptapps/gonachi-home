<?php
// /resources/views/partials/contractor-sidebar.php

declare(strict_types=1);

/**
 * Gonachi Contractor Discovery & Opportunity Engine - Left Navigation Sidebar
 */
?>
<aside
    class="fixed inset-y-0 left-0 z-50 flex flex-col w-64 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 transition-all duration-300 ease-in-out"
    :class="[$store.sidebar.expanded ? 'lg:w-64' : 'lg:w-24', mobileMenuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0']"
    x-cloak>

    <!-- Back to the Gonachi project hub -->
    <a href="<?= $baseUrl ?>" x-show="$store.sidebar.expanded || mobileMenuOpen" class="mx-4 mt-4 flex items-center gap-2 px-3 py-2 rounded-xl bg-gray-50 dark:bg-gray-800/60 hover:bg-secondary-50 dark:hover:bg-secondary-950/40 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:text-secondary-600 dark:hover:text-secondary-400 transition-colors">
        <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Gonachi Home
    </a>

    <!-- Sidebar Header: Identity & Brand Logo -->
    <div class="h-28 relative flex items-center justify-center px-6 border-b border-gray-200 dark:border-gray-800">
        <a href="<?= $baseUrl ?>contractor-discovery" data-partial class="flex items-center justify-center">
            <img src="<?= $assetBase ?>images/logo/favicon.png" alt="Gonachi Logo" class="h-24 w-24 flex-shrink-0 rounded-full object-contain bg-white ring-2 ring-black/5 dark:ring-white/10 shadow-md" />
        </a>

        <!-- Mobile Close Trigger -->
        <button @click="mobileMenuOpen = false" class="lg:hidden absolute right-6 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 focus:outline-none">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <?php
    // Seeds the correct active nav item on each full page load (this
    // sidebar's links aren't on the SPA partial-load router yet, so
    // there's no client-side updateActiveLink() to hand off to).
    $navActiveClasses = 'bg-secondary-500/10 text-secondary-600 dark:text-secondary-400 font-semibold';
    $navInactiveClasses = 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/60 font-medium';
    $currentPath = $path ?? '';
    ?>

    <!-- Navigation Directory -->
    <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto custom-scrollbar" data-nav-accent="secondary">
        <a href="<?= $baseUrl ?>contractor-discovery" data-partial class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-colors group <?= $currentPath === '/contractor-discovery' ? $navActiveClasses : $navInactiveClasses ?>">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
            </svg>
            <span x-show="$store.sidebar.expanded || mobileMenuOpen" class="text-sm">Contractor Directory</span>
        </a>

        <a href="<?= $baseUrl ?>job-requests" data-partial class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-colors group <?= $currentPath === '/job-requests' ? $navActiveClasses : $navInactiveClasses ?>">
            <svg class="h-5 w-5 text-gray-400 group-hover:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span x-show="$store.sidebar.expanded || mobileMenuOpen" class="text-sm">Job Requests</span>
        </a>

        <a href="<?= $baseUrl ?>bidding" data-partial class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-colors group <?= $currentPath === '/bidding' ? $navActiveClasses : $navInactiveClasses ?>">
            <svg class="h-5 w-5 text-gray-400 group-hover:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span x-show="$store.sidebar.expanded || mobileMenuOpen" class="text-sm">Bidding & Quotes</span>
        </a>

        <?php if (\Src\Service\AuthService::isAdmin()): ?>
            <a href="<?= $baseUrl ?>admin" data-partial class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-colors group <?= $currentPath === '/admin' ? $navActiveClasses : $navInactiveClasses ?>">
                <svg class="h-5 w-5 text-gray-400 group-hover:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h7v7H3V3zm0 11h7v7H3v-7zm11-11h7v7h-7V3zm0 11h7v7h-7v-7z" />
                </svg>
                <span x-show="$store.sidebar.expanded || mobileMenuOpen" class="text-sm">Admin Dashboard</span>
            </a>

            <a href="<?= $baseUrl ?>contractor-claims-review" data-partial class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-colors group <?= $currentPath === '/contractor-claims-review' ? $navActiveClasses : $navInactiveClasses ?>">
                <svg class="h-5 w-5 text-gray-400 group-hover:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span x-show="$store.sidebar.expanded || mobileMenuOpen" class="text-sm">Claim Review Queue</span>
            </a>

            <a href="<?= $baseUrl ?>live-chat" data-partial class="flex items-center justify-between px-3 py-2.5 rounded-xl transition-colors group <?= $currentPath === '/live-chat' ? $navActiveClasses : $navInactiveClasses ?>">
                <span class="flex items-center space-x-3">
                    <svg class="h-5 w-5 text-gray-400 group-hover:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <span x-show="$store.sidebar.expanded || mobileMenuOpen" class="text-sm">Live Chat</span>
                </span>
                <span id="live-chat-nav-badge" x-show="$store.sidebar.expanded || mobileMenuOpen" class="hidden flex-shrink-0 min-w-[1.25rem] h-5 px-1 rounded-full bg-red-600 text-white text-xs font-bold flex items-center justify-center">0</span>
            </a>
        <?php endif; ?>

        <a href="<?= $baseUrl ?>contact" data-partial class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-colors group <?= $currentPath === '/contact' ? $navActiveClasses : $navInactiveClasses ?>">
            <svg class="h-5 w-5 text-gray-400 group-hover:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
            </svg>
            <span x-show="$store.sidebar.expanded || mobileMenuOpen" class="text-sm">Contact</span>
        </a>

        <?php $currentProjectSlug = 'contractor-discovery'; ?>
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

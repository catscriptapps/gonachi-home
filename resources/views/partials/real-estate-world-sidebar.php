<?php
// /resources/views/partials/real-estate-world-sidebar.php

declare(strict_types=1);

/**
 * Gonachi Real Estate World - Left Navigation Sidebar
 *
 * Nav items mirror the legacy gonachi/ platform's guest menu exactly (see
 * gonachi/src/Config/NavigationConfig.php::publicLinks()) - Home, About,
 * Social Feed, Adverts, Quotations, Mentors, Listings, Contact. Only Home,
 * About, and Contact have pages ported over so far; the rest are
 * placeholders until those modules are built out here.
 */
?>
<aside
    class="fixed inset-y-0 left-0 z-50 flex flex-col w-64 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 transition-all duration-300 ease-in-out"
    :class="[$store.sidebar.expanded ? 'lg:w-64' : 'lg:w-24', mobileMenuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0']"
    x-cloak>

    <!-- Back to the Gonachi project hub -->
    <a href="<?= $baseUrl ?>" x-show="$store.sidebar.expanded || mobileMenuOpen" class="mx-4 mt-4 flex items-center gap-2 px-3 py-2 rounded-xl bg-gray-50 dark:bg-gray-800/60 hover:bg-teal-50 dark:hover:bg-teal-950/40 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:text-teal-600 dark:hover:text-teal-400 transition-colors">
        <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Home
    </a>

    <!-- Sidebar Header: Identity & Brand Logo -->
    <div class="h-28 relative flex items-center justify-center px-6 border-b border-gray-200 dark:border-gray-800">
        <a href="<?= $baseUrl ?>real-estate-world" data-partial class="flex items-center justify-center">
            <img src="<?= $assetBase ?>images/logo/favicon.png" alt="Gonachi Logo" class="h-24 w-24 flex-shrink-0 rounded-full object-contain bg-white ring-2 ring-black/5 dark:ring-white/10 shadow-md" />
        </a>

        <!-- Mobile Close Trigger -->
        <button @click="mobileMenuOpen = false" class="lg:hidden absolute right-6 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 focus:outline-none">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <?php
    $navActiveClasses = 'bg-teal-500/10 text-teal-600 dark:text-teal-400 font-semibold';
    $navInactiveClasses = 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800/60 font-medium';
    $currentPath = $path ?? '';

    // Mirrors gonachi/src/Config/NavigationConfig.php::publicLinks() exactly
    // (label, slug, icon path, stroke-width) — Blog Posts is commented out
    // there too, so it's left out here as well.
    $guestNavItems = [
        ['label' => 'Home', 'slug' => 'real-estate-world', 'strokeWidth' => '2', 'icon' => 'M3 12l9-9 9 9v9a2 2 0 0 1-2 2h-4a2 2 0 0 1-2-2v-4H9v4a2 2 0 0 1-2 2H3v-9z'],
        ['label' => 'About', 'slug' => 'about', 'strokeWidth' => '2', 'icon' => 'M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20z'],
        ['label' => 'Social Feed', 'slug' => 'social-feed', 'strokeWidth' => '1.75', 'icon' => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z'],
        ['label' => 'Adverts', 'slug' => 'adverts', 'strokeWidth' => '2', 'icon' => 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z'],
        ['label' => 'Quotations', 'slug' => 'quotations', 'strokeWidth' => '1.75', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['label' => 'Mentors', 'slug' => 'mentors', 'strokeWidth' => '2', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
        ['label' => 'Listings', 'slug' => 'listings', 'strokeWidth' => '2', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        ['label' => 'Contact', 'slug' => 'contact', 'strokeWidth' => '1', 'icon' => 'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75'],
    ];
    ?>

    <!-- Navigation Directory -->
    <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto" data-nav-accent="teal">
        <?php foreach ($guestNavItems as $item): ?>
            <a href="<?= $baseUrl . $item['slug'] ?>" data-partial class="flex items-center space-x-3 px-3 py-2.5 rounded-xl transition-colors group <?= $currentPath === '/' . $item['slug'] ? $navActiveClasses : $navInactiveClasses ?>">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="<?= $item['strokeWidth'] ?>" d="<?= $item['icon'] ?>" />
                </svg>
                <span x-show="$store.sidebar.expanded || mobileMenuOpen" class="text-sm"><?= htmlspecialchars($item['label']) ?></span>
            </a>
        <?php endforeach; ?>

        <?php $currentProjectSlug = 'real-estate-world'; ?>
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

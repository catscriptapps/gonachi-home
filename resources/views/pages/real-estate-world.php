<?php
// /resources/views/pages/real-estate-world.php

declare(strict_types=1);

/**
 * Gonachi Real Estate World - Ecosystem Overview
 *
 * Unlike the other three Gonachi projects (which extract/curate records
 * from public sources), Real Estate World is a global submission
 * platform: landlords, tenants, agents, contractors, and property
 * managers anywhere in the world submit their own adverts, listings, and
 * quotation requests directly to us. The "Platform Modules" section below
 * mirrors the legacy platform's guest nav exactly (see gonachi/src/Config/
 * NavigationConfig.php::publicLinks() and gonachi/resources/views/pages/
 * {social-feed,adverts,quotations,mentors,listings}.php for the source
 * material this page adapts) rather than referencing the other projects.
 *
 * @var bool $isLoggedIn
 * @var string $baseUrl
 * @var string $assetBase
 */

use Src\Utils\CuratedPhotos;

$slideshowImages = CuratedPhotos::fromHomeFolder($assetBase);

$roles = [
    [
        'name' => 'Landlords',
        'text' => 'Validate tenants and manage rental properties with confidence.',
        'accent' => 'indigo',
        'href' => $baseUrl . 'landlord-tenant-validation',
        'cta' => 'Validate a Tenant',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />',
    ],
    [
        'name' => 'Tenants',
        'text' => 'Check a landlord\'s record before signing, and browse rental opportunities.',
        'accent' => 'indigo',
        'href' => $baseUrl . 'rental-opportunities',
        'cta' => 'Browse Rentals',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />',
    ],
    [
        'name' => 'Agents & Brokers',
        'text' => 'Tap into live buyer and seller intent signals to grow your pipeline.',
        'accent' => 'primary',
        'href' => $baseUrl . 'real-estate-leads',
        'cta' => 'Find Leads',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />',
    ],
    [
        'name' => 'Contractors',
        'text' => 'Get discovered by property owners and receive job requests directly.',
        'accent' => 'secondary',
        'href' => $baseUrl . 'contractor-discovery',
        'cta' => 'Join The Directory',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />',
    ],
    [
        'name' => 'Property Managers',
        'text' => 'Streamline portfolio oversight across every stakeholder relationship.',
        'accent' => 'teal',
        'href' => $baseUrl . 'contact',
        'cta' => 'Get In Touch',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />',
    ],
];

$roleAccentClasses = [
    'primary' => 'bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 group-hover:bg-primary-500 group-hover:text-white',
    'secondary' => 'bg-secondary-50 dark:bg-secondary-950/40 text-secondary-600 dark:text-secondary-400 group-hover:bg-secondary-500 group-hover:text-white',
    'indigo' => 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 group-hover:bg-indigo-500 group-hover:text-white',
    'teal' => 'bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 group-hover:bg-teal-500 group-hover:text-white',
];

// Mirrors gonachi/src/Config/NavigationConfig.php::publicLinks() exactly —
// same 5 modules, same order, same icons — with descriptions adapted from
// each module's own hero copy in the legacy platform (see the page-level
// doc comment above for the source files).
$modules = [
    [
        'name' => 'Social Feed',
        'text' => "See what's happening across the network. Share updates, photos, and videos.",
        'slug' => 'social-feed',
        'live' => true,
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />',
    ],
    [
        'name' => 'Adverts',
        'text' => 'Connect with landlords, tenants, and pros — showcase your brand on the largest real estate social hub. Starting at $0.',
        'slug' => 'adverts',
        'live' => true,
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />',
    ],
    [
        'name' => 'Quotations',
        'text' => 'Fill out your request, upload media, and receive competitive bids from verified contractors instantly.',
        'slug' => 'quotations',
        'live' => true,
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
    ],
    [
        'name' => 'Mentors',
        'text' => 'Bridge the knowledge gap — connect with seasoned experts, searchable by skill or specialty.',
        'slug' => 'mentors',
        'live' => true,
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />',
    ],
    [
        'name' => 'Listings',
        'text' => 'Explore trending rentals, exclusive sales, and professional services tailored to your location.',
        'slug' => 'listings',
        'live' => true,
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />',
    ],
];
?>
<div class="space-y-6">

    <?php
    $breadcrumbs = [['label' => 'Real Estate World']];
    $breadcrumbAccent = 'teal';
    include __DIR__ . '/../components/breadcrumbs.php';
    ?>

    <!-- Hero Banner -->
    <section class="relative overflow-hidden rounded-3xl shadow-sm">
        <?php include __DIR__ . '/../components/hero-slideshow.php'; ?>
        <?php if (!empty($slideshowImages)): ?>
            <div class="absolute inset-0 bg-gray-50/85 dark:bg-gray-950/85"></div>
        <?php else: ?>
            <div class="absolute inset-0 bg-white dark:bg-gray-900"></div>
        <?php endif; ?>

        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-6 p-6 sm:p-10">
            <div>
                <span class="inline-block text-xs font-semibold tracking-[0.2em] text-teal-600 dark:text-teal-400 uppercase mb-2">Real Estate World</span>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">The Ultimate Nexus For Real Estate Stakeholders</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 max-w-md">A global submission platform — landlords, tenants, agents, contractors, and property managers anywhere in the world post directly to us.</p>
            </div>

            <!-- Ecosystem Counters -->
            <div class="flex items-center space-x-4 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md p-2 rounded-xl border border-gray-200 dark:border-gray-800 flex-shrink-0">
                <div class="px-4 py-2 border-r border-gray-200 dark:border-gray-800 text-center">
                    <span class="block text-2xl font-bold text-teal-600 dark:text-teal-400"><?= count($roles) ?></span>
                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Stakeholder Roles</span>
                </div>
                <div class="px-4 py-2 text-center">
                    <span class="block text-2xl font-bold text-primary-600 dark:text-primary-400">250+</span>
                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Countries Reachable</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Stakeholder Roles -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Built For Every Stakeholder</h3>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <?php foreach ($roles as $role): ?>
                <a href="<?= htmlspecialchars($role['href']) ?>" data-partial
                    class="group flex flex-col bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center mb-4 transition-colors duration-300 <?= $roleAccentClasses[$role['accent']] ?>">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><?= $role['icon'] ?></svg>
                    </div>
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-1"><?= htmlspecialchars($role['name']) ?></h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 flex-1"><?= htmlspecialchars($role['text']) ?></p>
                    <span class="mt-4 flex items-center text-xs font-semibold text-gray-700 dark:text-gray-300 group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors">
                        <?= htmlspecialchars($role['cta']) ?>
                        <svg class="h-3.5 w-3.5 ml-1.5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Platform Modules -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Platform Modules</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Submitted by people worldwide — not extracted from public sources, like the other Gonachi projects.</p>
            </div>
            <span class="text-xs text-teal-600 bg-teal-50 dark:bg-teal-950/40 px-2 py-1 rounded font-medium whitespace-nowrap"><?= count(array_filter($modules, fn($m) => $m['live'])) ?>/<?= count($modules) ?> Live</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($modules as $module): ?>
                <a href="<?= $baseUrl . $module['slug'] ?>" data-partial class="group bg-white dark:bg-gray-900 border <?= $module['live'] ? 'border-gray-200 dark:border-gray-800' : 'border-dashed border-gray-300 dark:border-gray-800' ?> rounded-2xl p-5 hover:border-teal-400 dark:hover:border-teal-700 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-start justify-between">
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center mb-4 bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><?= $module['icon'] ?></svg>
                        </div>
                        <?php if ($module['live']): ?>
                            <span class="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400">
                                <span class="relative flex h-1.5 w-1.5">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                                </span>
                                Live
                            </span>
                        <?php else: ?>
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                Coming Soon
                            </span>
                        <?php endif; ?>
                    </div>
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-1 group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors"><?= htmlspecialchars($module['name']) ?></h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400"><?= htmlspecialchars($module['text']) ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Early Access CTA -->
    <section class="relative rounded-3xl p-8 sm:p-12 text-center overflow-hidden bg-gray-900 dark:bg-gray-900 border border-gray-800">
        <div class="relative z-10 max-w-xl mx-auto">
            <h2 class="text-2xl sm:text-3xl font-bold text-white mb-3">Want early access to the full platform?</h2>
            <p class="text-sm text-gray-400 mb-6">Reach out and we'll let you know as new Real Estate World modules go live.</p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="<?= $baseUrl ?>contact" data-partial class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 bg-teal-500 hover:bg-teal-600 text-white font-bold text-sm rounded-xl transition-colors shadow-sm">
                    Contact Us
                </a>
                <a href="<?= $baseUrl ?>login" data-login-button class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 bg-white/10 hover:bg-white/20 text-white font-bold text-sm rounded-xl border border-white/20 transition-colors">
                    Sign In
                </a>
            </div>
        </div>
    </section>
</div>

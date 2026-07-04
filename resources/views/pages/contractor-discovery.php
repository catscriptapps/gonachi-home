<?php
// /resources/views/pages/contractor-discovery.php

declare(strict_types=1);

/**
 * Gonachi Contractor Discovery & Opportunity Engine - Main Discovery Viewport
 *
 * UI-first pass matching contractor_discovery.pdf's vision (directory,
 * claim-profile self-discovery loop, category/location SEO pages). No
 * backend yet — content below is illustrative, mirroring how the Real
 * Estate Leads project started before its extraction pipeline landed.
 */
?>
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Find A Trusted Contractor</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Search verified plumbers, electricians, builders, and more across active service networks.</p>
        </div>

        <!-- Live Counters -->
        <div class="flex items-center space-x-4 bg-white dark:bg-gray-900 p-2 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
            <div class="px-4 py-2 border-r border-gray-100 dark:border-gray-800 text-center">
                <span class="block text-2xl font-bold text-secondary-600">86</span>
                <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Contractors</span>
            </div>
            <div class="px-4 py-2 text-center">
                <span class="block text-2xl font-bold text-primary-600">23</span>
                <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Open Job Requests</span>
            </div>
        </div>
    </div>

    <!-- Search Routing & Category Filtering Bar -->
    <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col md:flex-row gap-4 items-center">
        <div class="w-full md:flex-1 relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input type="text" placeholder="Search for a service (e.g., 'Plumber in Lagos', 'Roofing Ikeja')..." class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-secondary-500 focus:outline-none text-gray-900 dark:text-white" />
        </div>
        <div class="w-full md:w-48">
            <select class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-secondary-500 focus:outline-none text-gray-700 dark:text-gray-300">
                <option value="">All Regions</option>
                <option value="lagos">Lagos State</option>
                <option value="abuja">Abuja FCT</option>
                <option value="ph">Port Harcourt</option>
            </select>
        </div>
        <button class="w-full md:w-auto px-6 py-2.5 bg-secondary-600 hover:bg-secondary-700 text-white font-bold text-sm rounded-lg transition-colors shadow-sm whitespace-nowrap">
            Get Quotes
        </button>
    </div>

    <!-- Directory + Category Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Primary Directory Listing Column -->
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Recently Added Contractors</h3>
                <span class="text-xs text-secondary-600 bg-secondary-50 dark:bg-secondary-950/40 px-2 py-1 rounded font-medium">Directory Growing Daily</span>
            </div>

            <!-- Contractor Card: Verified -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 hover:border-secondary-500/50 transition-all shadow-sm group">
                <div class="flex items-start justify-between gap-4 mb-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-secondary-100 text-secondary-800 dark:bg-secondary-950 dark:text-secondary-400">
                                Plumbing
                            </span>
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                Verified
                            </span>
                        </div>
                        <h4 class="text-base font-bold text-gray-900 dark:text-white mt-2 group-hover:text-secondary-600 transition-colors">
                            Johnson Plumbing Services
                        </h4>
                    </div>
                    <span class="text-xs font-medium text-amber-500 whitespace-nowrap">&#9733; 4.8 (32)</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 my-4 text-sm border-t border-b border-gray-100 dark:border-gray-800/80 py-3">
                    <div>
                        <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Location</span>
                        <span class="font-medium text-gray-700 dark:text-gray-300">Lekki, Lagos</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Operating Areas</span>
                        <span class="font-medium text-gray-700 dark:text-gray-300">Lekki, Ajah, VI</span>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Response Rate</span>
                        <span class="font-medium text-emerald-600 dark:text-emerald-400">Responds within 2h</span>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <span class="text-xs text-gray-400">Contact details unlock with a full profile view.</span>
                    <button class="inline-flex items-center px-4 py-2 bg-gray-900 hover:bg-gray-800 dark:bg-secondary-600 dark:hover:bg-secondary-500 text-white font-bold text-xs rounded-lg transition-colors shadow-sm tracking-wide">
                        View Profile
                    </button>
                </div>
            </div>

            <!-- Contractor Card: Unclaimed (Self-Discovery Loop) -->
            <div class="bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-700 rounded-xl p-5 transition-all shadow-sm group">
                <div class="flex items-start justify-between gap-4 mb-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-950 dark:text-primary-400">
                                General Contractor
                            </span>
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-gray-400">
                                Unclaimed Profile
                            </span>
                        </div>
                        <h4 class="text-base font-bold text-gray-900 dark:text-white mt-2">
                            Elite Builders Ltd
                        </h4>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 my-4 text-sm border-t border-b border-gray-100 dark:border-gray-800/80 py-3">
                    <div>
                        <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Location</span>
                        <span class="font-medium text-gray-700 dark:text-gray-300">Ikeja, Lagos</span>
                    </div>
                    <div class="col-span-2 sm:col-span-2">
                        <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Source</span>
                        <span class="font-medium text-gray-500 dark:text-gray-400">Public Business Directory</span>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <span class="text-xs text-gray-400">Is this your business?</span>
                    <button class="inline-flex items-center px-4 py-2 bg-secondary-600 hover:bg-secondary-700 text-white font-bold text-xs rounded-lg transition-colors shadow-sm tracking-wide">
                        Claim This Profile
                    </button>
                </div>
            </div>
        </div>

        <!-- SEO/Scalable Category Sidebar Column -->
        <div class="space-y-4">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Popular Searches</h3>
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl divide-y divide-gray-100 dark:divide-gray-800 overflow-hidden shadow-sm">
                <a href="<?= $baseUrl ?>plumbers-lagos" class="flex items-center justify-between p-3.5 hover:bg-gray-50 dark:hover:bg-gray-800/40 text-sm group transition-colors">
                    <span class="font-medium text-gray-700 dark:text-gray-300 group-hover:text-secondary-600">Plumbers in Lagos</span>
                    <svg class="h-4 w-4 text-gray-400 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                <a href="<?= $baseUrl ?>electricians-ikeja" class="flex items-center justify-between p-3.5 hover:bg-gray-50 dark:hover:bg-gray-800/40 text-sm group transition-colors">
                    <span class="font-medium text-gray-700 dark:text-gray-300 group-hover:text-secondary-600">Electricians in Ikeja</span>
                    <svg class="h-4 w-4 text-gray-400 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                <a href="<?= $baseUrl ?>builders-lekki" class="flex items-center justify-between p-3.5 hover:bg-gray-50 dark:hover:bg-gray-800/40 text-sm group transition-colors">
                    <span class="font-medium text-gray-700 dark:text-gray-300 group-hover:text-secondary-600">Builders in Lekki</span>
                    <svg class="h-4 w-4 text-gray-400 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                <a href="<?= $baseUrl ?>roofing-contractors-port-harcourt" class="flex items-center justify-between p-3.5 hover:bg-gray-50 dark:hover:bg-gray-800/40 text-sm group transition-colors">
                    <span class="font-medium text-gray-700 dark:text-gray-300 group-hover:text-secondary-600">Roofing Contractors in Port Harcourt</span>
                    <svg class="h-4 w-4 text-gray-400 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            <div class="bg-secondary-50 dark:bg-secondary-950/40 rounded-xl p-4 border border-secondary-100 dark:border-secondary-900/30">
                <h4 class="text-sm font-bold text-secondary-900 dark:text-secondary-300">Own A Contractor Business?</h4>
                <p class="text-xs text-secondary-700 dark:text-secondary-400 mt-1">Claim your free profile to add photos, certifications, and start receiving job alerts.</p>
                <button class="mt-3 w-full px-4 py-2 bg-secondary-600 hover:bg-secondary-700 text-white font-bold text-xs rounded-lg transition-colors shadow-sm">
                    Find My Business
                </button>
            </div>
        </div>

    </div>
</div>

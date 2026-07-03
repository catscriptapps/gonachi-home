<?php
// /resources/views/pages/home.php

declare(strict_types=1);

/**
 * Gonachi Real Estate Lead Engine - Main Discovery Engine Viewport
 */
?>
<!-- Search Optimization Metadata Block -->
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Active Property Requests</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Discover live home buyers and home sellers signaling real estate intent across active networks.</p>
        </div>
        
        <!-- Live Counters -->
        <div class="flex items-center space-x-4 bg-white dark:bg-gray-900 p-2 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
            <div class="px-4 py-2 border-r border-gray-100 dark:border-gray-800 text-center">
                <span class="block text-2xl font-bold text-primary-600">20</span>
                <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Active Buyers</span>
            </div>
            <div class="px-4 py-2 text-center">
                <span class="block text-2xl font-bold text-indigo-600">50</span>
                <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Active Sellers</span>
            </div>
        </div>
    </div>

    <!-- Search Routing & Location Filtering Bar -->
    <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col md:flex-row gap-4 items-center">
        <div class="w-full md:flex-1 relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input type="text" placeholder="Search intent categories (e.g., 'Lagos House', 'Port Harcourt Land')..." class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none text-gray-900 dark:text-white" />
        </div>
        <div class="w-full md:w-48">
            <select class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none text-gray-700 dark:text-gray-300">
                <option value="">All Regions</option>
                <option value="lagos">Lagos State</option>
                <option value="abuja">Abuja FCT</option>
                <option value="ph">Port Harcourt</option>
            </select>
        </div>
    </div>

    <!-- Active Feed Streams Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Primary Stream Listing Column (Spans 2 cols for visibility) -->
        <div class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Recently Extracted Lead Activity</h3>
                <span class="text-xs text-primary-600 bg-primary-50 dark:bg-primary-950/40 px-2 py-1 rounded font-medium">Real-time Stream</span>
            </div>

            <!-- Lead Entry Item Box -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 hover:border-primary-500/50 transition-all shadow-sm group">
                <div class="flex items-start justify-between gap-4 mb-3">
                    <div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-950 dark:text-primary-400">
                            Home Buyer
                        </span>
                        <h4 class="text-base font-bold text-gray-900 dark:text-white mt-2 group-hover:text-primary-600 transition-colors">
                            Seeking: 4 Bedroom Detached House
                        </h4>
                    </div>
                    <span class="text-xs font-medium text-gray-400 dark:text-gray-500 whitespace-nowrap">11 Hours Ago</span>
                </div>
                
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 my-4 text-sm border-t border-b border-gray-100 dark:border-gray-800/80 py-3">
                    <div>
                        <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Target Target</span>
                        <span class="font-medium text-gray-700 dark:text-gray-300">Lekki, Lagos</span>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Intent Score</span>
                        <span class="font-medium text-emerald-600 dark:text-emerald-400">High Engagement</span>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Origin Source</span>
                        <span class="font-medium text-gray-500 dark:text-gray-400">Public Request Board</span>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <span class="text-xs text-gray-400">Verified identity metadata is available.</span>
                    <button class="inline-flex items-center px-4 py-2 bg-gray-900 hover:bg-gray-800 dark:bg-primary-600 dark:hover:bg-primary-500 text-white font-bold text-xs rounded-lg transition-colors shadow-sm tracking-wide">
                        View Full Details
                    </button>
                </div>
            </div>

            <!-- Example of a Lead locked behind Conversion Gate flow -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 relative overflow-hidden shadow-sm">
                <div class="blur-[2px] opacity-40 select-none pointer-events-none">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">Home Seller</span>
                    <h4 class="text-base font-bold mt-2">Duplex Property for Outright Sale</h4>
                    <div class="grid grid-cols-2 gap-4 my-3 text-sm">
                        <div><span class="block text-xs font-semibold text-gray-400">Location</span><span>Ikeja, Lagos</span></div>
                    </div>
                </div>
                
                <!-- Intercept Conversion Prompt Screen overlay -->
                <div class="absolute inset-0 bg-gradient-to-t from-white via-white/95 to-white/80 dark:from-gray-900 dark:via-gray-900/95 dark:to-gray-900/80 flex flex-col items-center justify-center p-6 text-center">
                    <svg class="h-8 w-8 text-primary-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <h5 class="text-sm font-bold text-gray-900 dark:text-white">Full Information Gated</h5>
                    <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm mt-1 mb-3">Unlock complete records including active contact links by starting your profile account.</p>
                    <div class="flex items-center space-x-3">
                        <a href="#" class="px-4 py-1.5 bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold rounded-lg transition-all shadow-sm">Start Free Trial</a>
                        <a href="#" class="px-4 py-1.5 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-semibold rounded-lg transition-all">Sign In</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEO/Scalable Landing Category Sidebar Column -->
        <div class="space-y-4">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Hot Category Targets</h3>
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl divide-y divide-gray-100 dark:divide-gray-800 overflow-hidden shadow-sm">
                <a href="<?= $baseUrl ?>home-buyers-lagos" class="flex items-center justify-between p-3.5 hover:bg-gray-50 dark:hover:bg-gray-800/40 text-sm group transition-colors">
                    <span class="font-medium text-gray-700 dark:text-gray-300 group-hover:text-primary-600">Home Buyers in Lagos</span>
                    <svg class="h-4 w-4 text-gray-400 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                <a href="<?= $baseUrl ?>home-sellers-lagos" class="flex items-center justify-between p-3.5 hover:bg-gray-50 dark:hover:bg-gray-800/40 text-sm group transition-colors">
                    <span class="font-medium text-gray-700 dark:text-gray-300 group-hover:text-primary-600">Home Sellers in Lagos</span>
                    <svg class="h-4 w-4 text-gray-400 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                <a href="<?= $baseUrl ?>property-investors-abuja" class="flex items-center justify-between p-3.5 hover:bg-gray-50 dark:hover:bg-gray-800/40 text-sm group transition-colors">
                    <span class="font-medium text-gray-700 dark:text-gray-300 group-hover:text-primary-600">Property Investors in Abuja</span>
                    <svg class="h-4 w-4 text-gray-400 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

    </div>
</div>
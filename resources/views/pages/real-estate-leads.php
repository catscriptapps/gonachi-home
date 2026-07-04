<?php
// /resources/views/pages/real-estate-leads.php

declare(strict_types=1);

/**
 * Gonachi Real Estate Lead Engine - Main Discovery Engine Viewport
 *
 * @var bool $isLoggedIn Whether the user is logged in
 */

use Src\Controller\LeadsController;

$leadCounts = LeadsController::activeCounts();
$recentLeads = LeadsController::recentActive(5);
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
                <span class="block text-2xl font-bold text-primary-600"><?= $leadCounts['buyer'] ?></span>
                <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Active Buyers</span>
            </div>
            <div class="px-4 py-2 text-center">
                <span class="block text-2xl font-bold text-indigo-600"><?= $leadCounts['seller'] ?></span>
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

            <?php if ($recentLeads->isEmpty()): ?>
                <!-- Empty State: pipeline hasn't surfaced any reviewed leads yet -->
                <div class="bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-800 rounded-xl p-8 text-center">
                    <svg class="h-8 w-8 text-gray-300 dark:text-gray-700 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300">No Active Leads Yet</h4>
                    <p class="text-xs text-gray-400 dark:text-gray-500 max-w-sm mx-auto mt-1">
                        The extraction pipeline is running in the background. New requests appear here once they've been reviewed and marked active.
                    </p>
                </div>
            <?php else: ?>
                <?php foreach ($recentLeads as $lead): ?>
                    <?php
                    $badge = LeadsController::requestTypeBadge($lead);
                    $intent = LeadsController::intentBadge($lead);
                    $postedAt = $lead->posted_at ?? $lead->scraped_at;
                    ?>
                    <!-- Lead Entry Item Box -->
                    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 hover:border-primary-500/50 transition-all shadow-sm group">
                        <div class="flex items-start justify-between gap-4 mb-3">
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $badge['classes'] ?>">
                                    <?= htmlspecialchars($badge['label']) ?>
                                </span>
                                <h4 class="text-base font-bold text-gray-900 dark:text-white mt-2 group-hover:text-primary-600 transition-colors">
                                    <?= htmlspecialchars(LeadsController::headline($lead)) ?>
                                </h4>
                            </div>
                            <span class="text-xs font-medium text-gray-400 dark:text-gray-500 whitespace-nowrap"><?= htmlspecialchars($postedAt->diffForHumans()) ?></span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 my-4 text-sm border-t border-b border-gray-100 dark:border-gray-800/80 py-3">
                            <div>
                                <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Target Location</span>
                                <span class="font-medium text-gray-700 dark:text-gray-300"><?= htmlspecialchars(LeadsController::locationLabel($lead)) ?></span>
                            </div>
                            <div>
                                <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Intent Score</span>
                                <span class="font-medium <?= $intent['classes'] ?>"><?= htmlspecialchars($intent['label']) ?></span>
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Origin Source</span>
                                <span class="font-medium text-gray-500 dark:text-gray-400"><?= htmlspecialchars($lead->source->name ?? 'Public Request Board') ?></span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-2">
                            <span class="text-xs text-gray-400">
                                <?= $lead->contact_info_raw ? 'Public contact details available.' : 'Contact details unlock with a full record view.' ?>
                            </span>
                            <?php if ($isLoggedIn): ?>
                                <a href="<?= $baseUrl ?>leads/<?= $lead->id ?>" class="inline-flex items-center px-4 py-2 bg-gray-900 hover:bg-gray-800 dark:bg-primary-600 dark:hover:bg-primary-500 text-white font-bold text-xs rounded-lg transition-colors shadow-sm tracking-wide">
                                    View Full Details
                                </a>
                            <?php else: ?>
                                <a href="<?= $baseUrl ?>login" data-login-button class="inline-flex items-center px-4 py-2 bg-gray-900 hover:bg-gray-800 dark:bg-primary-600 dark:hover:bg-primary-500 text-white font-bold text-xs rounded-lg transition-colors shadow-sm tracking-wide">
                                    View Full Details
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
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
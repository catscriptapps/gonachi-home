<?php
// /resources/views/pages/list-rental-property.php

declare(strict_types=1);

/**
 * Gonachi Landlord & Tenant Validation Engine - "List A Property For Rent" Form
 *
 * Mirrors report-landlord.php exactly. Listings are held as pending_review
 * until an admin approves them via /rental-listing-review — see
 * rental-listing-review.php. Submission is pure AJAX (no page reload) — see
 * resources/js/pages/list-rental-property-page.js. Photos upload immediately
 * on selection to rental-listing-photo-upload.php; the main submit only
 * sends the resulting URLs.
 *
 * @var bool $isLoggedIn
 * @var string $baseUrl
 */

use Src\Service\AuthService;

$currentUserId = $isLoggedIn ? AuthService::userId() : null;

$registered = isset($_GET['registered']);
?>
<div class="max-w-3xl mx-auto space-y-6">

    <?php
    $breadcrumbs = [
        ['label' => 'Landlord & Tenant Validation', 'href' => $baseUrl . 'landlord-tenant-validation'],
        ['label' => 'Rental Opportunities', 'href' => $baseUrl . 'rental-opportunities'],
        ['label' => 'List A Property'],
    ];
    $breadcrumbAccent = 'indigo';
    include __DIR__ . '/../components/breadcrumbs.php';
    ?>

    <div>
        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">List A Property For Rent</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Landlords and agents can list an available property here — every listing is reviewed before it appears in Rental Opportunities.</p>
    </div>

    <div id="list-rental-property-message"></div>

    <?php if (!$currentUserId): ?>
        <div class="max-w-lg mx-auto text-center py-20">
            <?php if ($registered): ?>
                <div class="flex items-start gap-3 text-left bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-900/30 rounded-xl p-4 mb-8">
                    <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <div>
                        <h4 class="text-sm font-bold text-emerald-800 dark:text-emerald-300">Account created</h4>
                        <p class="text-xs text-emerald-700 dark:text-emerald-400 mt-0.5">Please sign in below to continue listing a property.</p>
                    </div>
                </div>
            <?php endif; ?>
            <svg class="h-10 w-10 text-gray-300 dark:text-gray-700 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Sign In To List A Property</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Listings are tied to your account so renters can trust who they're dealing with.</p>
            <div class="mt-6 flex items-center justify-center gap-3">
                <a href="<?= $baseUrl ?>login" data-login-button class="inline-flex items-center px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-bold rounded-lg transition-all shadow-sm">
                    Sign In
                </a>
                <button type="button" class="register-btn inline-flex items-center px-5 py-2 border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300 text-sm font-bold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/60 transition-all">
                    Create Account
                </button>
            </div>
        </div>
    <?php else: ?>

        <form id="list-rental-property-form" novalidate class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 sm:p-8 shadow-sm space-y-6">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="sm:col-span-2">
                    <label for="rental-listing-address" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Property Address</label>
                    <input type="text" id="rental-listing-address" name="address" required placeholder="e.g. House 14, Admiralty Way, Lekki" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-900 dark:text-white" />
                </div>

                <div>
                    <label for="rental-listing-landlord-name" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Landlord / Agent Name</label>
                    <input type="text" id="rental-listing-landlord-name" name="landlord_name" required placeholder="e.g. Mr X" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-900 dark:text-white" />
                </div>

                <div>
                    <label for="rental-listing-area" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Area</label>
                    <select id="rental-listing-area" name="area" required class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-700 dark:text-gray-300">
                        <option value="">Select an area&hellip;</option>
                        <option value="Lekki">Lekki</option>
                        <option value="Victoria Island">Victoria Island</option>
                        <option value="Ikoyi">Ikoyi</option>
                        <option value="Yaba">Yaba</option>
                        <option value="Ikeja">Ikeja</option>
                        <option value="Surulere">Surulere</option>
                        <option value="Ajah">Ajah</option>
                        <option value="Gbagada">Gbagada</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div>
                    <label for="rental-listing-property-type" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Property Type</label>
                    <select id="rental-listing-property-type" name="property_type" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-700 dark:text-gray-300">
                        <option value="">Select property type&hellip;</option>
                        <option value="flat">Flat / Apartment</option>
                        <option value="duplex">Duplex</option>
                        <option value="bungalow">Bungalow</option>
                        <option value="self-contain">Self Contain / Mini Flat</option>
                        <option value="commercial">Commercial Space</option>
                    </select>
                </div>

                <div>
                    <label for="rental-listing-bedrooms" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Bedrooms</label>
                    <input type="number" id="rental-listing-bedrooms" name="bedrooms" min="0" max="20" placeholder="e.g. 3" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-900 dark:text-white" />
                </div>

                <div>
                    <label for="rental-listing-rent-amount" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Rent Amount (&#8358;)</label>
                    <input type="number" id="rental-listing-rent-amount" name="rent_amount" required min="0" step="1000" placeholder="e.g. 2500000" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-900 dark:text-white" />
                </div>

                <div>
                    <label for="rental-listing-rent-period" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Per</label>
                    <select id="rental-listing-rent-period" name="rent_period" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-700 dark:text-gray-300">
                        <option value="year">Year</option>
                        <option value="month">Month</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label for="rental-listing-description" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Description</label>
                    <textarea id="rental-listing-description" name="description" rows="4" placeholder="Describe the property, amenities, and any conditions&hellip;" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-900 dark:text-white resize-none"></textarea>
                </div>
            </div>

            <div class="border-t border-gray-100 dark:border-gray-800 pt-6">
                <!-- Property Pictures — routed through the shared upload modal (client-side compression) -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Property Pictures <span class="normal-case font-medium text-gray-400">(up to 6 pictures)</span></label>
                    <button type="button" id="add-listing-pictures-btn" class="w-full flex flex-col items-center justify-center gap-2 border border-dashed border-gray-300 dark:border-gray-700 rounded-xl p-5 text-sm text-gray-500 dark:text-gray-400 hover:border-indigo-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        <span>Add Property Pictures</span>
                    </button>
                    <div id="listing-pictures-preview" class="grid grid-cols-3 sm:grid-cols-4 gap-3 mt-3 empty:mt-0"></div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2">
                <span class="text-xs text-gray-400">Listings are reviewed before appearing in Rental Opportunities.</span>
                <button type="submit" id="list-rental-property-submit" class="inline-flex items-center px-6 py-2.5 bg-gray-900 hover:bg-gray-800 dark:bg-indigo-600 dark:hover:bg-indigo-500 text-white font-bold text-sm rounded-lg transition-colors shadow-sm whitespace-nowrap">
                    Submit Listing
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>

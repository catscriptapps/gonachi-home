<?php
// /resources/views/pages/report-tenant.php

declare(strict_types=1);

/**
 * Gonachi Landlord & Tenant Validation Engine - Report A Tenant Form
 *
 * Mirror-image of report-landlord.php: landlords report problematic tenants
 * instead of tenants reporting landlords (see Src\Controller\
 * TenantDirectoryController). Reports are held as pending_review until an
 * admin approves them via /tenant-report-review — see
 * tenant-report-review.php. No photo/document upload here — the PDF's
 * Tenant Profile field list has no image attachments, unlike the Landlord
 * Profile's Building Images/Ownership Documents.
 *
 * Submission is pure AJAX (no page reload/redirect) — see
 * resources/js/pages/report-tenant-page.js.
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
        ['label' => 'Report A Tenant'],
    ];
    $breadcrumbAccent = 'indigo';
    include __DIR__ . '/../components/breadcrumbs.php';
    ?>

    <div>
        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Report A Tenant</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Share your experience as a landlord to help other property owners — every contribution strengthens the tenant's verification confidence score.</p>
    </div>

    <div id="report-tenant-message"></div>

    <?php if (!$currentUserId): ?>
        <div class="max-w-lg mx-auto text-center py-20">
            <?php if ($registered): ?>
                <div class="flex items-start gap-3 text-left bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-900/30 rounded-xl p-4 mb-8">
                    <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <div>
                        <h4 class="text-sm font-bold text-emerald-800 dark:text-emerald-300">Account created</h4>
                        <p class="text-xs text-emerald-700 dark:text-emerald-400 mt-0.5">Please sign in below to continue reporting a tenant.</p>
                    </div>
                </div>
            <?php endif; ?>
            <svg class="h-10 w-10 text-gray-300 dark:text-gray-700 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Sign In To Report A Tenant</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Reports are tied to your account so we can detect corroboration from independent landlords.</p>
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

        <form id="report-tenant-form" novalidate class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 sm:p-8 shadow-sm space-y-6">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="report-tenant-name" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Tenant Name</label>
                    <input type="text" id="report-tenant-name" name="tenant_name" required placeholder="e.g. Jane Doe" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-900 dark:text-white" />
                </div>

                <div>
                    <label for="report-tenant-address" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Property Address <span class="normal-case font-medium text-gray-400">(optional)</span></label>
                    <input type="text" id="report-tenant-address" name="property_address" placeholder="e.g. House 14, Admiralty Way, Lekki" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-900 dark:text-white" />
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">How Would You Rate This Tenant?</label>
                    <div class="flex items-center gap-1" id="tenant-rating-picker">
                        <?php for ($star = 1; $star <= 5; $star++): ?>
                            <button type="button" data-star="<?= $star ?>" class="star-btn text-gray-300 dark:text-gray-600 hover:text-amber-400 transition-colors">
                                <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 00-.363 1.118l1.287 3.957c.3.922-.755 1.688-1.539 1.118L10.6 15.63a1 1 0 00-1.176 0l-3.367 2.446c-.784.57-1.838-.196-1.539-1.118l1.287-3.957a1 1 0 00-.363-1.118L2.075 9.436c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 00.95-.69l1.273-3.958z"/></svg>
                            </button>
                        <?php endfor; ?>
                        <input type="hidden" id="tenant-rating-value" name="rating" value="0">
                    </div>
                    <p id="tenant-rating-error" class="hidden text-xs text-red-500 mt-1">Please select a star rating.</p>
                    <p class="text-xs text-gray-400 mt-1">1 star = terrible tenant, 5 stars = excellent tenant.</p>
                </div>

                <div>
                    <label for="report-tenant-duration" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Duration Of Tenancy</label>
                    <input type="text" id="report-tenant-duration" name="duration_of_tenancy" placeholder="e.g. 2 years" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-900 dark:text-white" />
                </div>

                <div>
                    <label for="report-tenant-conduct-type" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Conduct Type</label>
                    <select id="report-tenant-conduct-type" name="conduct_type" required class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-700 dark:text-gray-300">
                        <option value="">Select an issue&hellip;</option>
                        <option value="payment_default">Late / Defaulted Rent Payment</option>
                        <option value="property_damage">Property Damage</option>
                        <option value="lease_violation">Lease Violation</option>
                        <option value="noise_complaints">Noise Complaints</option>
                        <option value="abandoned_property">Abandoned Property</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div>
                    <label for="report-tenant-reference-name" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Reference Name <span class="normal-case font-medium text-gray-400">(optional)</span></label>
                    <input type="text" id="report-tenant-reference-name" name="reference_name" placeholder="e.g. Previous landlord's name" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-900 dark:text-white" />
                </div>

                <div>
                    <label for="report-tenant-reference-phone" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Reference Phone Number <span class="normal-case font-medium text-gray-400">(optional, if known)</span></label>
                    <input type="tel" id="report-tenant-reference-phone" name="reference_phone" placeholder="e.g. 08011111111" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-900 dark:text-white" />
                    <p class="text-xs text-gray-400 mt-1">Helps future landlords verify this tenant directly — kept private until another landlord spends a credit to reveal it.</p>
                </div>

                <div class="sm:col-span-2">
                    <label for="report-tenant-notes" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Additional Notes</label>
                    <textarea id="report-tenant-notes" name="notes" rows="4" placeholder="Describe what happened&hellip;" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-900 dark:text-white resize-none"></textarea>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2">
                <span class="text-xs text-gray-400">Reports are reviewed before appearing on a tenant's public record.</span>
                <button type="submit" id="report-tenant-submit" class="inline-flex items-center px-6 py-2.5 bg-gray-900 hover:bg-gray-800 dark:bg-indigo-600 dark:hover:bg-indigo-500 text-white font-bold text-sm rounded-lg transition-colors shadow-sm whitespace-nowrap">
                    Submit Report
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>

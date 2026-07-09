<?php
// /resources/views/pages/report-landlord.php

declare(strict_types=1);

/**
 * Gonachi Landlord & Tenant Validation Engine - Contribution Form
 *
 * The report-a-landlord loop that feeds the confidence engine (see
 * Src\Controller\LandlordDirectoryController). Reports are held as
 * pending_review until an admin approves them via /landlord-report-review —
 * see landlord-report-review.php.
 *
 * @var bool $isLoggedIn
 * @var string $baseUrl
 */

use Src\Service\AuthService;

$currentUserId = $isLoggedIn ? AuthService::userId() : null;

$submitted = isset($_GET['submitted']);
$error = $_GET['error'] ?? null;
$registered = isset($_GET['registered']);
$signupRedirect = ltrim($path ?? '/report-landlord', '/');
?>
<div class="max-w-3xl mx-auto space-y-6">

    <div>
        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Report A Landlord</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Share your experience to help the next renter — every contribution strengthens the verification confidence score.</p>
    </div>

    <?php if ($submitted): ?>
        <div class="flex items-start gap-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-900/30 rounded-xl p-4">
            <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <div>
                <h4 class="text-sm font-bold text-emerald-800 dark:text-emerald-300">Report submitted</h4>
                <p class="text-xs text-emerald-700 dark:text-emerald-400 mt-0.5">Thank you — it's in the review queue and will appear on the landlord's public record once approved.</p>
            </div>
        </div>
    <?php elseif ($error): ?>
        <div class="flex items-start gap-3 bg-red-50 dark:bg-red-950/40 border border-red-100 dark:border-red-900/30 rounded-xl p-4">
            <svg class="h-5 w-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M4.93 4.93a10 10 0 0114.14 0 10 10 0 010 14.14 10 10 0 01-14.14 0 10 10 0 010-14.14z" /></svg>
            <div>
                <h4 class="text-sm font-bold text-red-800 dark:text-red-300">Couldn't submit that report</h4>
                <p class="text-xs text-red-700 dark:text-red-400 mt-0.5"><?= htmlspecialchars($error) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!$currentUserId): ?>
        <div class="max-w-lg mx-auto text-center py-20">
            <?php if ($registered): ?>
                <div class="flex items-start gap-3 text-left bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-900/30 rounded-xl p-4 mb-8">
                    <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <div>
                        <h4 class="text-sm font-bold text-emerald-800 dark:text-emerald-300">Account created</h4>
                        <p class="text-xs text-emerald-700 dark:text-emerald-400 mt-0.5">Please sign in below to continue reporting a landlord.</p>
                    </div>
                </div>
            <?php endif; ?>
            <svg class="h-10 w-10 text-gray-300 dark:text-gray-700 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Sign In To Report A Landlord</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Reports are tied to your account so we can detect corroboration from independent renters.</p>
            <div class="mt-6 flex items-center justify-center gap-3">
                <a href="<?= $baseUrl ?>login" data-login-button class="inline-flex items-center px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-bold rounded-lg transition-all shadow-sm">
                    Sign In
                </a>
                <a href="<?= $baseUrl ?>signup?redirect=<?= urlencode($signupRedirect) ?>" data-partial class="inline-flex items-center px-5 py-2 border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300 text-sm font-bold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/60 transition-all">
                    Create Account
                </a>
            </div>
        </div>
    <?php else: ?>

        <form id="report-landlord-form" method="POST" action="<?= $baseUrl ?>api/report-landlord" enctype="multipart/form-data" novalidate class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 sm:p-8 shadow-sm space-y-6">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="sm:col-span-2">
                    <label for="report-landlord-address" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Property Address</label>
                    <input type="text" id="report-landlord-address" name="address" required placeholder="e.g. House 14, Admiralty Way, Lekki" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-900 dark:text-white" />
                </div>

                <div>
                    <label for="report-landlord-name" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Landlord Name</label>
                    <input type="text" id="report-landlord-name" name="landlord_name" required placeholder="e.g. Mr X" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-900 dark:text-white" />
                </div>

                <div>
                    <label for="report-landlord-property-type" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Property Type</label>
                    <select id="report-landlord-property-type" name="property_type" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-700 dark:text-gray-300">
                        <option value="">Select property type&hellip;</option>
                        <option value="flat">Flat / Apartment</option>
                        <option value="duplex">Duplex</option>
                        <option value="bungalow">Bungalow</option>
                        <option value="self-contain">Self Contain / Mini Flat</option>
                        <option value="commercial">Commercial Space</option>
                    </select>
                </div>

                <div>
                    <label for="report-landlord-duration" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Duration Of Tenancy</label>
                    <input type="text" id="report-landlord-duration" name="duration_of_tenancy" placeholder="e.g. 2 years" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-900 dark:text-white" />
                </div>

                <div>
                    <label for="report-landlord-issue-type" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Issue Type</label>
                    <select id="report-landlord-issue-type" name="issue_type" required class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-700 dark:text-gray-300">
                        <option value="">Select an issue&hellip;</option>
                        <option value="deposit">Withheld Deposit</option>
                        <option value="harassment">Harassment</option>
                        <option value="unsafe">Unsafe / Uninhabitable Conditions</option>
                        <option value="eviction">Illegal Eviction</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label for="report-landlord-notes" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Additional Notes</label>
                    <textarea id="report-landlord-notes" name="notes" rows="4" placeholder="Describe what happened&hellip;" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-900 dark:text-white resize-none"></textarea>
                </div>
            </div>

            <div class="border-t border-gray-100 dark:border-gray-800 pt-6">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Optional Documents</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="flex flex-col items-center justify-center gap-2 border border-dashed border-gray-300 dark:border-gray-700 rounded-xl p-5 text-sm text-gray-500 dark:text-gray-400 cursor-pointer hover:border-indigo-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        <span>Building Pictures</span>
                        <input type="file" name="building_pictures[]" accept="image/*" multiple class="hidden" onchange="this.previousElementSibling.textContent = this.files.length ? this.files.length + ' file(s) selected' : 'Building Pictures'" />
                    </label>
                    <label class="flex flex-col items-center justify-center gap-2 border border-dashed border-gray-300 dark:border-gray-700 rounded-xl p-5 text-sm text-gray-500 dark:text-gray-400 cursor-pointer hover:border-indigo-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        <span>Supporting Evidence</span>
                        <input type="file" name="supporting_evidence[]" accept="image/*" multiple class="hidden" onchange="this.previousElementSibling.textContent = this.files.length ? this.files.length + ' file(s) selected' : 'Supporting Evidence'" />
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2">
                <span class="text-xs text-gray-400">Reports are reviewed before appearing on a landlord's public record.</span>
                <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-gray-900 hover:bg-gray-800 dark:bg-indigo-600 dark:hover:bg-indigo-500 text-white font-bold text-sm rounded-lg transition-colors shadow-sm whitespace-nowrap">
                    Submit Report
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>

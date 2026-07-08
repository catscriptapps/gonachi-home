<?php
// /resources/views/pages/report-landlord.php

declare(strict_types=1);

/**
 * Gonachi Landlord & Tenant Validation Engine - Contribution Form
 *
 * UI-first pass matching landlord_and_tenant_validation.pdf's "Contribution
 * Form": the report-a-landlord loop that feeds the confidence engine. No
 * backend yet — submit is disabled, matching the transactions.php precedent
 * for not-yet-wired CTAs.
 */
?>
<div class="max-w-3xl mx-auto space-y-6">

    <div>
        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Report A Landlord</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Share your experience to help the next renter — every contribution strengthens the verification confidence score.</p>
    </div>

    <form class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 sm:p-8 shadow-sm space-y-6">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Property Address</label>
                <input type="text" placeholder="e.g. House 14, Admiralty Way, Lekki" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-900 dark:text-white" />
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Landlord Name</label>
                <input type="text" placeholder="e.g. Mr X" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-900 dark:text-white" />
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Property Type</label>
                <select class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-700 dark:text-gray-300">
                    <option value="">Select property type&hellip;</option>
                    <option value="flat">Flat / Apartment</option>
                    <option value="duplex">Duplex</option>
                    <option value="bungalow">Bungalow</option>
                    <option value="self-contain">Self Contain / Mini Flat</option>
                    <option value="commercial">Commercial Space</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Duration Of Tenancy</label>
                <input type="text" placeholder="e.g. 2 years" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-900 dark:text-white" />
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Issue Type</label>
                <select class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-700 dark:text-gray-300">
                    <option value="">Select an issue&hellip;</option>
                    <option value="deposit">Withheld Deposit</option>
                    <option value="harassment">Harassment</option>
                    <option value="unsafe">Unsafe / Uninhabitable Conditions</option>
                    <option value="eviction">Illegal Eviction</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Additional Notes</label>
                <textarea rows="4" placeholder="Describe what happened&hellip;" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-900 dark:text-white resize-none"></textarea>
            </div>
        </div>

        <div class="border-t border-gray-100 dark:border-gray-800 pt-6">
            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Optional Documents</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label class="flex items-center justify-center gap-2 border border-dashed border-gray-300 dark:border-gray-700 rounded-xl p-5 text-sm text-gray-500 dark:text-gray-400 cursor-not-allowed">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    Building Pictures
                </label>
                <label class="flex items-center justify-center gap-2 border border-dashed border-gray-300 dark:border-gray-700 rounded-xl p-5 text-sm text-gray-500 dark:text-gray-400 cursor-not-allowed">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    Supporting Evidence
                </label>
            </div>
        </div>

        <div class="flex items-center justify-between pt-2">
            <span class="text-xs text-gray-400">Reports are reviewed before appearing on a landlord's public record.</span>
            <button type="submit" disabled title="Coming soon" class="inline-flex items-center px-6 py-2.5 bg-gray-100 dark:bg-gray-800 text-gray-400 font-bold text-sm rounded-lg cursor-not-allowed whitespace-nowrap">
                Submit Report
            </button>
        </div>
    </form>
</div>

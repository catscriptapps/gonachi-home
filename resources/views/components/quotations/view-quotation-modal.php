<?php
// /resources/views/components/quotations/view-quotation-modal.php
//
// Shared across /quotations and /my-quotations. All content is populated
// client-side (resources/js/utils/quotations/view-quotation-modal.js) from
// the clicked card's data-* attributes — no fetch needed to open it, except
// for pictures and (owner-only) the bid Responses list.

$modalDetailOwnerId = 'quote';
$modalDetailOwnerTitle = 'Posted By';
?>
<div id="view-quote-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-modal="true" role="dialog">
    <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity close-quote-modal"></div>

        <div class="inline-block transform overflow-hidden rounded-2xl bg-white dark:bg-gray-900 text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:align-middle border border-gray-200 dark:border-gray-800">

            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800 flex items-start justify-between gap-4">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-11 h-11 rounded-xl bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div class="min-w-0">
                        <h3 id="view-quote-title" class="text-base font-bold text-gray-900 dark:text-white truncate"></h3>
                        <p id="view-quote-trade" class="text-xs text-gray-500 dark:text-gray-400"></p>
                    </div>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    <span id="view-quote-status-badge"></span>
                    <button type="button" class="close-quote-modal text-gray-400 hover:text-gray-500 transition-colors">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>

            <div class="px-6 py-5 space-y-5 max-h-[70vh] overflow-y-auto custom-scrollbar">
                <?php include __DIR__ . '/../ui/modal-detail-owner.php'; ?>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600">Project Media</label>
                        <span id="view-quote-pics-count" class="text-[10px] font-bold text-gray-400">0/12</span>
                    </div>
                    <div id="quote-pics-wrapper" class="grid grid-cols-4 gap-2 mb-2"></div>
                    <button type="button" id="quote-add-photo-btn" class="quote-owner-only hidden text-xs font-bold text-teal-600 hover:text-teal-700 flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        Add Photo
                    </button>
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600 mb-1">Work Description</label>
                    <p id="view-quote-description" class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line"></p>
                </div>

                <div class="grid sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600 mb-1">Country</label>
                        <p id="view-quote-country" class="text-sm text-gray-700 dark:text-gray-300"></p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600 mb-1">Region</label>
                        <p id="view-quote-region" class="text-sm text-gray-700 dark:text-gray-300"></p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600 mb-1">City</label>
                        <p id="view-quote-city" class="text-sm text-gray-700 dark:text-gray-300"></p>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600 mb-1">Contractor Type</label>
                        <p id="view-quote-contractor-type" class="text-sm text-gray-700 dark:text-gray-300"></p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600 mb-1">Skilled Trade</label>
                        <p id="view-quote-skilled-trade" class="text-sm text-gray-700 dark:text-gray-300"></p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600 mb-1">Unit Type</label>
                        <p id="view-quote-unit-type" class="text-sm text-gray-700 dark:text-gray-300"></p>
                    </div>
                    <div id="view-quote-house-type-row" class="hidden">
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600 mb-1">House Style</label>
                        <p id="view-quote-house-type" class="text-sm text-gray-700 dark:text-gray-300"></p>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600 mb-1">Timeline</label>
                        <p id="view-quote-timeline" class="text-sm text-gray-700 dark:text-gray-300"></p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600 mb-1">Working Hours</label>
                        <p id="view-quote-hours" class="text-sm text-gray-700 dark:text-gray-300"></p>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600 mb-1">Quotation Type</label>
                        <p id="view-quote-type" class="text-sm text-gray-700 dark:text-gray-300"></p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600 mb-1">Budget</label>
                        <p id="view-quote-budget" class="text-sm text-gray-700 dark:text-gray-300"></p>
                    </div>
                </div>

                <a id="view-quote-video-link" href="#" target="_blank" rel="noopener noreferrer" class="hidden items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-800 hover:border-teal-300 dark:hover:border-teal-800 transition-colors">
                    <span class="text-xs text-gray-500 dark:text-gray-400 truncate">Project Video</span>
                    <span class="text-xs font-bold text-teal-600 flex-shrink-0 ml-3">Watch on YouTube</span>
                </a>

                <div id="view-quote-responses-wrapper" class="quote-owner-only hidden">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600 mb-2">Responses</label>
                    <div id="view-quote-responses-list" class="space-y-2"></div>
                </div>

                <div class="grid grid-cols-3 gap-3 pt-3 border-t border-gray-100 dark:border-gray-800 text-center">
                    <div>
                        <p id="view-quote-created" class="text-xs font-bold text-gray-700 dark:text-gray-300"></p>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wider">Created</p>
                    </div>
                    <div>
                        <p id="view-quote-updated" class="text-xs font-bold text-gray-700 dark:text-gray-300"></p>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wider">Updated</p>
                    </div>
                    <div>
                        <p id="view-quote-views-count" class="text-xs font-bold text-gray-700 dark:text-gray-300"></p>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wider">Views</p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 dark:bg-gray-800/50 px-6 py-4 flex flex-wrap items-center justify-end gap-2">
                <button type="button" id="view-quote-edit-btn" class="quote-owner-only hidden px-4 py-2 text-xs font-bold rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors">Edit Quotation</button>
                <button type="button" class="close-quote-modal px-4 py-2 text-xs font-bold rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">Dismiss</button>
                <button type="button" id="view-quote-primary-btn" class="px-4 py-2 text-xs font-bold rounded-lg bg-teal-600 hover:bg-teal-700 text-white transition-colors">Connect with Owner</button>
            </div>
        </div>
    </div>
</div>

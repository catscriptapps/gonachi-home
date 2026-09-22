<?php
// /resources/views/components/swap-listings/view-swap-listing-modal.php
//
// Shared across /swap, /my-swap-listings, and /saved-swap-listings. All
// content is populated client-side (resources/js/utils/swap-listings/
// view-swap-listing-modal.js) straight from the clicked card's data-*
// attributes — no fetch needed to open it, including the photo gallery
// (the card already carries every photo URL in data-photos for the edit
// modal's prefill) and the video (data-video-url). Mirrors Real Estate
// World's view-quotation-modal.php, minus the fields Swap doesn't have
// (location/contractor-type/timeline/budget) and the Responses section
// (Swap has no inquiry/bid system).

$modalDetailOwnerId = 'swap';
$modalDetailOwnerTitle = 'Posted By';
?>
<div id="view-swap-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-modal="true" role="dialog">
    <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity close-swap-modal"></div>

        <div class="inline-block transform overflow-hidden rounded-2xl bg-white dark:bg-gray-900 text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:align-middle border border-gray-200 dark:border-gray-800">

            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800 flex items-start justify-between gap-4">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-11 h-11 rounded-xl bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                    </div>
                    <div class="min-w-0">
                        <h3 id="view-swap-title" class="text-base font-bold text-gray-900 dark:text-white truncate"></h3>
                        <p id="view-swap-subtitle" class="text-xs text-gray-500 dark:text-gray-400"></p>
                    </div>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    <span id="view-swap-status-badge"></span>
                    <button type="button" class="close-swap-modal text-gray-400 hover:text-gray-500 transition-colors">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>

            <div class="px-6 py-5 space-y-5 max-h-[70vh] overflow-y-auto custom-scrollbar">
                <?php include __DIR__ . '/../ui/modal-detail-owner.php'; ?>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-purple-600">Photos</label>
                        <span id="view-swap-pics-count" class="text-[10px] font-bold text-gray-400">0</span>
                    </div>
                    <div id="swap-pics-wrapper" class="grid grid-cols-4 gap-2"></div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-purple-600">Video</label>
                        <span class="text-[10px] font-bold text-gray-400">Max 1</span>
                    </div>
                    <div id="swap-video-wrapper" class="mb-2"></div>
                    <button type="button" id="swap-add-video-btn" class="hidden text-xs font-bold text-purple-600 hover:text-purple-700 items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                        Add Video
                    </button>
                    <button type="button" id="swap-remove-video-btn" class="hidden text-xs font-bold text-red-500 hover:text-red-600 items-center gap-1.5 mt-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        Remove Video
                    </button>
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-purple-600 mb-1">Description</label>
                    <p id="view-swap-description" class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line"></p>
                </div>

                <div class="grid sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-purple-600 mb-1">Category</label>
                        <p id="view-swap-category" class="text-sm text-gray-700 dark:text-gray-300"></p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-purple-600 mb-1">Condition</label>
                        <p id="view-swap-condition" class="text-sm text-gray-700 dark:text-gray-300"></p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-purple-600 mb-1">Location</label>
                        <p id="view-swap-city" class="text-sm text-gray-700 dark:text-gray-300"></p>
                    </div>
                </div>

                <div id="view-swap-price-row" class="hidden">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-purple-600 mb-1">Price</label>
                    <p id="view-swap-price" class="text-sm text-gray-700 dark:text-gray-300"></p>
                </div>

                <div id="view-swap-trade-pref-row" class="hidden">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-purple-600 mb-1">Looking For</label>
                    <p id="view-swap-trade-pref" class="text-sm text-gray-700 dark:text-gray-300"></p>
                </div>

                <div class="grid grid-cols-3 gap-3 pt-3 border-t border-gray-100 dark:border-gray-800 text-center">
                    <div>
                        <p id="view-swap-created" class="text-xs font-bold text-gray-700 dark:text-gray-300"></p>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wider">Created</p>
                    </div>
                    <div>
                        <p id="view-swap-updated" class="text-xs font-bold text-gray-700 dark:text-gray-300"></p>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wider">Updated</p>
                    </div>
                    <div>
                        <p id="view-swap-views-count" class="text-xs font-bold text-gray-700 dark:text-gray-300"></p>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wider">Views</p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 dark:bg-gray-800/50 px-6 py-4 flex flex-wrap items-center justify-end gap-2">
                <button type="button" id="view-swap-edit-btn" class="swap-owner-only hidden px-4 py-2 text-xs font-bold rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors">Edit Listing</button>
                <button type="button" class="close-swap-modal px-4 py-2 text-xs font-bold rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">Dismiss</button>
                <button type="button" id="view-swap-primary-btn" class="px-4 py-2 text-xs font-bold rounded-lg bg-purple-600 hover:bg-purple-700 text-white transition-colors"></button>
            </div>
        </div>
    </div>
</div>

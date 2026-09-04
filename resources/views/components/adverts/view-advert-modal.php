<?php
// /resources/views/components/adverts/view-advert-modal.php
//
// Shared across /adverts, /my-adverts, and /adverts-admin. All content is
// populated client-side (resources/js/utils/adverts/view-advert-modal.js)
// from the clicked card/row's data-* attributes — no fetch needed to open
// it, matching the legacy platform's view-advert modal.

$modalDetailOwnerId = 'ad';
$modalDetailOwnerTitle = 'Posted By';
?>
<div id="view-ad-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-modal="true" role="dialog">
    <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity close-ad-modal"></div>

        <div class="inline-block transform overflow-hidden rounded-2xl bg-white dark:bg-gray-900 text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:align-middle border border-gray-200 dark:border-gray-800">

            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800 flex items-start justify-between gap-4">
                <div class="flex items-center gap-3 min-w-0">
                    <div id="view-ad-package-icon" class="w-11 h-11 rounded-xl bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path id="view-ad-package-icon-path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d=""/></svg>
                    </div>
                    <div class="min-w-0">
                        <h3 id="view-ad-title" class="text-base font-bold text-gray-900 dark:text-white truncate"></h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400"><span id="view-ad-package-name"></span> &middot; <span id="view-ad-package-description"></span></p>
                    </div>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    <span id="view-ad-status-badge"></span>
                    <button type="button" class="close-ad-modal text-gray-400 hover:text-gray-500 transition-colors">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>

            <div class="px-6 py-5 space-y-5 max-h-[70vh] overflow-y-auto custom-scrollbar">
                <?php include __DIR__ . '/../ui/modal-detail-owner.php'; ?>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600">Media Assets</label>
                        <span id="view-ad-pics-count" class="text-[10px] font-bold text-gray-400">0/12</span>
                    </div>
                    <div id="ad-pics-wrapper" class="grid grid-cols-4 gap-2 mb-2"></div>
                    <button type="button" id="ad-add-photo-btn" class="ad-owner-only hidden text-xs font-bold text-teal-600 hover:text-teal-700 flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        Add Photo
                    </button>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600">Video</label>
                        <span class="text-[10px] font-bold text-gray-400">Max 1</span>
                    </div>
                    <div id="ad-video-wrapper" class="mb-2"></div>
                    <button type="button" id="ad-add-video-btn" class="hidden text-xs font-bold text-teal-600 hover:text-teal-700 items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                        Add Video
                    </button>
                    <button type="button" id="ad-remove-video-btn" class="hidden text-xs font-bold text-red-500 hover:text-red-600 items-center gap-1.5 mt-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        Remove Video
                    </button>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600 mb-1">Advert Content</label>
                        <p id="view-ad-description" class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line"></p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600 mb-1">Search Keywords</label>
                        <p id="view-ad-keywords" class="text-sm text-gray-500 dark:text-gray-400"></p>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600 mb-1">Target Countries</label>
                        <div id="view-ad-countries" class="flex flex-wrap gap-1.5"></div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600 mb-1">Audience Types</label>
                        <div id="view-ad-user-types" class="flex flex-wrap gap-1.5"></div>
                    </div>
                </div>

                <a id="view-ad-landing-link" href="#" target="_blank" rel="noopener noreferrer" class="hidden items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-800 hover:border-teal-300 dark:hover:border-teal-800 transition-colors">
                    <span id="view-ad-landing-url" class="text-xs text-gray-500 dark:text-gray-400 truncate"></span>
                    <span id="view-ad-cta-label" class="text-xs font-bold text-teal-600 flex-shrink-0 ml-3"></span>
                </a>

                <div class="grid grid-cols-3 gap-3 pt-3 border-t border-gray-100 dark:border-gray-800 text-center">
                    <div>
                        <p id="view-ad-created" class="text-xs font-bold text-gray-700 dark:text-gray-300"></p>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wider">Created</p>
                    </div>
                    <div>
                        <p id="view-ad-updated" class="text-xs font-bold text-gray-700 dark:text-gray-300"></p>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wider">Updated</p>
                    </div>
                    <div>
                        <p id="view-ad-views-count" class="text-xs font-bold text-gray-700 dark:text-gray-300"></p>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wider">Views</p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 dark:bg-gray-800/50 px-6 py-4 flex flex-wrap items-center justify-end gap-2">
                <div id="view-ad-admin-actions" class="hidden flex-1 flex flex-wrap items-center gap-2">
                    <button type="button" id="admin-approve-ad-btn" class="px-4 py-2 text-xs font-bold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white transition-colors">Approve</button>
                    <button type="button" id="admin-deactivate-ad-btn" class="px-4 py-2 text-xs font-bold rounded-lg bg-gray-500 hover:bg-gray-600 text-white transition-colors">Deactivate</button>
                    <button type="button" id="admin-reject-ad-btn" class="px-4 py-2 text-xs font-bold rounded-lg bg-red-600 hover:bg-red-700 text-white transition-colors">Reject</button>
                </div>
                <button type="button" id="view-ad-edit-btn" class="ad-owner-only hidden px-4 py-2 text-xs font-bold rounded-lg bg-teal-600 hover:bg-teal-700 text-white transition-colors">Edit Ad</button>
                <button type="button" class="close-ad-modal px-4 py-2 text-xs font-bold rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">Dismiss</button>
            </div>
        </div>
    </div>
</div>

<?php
// /resources/views/components/listings/view-listing-modal.php
//
// Shared across /listings and /my-listings. All content is populated
// client-side (resources/js/utils/listings/view-listing-modal.js) from the
// clicked card's data-* attributes — no fetch needed to open it, except for
// pictures and (owner-only) the Inquiries list. Property-specific sections
// (location/details/amenities/availability) are hidden entirely for
// "service" listings (category 2/3), matching legacy's isService split.

$modalDetailOwnerId = 'listing';
$modalDetailOwnerTitle = 'Listing Owner';
?>
<div id="view-listing-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-modal="true" role="dialog">
    <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity close-listing-modal"></div>

        <div class="inline-block transform overflow-hidden rounded-2xl bg-white dark:bg-gray-900 text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:align-middle border border-gray-200 dark:border-gray-800">

            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800 flex items-start justify-between gap-4">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-11 h-11 rounded-xl bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                    </div>
                    <div class="min-w-0">
                        <h3 id="view-listing-title" class="text-base font-bold text-gray-900 dark:text-white truncate"></h3>
                        <p id="view-listing-category-sub" class="text-xs text-gray-500 dark:text-gray-400"></p>
                    </div>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    <span id="view-listing-status-badge"></span>
                    <button type="button" class="close-listing-modal text-gray-400 hover:text-gray-500 transition-colors">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>

            <button type="button" id="view-listing-inquiries-banner" class="hidden w-full text-left px-6 py-3 bg-teal-50 dark:bg-teal-950/30 border-b border-teal-100 dark:border-teal-900/40 text-teal-700 dark:text-teal-400 text-xs font-bold flex items-center justify-between gap-2 hover:bg-teal-100 dark:hover:bg-teal-950/50 transition-colors">
                <span id="view-listing-inquiries-banner-text"></span>
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
            </button>

            <div class="px-6 py-5 space-y-5 max-h-[70vh] overflow-y-auto custom-scrollbar">
                <?php include __DIR__ . '/../ui/modal-detail-owner.php'; ?>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600">Listing Photos</label>
                        <span id="view-listing-pics-count" class="text-[10px] font-bold text-gray-400">0/12</span>
                    </div>
                    <div id="listing-pics-wrapper" class="grid grid-cols-4 gap-2 mb-2"></div>
                    <button type="button" id="listing-add-photo-btn" class="listing-owner-only hidden text-xs font-bold text-teal-600 hover:text-teal-700 flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                        Add Photo
                    </button>
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600 mb-1">Description</label>
                    <p id="view-listing-description" class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line"></p>
                </div>

                <div id="listing-section-location" class="grid sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600 mb-1">Street Address</label>
                        <p id="view-listing-address" class="text-sm text-gray-700 dark:text-gray-300"></p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600 mb-1">Region</label>
                        <p id="view-listing-region" class="text-sm text-gray-700 dark:text-gray-300"></p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600 mb-1">Country</label>
                        <p id="view-listing-country" class="text-sm text-gray-700 dark:text-gray-300"></p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600 mb-1">City</label>
                        <p id="view-listing-city" class="text-sm text-gray-700 dark:text-gray-300"></p>
                    </div>
                </div>

                <div id="listing-section-details" class="bg-gray-50/50 dark:bg-gray-800/20 p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600 mb-3">Property Details</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Unit Type</p>
                            <p id="view-listing-unit-type" class="text-sm font-bold text-gray-900 dark:text-white"></p>
                        </div>
                        <div id="view-listing-house-type-row">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">House Style</p>
                            <p id="view-listing-house-type" class="text-sm font-bold text-gray-900 dark:text-white"></p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Bedrooms</p>
                            <p id="view-listing-bedrooms" class="text-sm font-bold text-gray-900 dark:text-white"></p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Bathrooms</p>
                            <p id="view-listing-bathrooms" class="text-sm font-bold text-gray-900 dark:text-white"></p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Property Size</p>
                            <p id="view-listing-size" class="text-sm font-bold text-gray-900 dark:text-white"></p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-1">Parking</p>
                            <p id="view-listing-parking" class="text-sm font-bold text-gray-900 dark:text-white"></p>
                        </div>
                    </div>
                </div>

                <div id="listing-section-amenities">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600 mb-2">Included Amenities</label>
                    <div id="view-listing-amenities-container" class="flex flex-wrap gap-1.5">
                        <p class="text-xs text-gray-400 italic">No specific amenities listed.</p>
                    </div>
                </div>

                <div id="listing-section-availability" class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600 mb-2">Availability & Features</label>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-1.5">
                                <span class="text-xs text-gray-500">Air Conditioning</span>
                                <span id="view-listing-is-ac" class="text-xs font-bold text-gray-900 dark:text-white"></span>
                            </div>
                            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-1.5">
                                <span class="text-xs text-gray-500">Furnished</span>
                                <span id="view-listing-is-furnished" class="text-xs font-bold text-gray-900 dark:text-white"></span>
                            </div>
                            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-1.5">
                                <span class="text-xs text-gray-500">Pets Allowed</span>
                                <span id="view-listing-pets" class="text-xs font-bold text-gray-900 dark:text-white"></span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600 mb-2">Financials</label>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-1.5">
                                <span class="text-xs text-gray-500">Price / Rent</span>
                                <span id="view-listing-price" class="text-xs font-black text-teal-600"></span>
                            </div>
                            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-1.5">
                                <span class="text-xs text-gray-500">Agreement Type</span>
                                <span id="view-listing-agreement" class="text-xs font-bold text-gray-900 dark:text-white"></span>
                            </div>
                            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-1.5">
                                <span class="text-xs text-gray-500">Move-in Date</span>
                                <span id="view-listing-move-in" class="text-xs font-bold text-gray-900 dark:text-white"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <a id="view-listing-video-link" href="#" target="_blank" rel="noopener noreferrer" class="hidden items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-800 hover:border-teal-300 dark:hover:border-teal-800 transition-colors">
                    <span class="text-xs text-gray-500 dark:text-gray-400 truncate">Property Video</span>
                    <span class="text-xs font-bold text-teal-600 flex-shrink-0 ml-3">Watch on YouTube</span>
                </a>

                <div class="flex items-center justify-between p-3 rounded-xl border border-gray-100 dark:border-gray-800">
                    <span class="text-xs text-gray-500 dark:text-gray-400">Contact Phone</span>
                    <span id="view-listing-phone" class="text-xs font-bold text-gray-900 dark:text-white">No phone provided</span>
                </div>

                <div id="view-listing-inquiries-wrapper" class="listing-owner-only hidden">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600 mb-2">Inquiries</label>
                    <div id="view-listing-inquiries-list" class="space-y-2"></div>
                </div>

                <div class="grid grid-cols-3 gap-3 pt-3 border-t border-gray-100 dark:border-gray-800 text-center">
                    <div>
                        <p id="view-listing-created" class="text-xs font-bold text-gray-700 dark:text-gray-300"></p>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wider">Created</p>
                    </div>
                    <div>
                        <p id="view-listing-updated" class="text-xs font-bold text-gray-700 dark:text-gray-300"></p>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wider">Updated</p>
                    </div>
                    <div>
                        <p id="view-listing-views-count" class="text-xs font-bold text-gray-700 dark:text-gray-300"></p>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wider">Views</p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 dark:bg-gray-800/50 px-6 py-4 flex flex-wrap items-center justify-end gap-2">
                <button type="button" id="view-listing-edit-btn" class="listing-owner-only hidden px-4 py-2 text-xs font-bold rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors">Edit Listing</button>
                <button type="button" class="close-listing-modal px-4 py-2 text-xs font-bold rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">Dismiss</button>
                <button type="button" id="view-listing-primary-btn" class="px-4 py-2 text-xs font-bold rounded-lg bg-teal-600 hover:bg-teal-700 text-white transition-colors">Contact Owner</button>
            </div>
        </div>
    </div>
</div>

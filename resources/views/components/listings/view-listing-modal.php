<?php
// /resources/views/components/listings/view-listing-modal.php
?>

<style>
    /* Custom Stylish Scrollbar for the Modal Content */
    #view-listing-modal-content::-webkit-scrollbar {
        width: 6px;
    }

    #view-listing-modal-content::-webkit-scrollbar-track {
        background: transparent;
        margin: 10px;
    }

    #view-listing-modal-content::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
        border: 2px solid transparent;
    }

    .dark #view-listing-modal-content::-webkit-scrollbar-thumb {
        background: #334155;
    }

    #view-listing-modal-content::-webkit-scrollbar-thumb:hover {
        background: #fc832b;
        /* Primary 500 */
    }
</style>

<div id="view-listing-modal" class="fixed inset-0 z-50 hidden">
    <div id="close-view-listing-modal-overlay" class="absolute inset-0 bg-secondary-955/60 backdrop-blur-sm transition-opacity"></div>

    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-900 w-full max-w-5xl rounded-[2.5rem] shadow-2xl border border-gray-200 dark:border-secondary-900 overflow-hidden transform transition-all animate-in fade-in zoom-in duration-200">

            <!-- Header Section -->
            <div class="px-8 py-6 border-b border-gray-100 dark:border-secondary-900 flex items-center justify-between bg-gray-50/50 dark:bg-secondary-950/50">
                <div class="flex items-center space-x-5 overflow-hidden">
                    <div id="view-listing-icon-container" class="h-14 w-14 flex-shrink-0 rounded-2xl bg-primary-500 flex items-center justify-center text-secondary-950 shadow-lg shadow-primary-500/20">
                        <span id="view-listing-initial" class="text-2xl font-black uppercase">L</span>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-3 mb-1">
                            <h3 class="text-xl font-black text-secondary-900 dark:text-white truncate leading-tight" id="view-listing-title">Listing Title</h3>
                            <span id="view-listing-status" class="inline-flex items-center rounded-full bg-green-50 dark:bg-green-900/20 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-green-600 dark:text-green-400 border border-green-100 dark:border-green-800/30">Active</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5">
                            <p id="view-listing-category-sub" class="text-[10px] text-gray-400 font-black uppercase tracking-[0.2em] leading-none">General Category</p>
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-300 dark:bg-secondary-800 hidden sm:inline-block"></span>
                            <div class="flex items-center gap-1.5">
                                <span id="view-listing-type-badge" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 border border-primary-100 dark:border-primary-900/30 shadow-sm">Standard</span>
                                <span id="view-listing-condition-badge" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-secondary-50 dark:bg-secondary-900/40 text-secondary-600 dark:text-secondary-400 border border-secondary-100 dark:border-secondary-800/30 shadow-sm">Used</span>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" class="close-view-listing-modal text-gray-400 hover:text-primary-500 p-2 transition-colors">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div id="view-listing-modal-content" class="p-8 space-y-10 font-sans overflow-y-auto max-h-[70vh]">

                <?php
                $modalDetailOwnerId = 'listing';
                $modalDetailOwnerTitle = 'Listing Owner';
                include __DIR__ . '/../ui/modal-detail-owner.php';
                ?>

                <!-- Media Section -->
                <div class="space-y-4 px-2">
                    <div class="flex items-center justify-between border-b border-gray-50 dark:border-secondary-900 pb-2">
                        <div class="flex items-center gap-3">
                            <h3 class="text-xs font-black text-primary-500 uppercase tracking-[0.3em] flex items-center gap-2">
                                <span class="w-8 h-[2px] bg-primary-500"></span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Item Media
                            </h3>
                            <span id="listing-pics-count" class="text-[10px] font-bold text-primary-500 bg-primary-50 dark:bg-primary-950/30 px-2 py-0.5 rounded-md border border-primary-100 dark:border-primary-900/50"></span>
                        </div>

                        <button type="button" id="trigger-listing-pic-upload" class="listing-owner-only flex items-center gap-1.5 px-3 py-1.5 bg-primary-50 dark:bg-primary-900/20 text-primary-500 hover:bg-primary-500 hover:text-secondary-950 rounded-lg transition-all group">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            <span class="text-[10px] font-black uppercase tracking-tight">Add Photo</span>
                        </button>
                    </div>

                    <div id="listing-pics-wrapper" class="grid grid-cols-4 sm:grid-cols-6 gap-3">
                        <!-- Dynamic pictures via JS -->
                    </div>
                    <input type="file" id="listing-pic-input" class="hidden" accept="image/*" multiple>
                </div>

                <!-- Location Section -->
                <div class="bg-gray-50/50 dark:bg-secondary-900/10 p-6 rounded-[2rem] border border-gray-100 dark:border-secondary-800">
                    <h3 class="text-xs font-black text-primary-500 uppercase tracking-[0.3em] flex items-center gap-2 mb-6">
                        <span class="w-8 h-[2px] bg-primary-500"></span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Item Location
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Country</p>
                            <p id="view-listing-country" class="text-sm font-bold text-secondary-900 dark:text-white">---</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Region / State</p>
                            <p id="view-listing-region" class="text-sm font-bold text-secondary-900 dark:text-white">---</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">City</p>
                            <p id="view-listing-city" class="text-sm font-bold text-secondary-900 dark:text-white">---</p>
                        </div>
                    </div>
                </div>

                <!-- Description Section -->
                <div class="px-2">
                    <h3 class="text-xs font-black text-primary-500 uppercase tracking-[0.3em] flex items-center gap-2 mb-6">
                        <span class="w-8 h-[2px] bg-primary-500"></span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                        </svg>
                        Item Description
                    </h3>
                    <div class="space-y-4">
                        <div class="bg-white dark:bg-secondary-900/20 p-6 rounded-2xl border border-gray-100 dark:border-secondary-800">
                            <p id="view-listing-description" class="text-sm text-secondary-900 dark:text-gray-300 leading-relaxed whitespace-pre-line">---</p>
                        </div>
                    </div>
                </div>

                <!-- Transaction Intent Section -->
                <div class="bg-secondary-900 dark:bg-black p-8 rounded-[2rem] text-white shadow-xl border border-secondary-800">
                    <h3 class="text-xs font-black text-primary-500 uppercase tracking-[0.3em] flex items-center gap-2 mb-6">
                        <span class="w-8 h-[2px] bg-primary-500"></span>
                        Transaction Intent
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-500 mb-1">Offer Type</p>
                            <p id="view-listing-type" class="text-sm font-bold text-white">---</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-500 mb-1">Item Condition</p>
                            <p id="view-listing-condition" class="text-sm font-bold text-white">---</p>
                        </div>
                        <div id="view-listing-trade-pref-wrapper">
                            <p class="text-[10px] font-black uppercase tracking-widest text-gray-500 mb-1">Trade Preferences</p>
                            <p id="view-listing-trade-pref" class="text-sm font-bold text-primary-500">---</p>
                        </div>
                    </div>
                    <div class="mt-6 pt-6 border-t border-white/5 flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-500">Estimated Value / Price</span>
                        <span id="view-listing-price" class="text-2xl font-black text-white">$0.00</span>
                    </div>
                </div>

                <!-- Contact & Video Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 px-2">
                    <div class="flex items-center space-x-4 p-5 rounded-2xl bg-secondary-950 text-white">
                        <div class="bg-white/10 p-3 rounded-xl">
                            <svg class="w-6 h-6 text-[#FF0000]" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[9px] font-bold text-gray-500 uppercase tracking-widest mb-1">Walkthrough Video</p>
                            <a id="view-listing-url" href="#" target="_blank" class="block text-sm font-bold text-primary-500 truncate hover:text-primary-400">No video</a>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4 p-5 rounded-2xl border border-gray-100 dark:border-secondary-800">
                        <div class="bg-secondary-100 dark:bg-secondary-900 p-3 rounded-xl text-secondary-900 dark:text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-1">Contact Phone</p>
                            <p id="view-listing-phone" class="text-sm font-bold text-secondary-900 dark:text-white">---</p>
                        </div>
                    </div>
                </div>

                <!-- Footer Metadata -->
                <div class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-gray-50 dark:border-secondary-900">
                    <div class="flex gap-8">
                        <div>
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Posted</p><span id="view-listing-created" class="text-[11px] font-bold text-secondary-900 dark:text-white">---</span>
                        </div>
                        <div>
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Views</p><span id="view-listing-views-count" class="text-[11px] font-bold text-secondary-900 dark:text-white">0</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Footer -->
            <div class="px-8 py-5 border-t border-gray-100 dark:border-secondary-900 bg-gray-50/50 dark:bg-secondary-950/50 flex justify-end items-center space-x-4">
                <button type="button" class="close-view-listing-modal px-5 py-3 text-xs font-black text-gray-500 uppercase tracking-widest hover:text-secondary-900 dark:hover:text-white transition-colors">Dismiss</button>
                <button type="button" id="view-listing-edit-btn" class="listing-owner-only px-8 py-3 text-xs font-black text-white bg-primary-500 hover:bg-secondary-950 hover:text-white rounded-xl transition-all active:scale-95 shadow-lg shadow-primary-500/20 flex items-center gap-2 uppercase tracking-widest">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg>
                    Edit Listing
                </button>
                <button type="button" id="view-listing-connect-btn" class="px-8 py-3 text-xs font-black text-secondary-950 bg-primary-500 hover:bg-secondary-950 hover:text-white rounded-xl transition-all active:scale-95 shadow-lg shadow-primary-500/20 flex items-center gap-2 uppercase tracking-widest">
                    Message Swapper
                </button>
            </div>
        </div>
    </div>
</div>
<?php
// /resources/views/components/landlords/view-landlord-modal.php

?>

<div id="view-landlord-modal" class="fixed inset-0 z-50 hidden">
    <div id="close-view-modal-overlay" class="absolute inset-0 bg-navy-900/60 backdrop-blur-sm transition-opacity"></div>

    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-900 w-full max-w-2xl rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden transform transition-all animate-in fade-in zoom-in duration-200">

            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between bg-gray-50/50 dark:bg-gray-800/50">
                <div class="flex items-center space-x-3 overflow-hidden">
                    <div id="view-landlord-logo-container" class="h-12 w-12 flex-shrink-0">
                        <div id="view-landlord-logo-fallback" class="h-full w-full rounded-full bg-primary-600 flex items-center justify-center text-white font-bold text-xl">L</div>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-navy-900 dark:text-white truncate" id="view-landlord-name">Company Name</h3>
                        <p id="view-landlord-email-sub" class="text-xs text-gray-500 dark:text-gray-400 font-medium"></p>
                    </div>
                </div>
                <button type="button" class="close-view-modal text-gray-400 hover:text-navy-600 dark:hover:text-gray-200 p-1 transition-colors">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-8 space-y-8 font-sans">
                <div class="flex justify-between items-center">
                    <div id="view-landlord-status-container">
                        <span id="view-landlord-status" class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest border"></span>
                    </div>
                    <span id="view-landlord-joined" class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-tight"></span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center space-x-4 bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                        <div class="text-primary-600 dark:text-primary-400 shrink-0">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">HQ Operations Center</p>
                            <p id="view-landlord-combined-location" class="text-sm font-bold text-navy-900 dark:text-white truncate"></p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-4 bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                        <div class="text-primary-600 dark:text-primary-400 shrink-0">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tax Identity / Registration</p>
                            <p id="view-landlord-tax-id" class="text-sm font-bold font-mono text-navy-900 dark:text-white truncate">N/A</p>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="text-navy-600 dark:text-navy-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                        </div>
                        <h4 class="text-xs font-bold text-navy-900 dark:text-white uppercase tracking-wider">Corporate Mailing Address</h4>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-5 border border-gray-100 dark:border-gray-800 space-y-1 text-sm text-gray-700 dark:text-gray-300">
                        <p id="view-landlord-address-1" class="font-medium"></p>
                        <p id="view-landlord-address-2" class="font-medium empty:hidden"></p>
                        <p id="view-landlord-address-cityline" class="text-xs text-gray-500 dark:text-gray-400"></p>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50 flex justify-end space-x-3">
                <button type="button" class="close-view-modal px-5 py-2.5 text-sm font-bold text-gray-600 dark:text-gray-400 hover:text-navy-900 dark:hover:text-white transition-colors">
                    Close
                </button>
                <button type="button" id="view-landlord-edit-btn" class="px-6 py-2.5 text-sm font-bold text-white bg-primary-600 hover:bg-primary-700 rounded-xl transition-all active:scale-95 shadow-lg shadow-primary-600/20">
                    Edit Profile
                </button>
            </div>
        </div>
    </div>
</div>
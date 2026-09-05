<?php
// /resources/views/components/mentors/view-mentor-modal.php
//
// Shared across the /mentors directory. All content is populated
// client-side (resources/js/utils/mentors/view-mentor-modal.js) from the
// clicked card's data-* attributes — no fetch needed to open it, except for
// the owner-only Requests list.

$modalDetailOwnerId = 'mentor';
$modalDetailOwnerTitle = 'Expert Profile';
?>
<div id="view-mentor-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-modal="true" role="dialog">
    <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity close-mentor-modal"></div>

        <div class="inline-block transform overflow-hidden rounded-2xl bg-white dark:bg-gray-900 text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:align-middle border border-gray-200 dark:border-gray-800">

            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800 flex items-start justify-between gap-4">
                <div class="flex items-center gap-3 min-w-0">
                    <div id="view-mentor-avatar-container" class="w-11 h-11 rounded-xl bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 flex items-center justify-center flex-shrink-0 overflow-hidden">
                        <img id="view-mentor-avatar" src="" class="w-full h-full object-cover hidden" alt="Mentor">
                        <span id="view-mentor-initial" class="text-lg font-black"></span>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <h3 id="view-mentor-name" class="text-base font-bold text-gray-900 dark:text-white truncate"></h3>
                            <span id="view-mentor-type-badge" class="inline-flex items-center rounded-full bg-teal-50 dark:bg-teal-950/40 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider text-teal-600 dark:text-teal-400 border border-teal-100 dark:border-teal-900/40 flex-shrink-0"></span>
                        </div>
                        <p id="view-mentor-headline" class="text-xs text-gray-500 dark:text-gray-400 truncate"></p>
                    </div>
                </div>
                <button type="button" class="close-mentor-modal text-gray-400 hover:text-gray-500 transition-colors flex-shrink-0">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="px-6 py-5 space-y-5 max-h-[70vh] overflow-y-auto custom-scrollbar">
                <?php include __DIR__ . '/../ui/modal-detail-owner.php'; ?>

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600 mb-1">Professional Bio</label>
                    <p id="view-mentor-bio" class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line"></p>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <a id="view-mentor-youtube-link" href="#" target="_blank" rel="noopener noreferrer" class="hidden items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-800 hover:border-teal-300 dark:hover:border-teal-800 transition-colors">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Introduction Video</span>
                        <span class="text-xs font-bold text-teal-600 flex-shrink-0 ml-3">Watch on YouTube</span>
                    </a>
                    <a id="view-mentor-website-link" href="#" target="_blank" rel="noopener noreferrer" class="hidden items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-800 hover:border-teal-300 dark:hover:border-teal-800 transition-colors">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Professional Website</span>
                        <span class="text-xs font-bold text-teal-600 flex-shrink-0 ml-3">Visit Site</span>
                    </a>
                </div>

                <div class="grid sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600 mb-1">Country</label>
                        <p id="view-mentor-country" class="text-sm text-gray-700 dark:text-gray-300"></p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600 mb-1">Region</label>
                        <p id="view-mentor-region" class="text-sm text-gray-700 dark:text-gray-300"></p>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600 mb-1">City</label>
                        <p id="view-mentor-city" class="text-sm text-gray-700 dark:text-gray-300"></p>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600 mb-2">Areas of Expertise</label>
                    <div id="view-mentor-skills-container" class="flex flex-wrap gap-1.5"></div>
                </div>

                <div id="view-mentor-requests-wrapper" class="mentor-owner-only hidden">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-teal-600 mb-2">Requests</label>
                    <div id="view-mentor-requests-list" class="space-y-2"></div>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-3 border-t border-gray-100 dark:border-gray-800 text-center">
                    <div>
                        <p id="view-mentor-created" class="text-xs font-bold text-gray-700 dark:text-gray-300"></p>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wider">Joined</p>
                    </div>
                    <div>
                        <p id="view-mentor-updated" class="text-xs font-bold text-gray-700 dark:text-gray-300"></p>
                        <p class="text-[10px] text-gray-400 uppercase tracking-wider">Updated</p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 dark:bg-gray-800/50 px-6 py-4 flex flex-wrap items-center justify-end gap-2">
                <button type="button" id="view-mentor-edit-btn" class="mentor-owner-only hidden px-4 py-2 text-xs font-bold rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors">Edit Profile</button>
                <button type="button" class="close-mentor-modal px-4 py-2 text-xs font-bold rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">Close Profile</button>
                <button type="button" id="view-mentor-primary-btn" class="px-4 py-2 text-xs font-bold rounded-lg bg-teal-600 hover:bg-teal-700 text-white transition-colors disabled:bg-gray-300 dark:disabled:bg-gray-700 disabled:cursor-not-allowed">Message Mentor</button>
            </div>
        </div>
    </div>
</div>

<?php
// /resources/views/components/notifications/notification-modal.php
?>

<div id="notification-master-modal" class="fixed inset-0 z-[60] hidden">
    <div class="close-notification-modal absolute inset-0 bg-secondary-955/60 backdrop-blur-sm transition-opacity"></div>

    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-900 w-full max-w-2xl rounded-[2.5rem] shadow-2xl border border-gray-200 dark:border-secondary-900 overflow-hidden transform transition-all">

            <div class="px-8 py-6 border-b border-gray-100 dark:border-secondary-900 flex items-center justify-between bg-gray-50/50 dark:bg-secondary-950/50">
                <h3 id="nt-modal-title" class="text-xl font-black text-secondary-900 dark:text-white uppercase tracking-tighter">Notification</h3>
                <button type="button" class="close-notification-modal text-gray-400 hover:text-primary-400 transition-colors">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div id="nt-modal-body" class="p-0 overflow-y-auto max-h-[80vh]">
            </div>

        </div>
    </div>
</div>
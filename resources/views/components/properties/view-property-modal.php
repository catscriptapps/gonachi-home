<?php
// /resources/views/components/properties/view-property-modal.php

use Src\Service\AuthService;

$__currentLandlord = AuthService::currentLandlord();
$__subscribedServices = $__currentLandlord
    ? $__currentLandlord->services()->wherePivot('status_id', 1)->get(['services.id', 'services.name', 'services.short_description'])
    : collect();
?>

<style>
    #view-property-modal-content::-webkit-scrollbar {
        width: 6px;
    }

    #view-property-modal-content::-webkit-scrollbar-track {
        background: transparent;
        margin: 10px;
    }

    #view-property-modal-content::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
        border: 2px solid transparent;
    }

    .dark #view-property-modal-content::-webkit-scrollbar-thumb {
        background: #334155;
    }

    #view-property-modal-content::-webkit-scrollbar-thumb:hover {
        background: #14b8a6;
    }
</style>

<div id="view-property-modal" class="fixed inset-0 z-[10000] hidden">
    <div id="close-view-property-modal-overlay" class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>

    <div class="fixed inset-0 z-[10000] flex items-center justify-center px-4 pb-4 pt-[60px]">
        <div class="bg-white dark:bg-gray-900 w-full max-w-5xl rounded-[2.5rem] shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden transform transition-all animate-in fade-in zoom-in duration-200">

            <div class="px-8 py-5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between bg-gray-50/50 dark:bg-gray-950/50">
                <div class="flex items-center space-x-5 overflow-hidden">
                    <div id="view-property-icon-container" class="h-14 w-14 flex-shrink-0 rounded-2xl bg-primary-500 flex items-center justify-center text-white shadow-lg shadow-primary-500/20">
                        <span id="view-property-initial" class="text-2xl font-black uppercase"></span>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-3 mb-1">
                            <h3 class="text-xl font-black text-gray-900 dark:text-white truncate leading-tight" id="view-property-title">Property Name</h3>
                            <span id="view-property-status" class="px-3 py-0.5 rounded-full text-[9px] font-black uppercase tracking-widest border"></span>
                        </div>

                        <p id="view-property-unit-sub" class="text-[10px] text-gray-400 font-black uppercase tracking-[0.2em]"></p>

                        <div class="flex items-center gap-1.5 mt-1 text-gray-500 dark:text-gray-400">
                            <svg class="w-3 h-3 text-primary-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                            </svg>
                            <span id="view-property-full-address" class="text-[11px] font-bold truncate">---</span>
                        </div>
                    </div>
                </div>
                <button type="button" class="close-view-property-modal text-gray-400 hover:text-primary-500 p-2 transition-colors flex-shrink-0">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div id="view-property-modal-content" class="p-8 space-y-6 font-sans overflow-y-auto max-h-[70vh]">

                <div class="hidden" data-section="property-owner">
                    <?php
                    $modalDetailOwnerId    = 'property';
                    $modalDetailOwnerTitle = 'Property Owner / Landlord';
                    include __DIR__ . '/../ui/modal-detail-owner.php';
                    ?>
                </div>

                <div class="space-y-4 px-2">
                    <div class="flex items-center justify-between pb-2">
                        <div class="flex items-center gap-3">
                            <h3 class="text-xs font-black text-primary-500 uppercase tracking-[0.3em] flex items-center gap-2">
                                <span class="w-8 h-[2px] bg-primary-500"></span>
                                <i class="bi bi-images"></i> Property Photos
                            </h3>
                            <span id="property-pics-count" class="text-[10px] font-bold text-primary-500 bg-primary-50 dark:bg-primary-950/30 px-2 py-0.5 rounded-md border border-primary-100 dark:border-primary-900/50">0</span>
                        </div>

                        <button type="button" id="trigger-property-pic-upload" class="property-owner-only flex items-center gap-1.5 px-3 py-1.5 bg-primary-50 dark:bg-primary-900/20 text-primary-500 hover:bg-primary-500 hover:text-white rounded-lg transition-all group">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            <span class="text-[10px] font-black uppercase tracking-tight">Add Photo</span>
                        </button>
                    </div>

                    <div id="property-pics-wrapper" class="grid grid-cols-4 sm:grid-cols-6 gap-3"></div>

                    <input type="file" id="property-pic-input" class="hidden" accept="image/*" multiple>
                </div>

                <div id="section-property-services" class="bg-primary-100 dark:bg-gray-900/10 p-6 rounded-[2rem] border border-gray-100 dark:border-gray-800">
                    <h3 class="text-xs font-black text-primary-500 uppercase tracking-[0.3em] flex items-center gap-2 mb-4">
                        <span class="w-8 h-[2px] bg-primary-500"></span>
                        <i class="bi bi-cpu"></i> Subscribed Services
                    </h3>

                    <script id="landlord-subscribed-services-data" type="application/json"><?= json_encode($__subscribedServices->map(fn($s) => [
                        'id' => $s->id,
                        'name' => $s->name,
                        'short_description' => $s->short_description,
                    ])->values()) ?></script>

                    <div id="subscribed-services-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4"></div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-4 pt-2 border-t border-gray-200 dark:border-gray-800">
                    <div class="flex gap-8">
                        <div>
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Date Registered</p>
                            <span id="view-property-created" class="text-[11px] font-bold text-gray-900 dark:text-white">---</span>
                        </div>
                        <div>
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Views</p>
                            <span id="view-property-views-count" class="text-[11px] font-bold text-gray-900 dark:text-white">0</span>
                        </div>
                    </div>
                </div>

            </div>

            <div class="px-8 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-950/50 flex justify-end items-center space-x-4">
                <button type="button" class="close-view-property-modal px-5 py-3 text-xs font-black text-gray-500 uppercase tracking-widest hover:text-gray-900 dark:hover:text-white transition-colors">
                    Dismiss
                </button>
                <button type="button" id="view-property-edit-btn" class="property-owner-only px-8 py-3 text-xs font-black text-white bg-primary-500 hover:bg-primary-600 rounded-xl transition-all active:scale-95 shadow-lg shadow-primary-500/20 flex items-center gap-2 uppercase tracking-widest">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg>
                    Edit Property
                </button>
            </div>

        </div>
    </div>
</div>
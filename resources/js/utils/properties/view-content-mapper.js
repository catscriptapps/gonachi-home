// /resources/js/utils/properties/view-content-mapper.js

import { openMediaUpload } from '../media-manager.js';
import { viewMedia } from './view-media.js';
import { closeTriggerESC } from '../helpers.js';
import { openCreateAccessTokenModal } from '../access-tokens/access-token-modal.js';
import { showToast } from '../../ui/toast.js';

function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value ?? '';
    return div.innerHTML;
}

/**
 * Maps Property Data to the View Modal DOM
 */
export const ViewContentMapper = {
    mapAll(data) {
        this.mapHeader(data);
        this.mapOverviewLocation(data);
        this.mapSubscribedServices(data);
        this.mapMetadata(data);
        this.syncEditButton(data);
    },

    initMediaListeners() {
        document.addEventListener('property:pics-updated', (e) => {
            const modal = document.getElementById('view-property-modal');
            const grid = document.getElementById('property-pics-wrapper');
            if (modal && modal.dataset.propertyId == e.detail.id) {
                const canManage = grid?.dataset.canManage === 'true';
                viewMedia(e.detail.id, canManage);
            }
        });
    },

    initUIBehaviors() {
        document.addEventListener('click', (e) => {
            const uploadBtn = e.target.closest('#trigger-property-pic-upload');
            if (uploadBtn) {
                e.preventDefault();
                const modal = document.getElementById('view-property-modal');
                openMediaUpload({ type: 'property', id: modal?.dataset.propertyId, gridId: '#property-pics-wrapper' });
            }
        });

        document.addEventListener('click', (e) => {
            const isCloseTrigger = e.target.closest('.close-view-property-modal') || e.target.id === 'close-view-property-modal-overlay';
            if (isCloseTrigger) this.closeModal();
        });

        document.addEventListener('click', (e) => {
            const trigger = e.target.closest('.create-access-token-trigger');
            if (!trigger) return;

            const modal = document.getElementById('view-property-modal');
            const serviceId = trigger.dataset.serviceId;

            openCreateAccessTokenModal({
                propertyId: modal?.dataset.propertyId,
                propertyName: modal?.dataset.propertyName,
                serviceId,
                serviceName: trigger.dataset.serviceName,
            }, (tokenData) => {
                // Reflect the new active token immediately without a full page reload
                if (!modal) return;

                let tokensByService = {};
                try { tokensByService = JSON.parse(modal.dataset.activeTokensByService || '{}'); } catch (err) { /* start fresh */ }
                tokensByService[serviceId] = tokenData?.token_code;
                modal.dataset.activeTokensByService = JSON.stringify(tokensByService);

                this.mapSubscribedServices({
                    encodedId: modal.dataset.propertyId,
                    activeTokensByService: modal.dataset.activeTokensByService,
                });
            });
        });

        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.copy-access-token-btn');
            if (!btn) return;

            const tokenCode = btn.dataset.tokenCode;
            if (!tokenCode) return;

            navigator.clipboard.writeText(tokenCode)
                .then(() => showToast('Access token copied to clipboard', 'success'))
                .catch(() => showToast('Unable to copy access token', 'error'));
        });

        closeTriggerESC(this);
    },

    closeModal() {
        const modal = document.getElementById('view-property-modal');
        if (modal && !modal.classList.contains('hidden')) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    },

    mapHeader(data) {
        const initialEl = document.getElementById('view-property-initial');
        const titleEl = document.getElementById('view-property-title');
        const unitSubEl = document.getElementById('view-property-unit-sub');
        const statusEl = document.getElementById('view-property-status');

        const propertyName = data.propertyName || 'Unnamed Property';
        const isActive = data.isActive === '1';

        if (initialEl) initialEl.textContent = propertyName.charAt(0).toUpperCase();
        if (titleEl) titleEl.textContent = propertyName;
        if (unitSubEl) unitSubEl.textContent = data.unitNumber ? `Unit ${data.unitNumber}` : 'Whole Building';

        if (statusEl) {
            statusEl.textContent = isActive ? 'Active' : 'Inactive';
            statusEl.className = isActive
                ? 'px-3 py-0.5 rounded-full text-[9px] font-black uppercase tracking-widest border bg-emerald-50 text-emerald-600 border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800/30'
                : 'px-3 py-0.5 rounded-full text-[9px] font-black uppercase tracking-widest border bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700';
        }
    },

    mapOverviewLocation(data) {
        const fullAddressEl = document.getElementById('view-property-full-address');
        if (!fullAddressEl) return;

        const parts = [data.addressLine1, data.city, data.regionName, data.postalCode, data.countryName]
            .filter(part => !!part);

        fullAddressEl.textContent = parts.length ? parts.join(', ') : '---';
    },

    mapSubscribedServices(data) {
        const grid = document.getElementById('subscribed-services-grid');
        if (!grid) return;

        let services = [];
        try {
            services = JSON.parse(document.getElementById('landlord-subscribed-services-data')?.textContent || '[]');
        } catch (err) {
            console.error('Failed to parse subscribed services data:', err);
        }

        if (!services.length) {
            grid.innerHTML = `
                <div class="sm:col-span-2 md:col-span-3 text-center py-6">
                    <p class="text-xs font-bold text-gray-400">No active service subscriptions yet.</p>
                    <a href="/services" data-partial class="text-[10px] font-black uppercase tracking-wider text-primary-500 hover:underline">Browse Services</a>
                </div>`;
            return;
        }

        const encodedId = data.encodedId || '';

        let tokensByService = {};
        try {
            tokensByService = JSON.parse(data.activeTokensByService || '{}');
        } catch (err) {
            console.error('Failed to parse active tokens by service:', err);
        }

        grid.innerHTML = services.map(service => {
            const tokenCode = tokensByService[service.id];

            const createOrStatusControl = tokenCode
                ? `<span title="An access token is already active for this service" class="h-8 w-8 flex items-center justify-center rounded-lg text-emerald-500">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                    </span>`
                : `<button type="button" title="Create Access Token"
                        class="create-access-token-trigger h-8 w-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-primary-500 hover:bg-primary-50 dark:hover:bg-primary-950/40 transition-all"
                        data-service-id="${service.id}" data-service-name="${escapeHtml(service.name)}">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                    </button>`;

            const tokenRow = tokenCode
                ? `<div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5 min-w-0">
                            <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 11-12 0 6 6 0 0112 0zM2.25 21a8.966 8.966 0 015.06-8.006" />
                            </svg>
                            <span class="font-mono text-[11px] font-bold text-gray-700 dark:text-gray-300 truncate">${escapeHtml(tokenCode)}</span>
                        </div>
                        <button type="button" title="Copy access token"
                            class="copy-access-token-btn h-7 w-7 flex-shrink-0 flex items-center justify-center rounded-lg text-gray-400 hover:text-primary-500 hover:bg-primary-50 dark:hover:bg-primary-950/40 transition-all"
                            data-token-code="${escapeHtml(tokenCode)}">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5A2.25 2.25 0 0118 21.75H6A2.25 2.25 0 013.75 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" />
                            </svg>
                        </button>
                    </div>`
                : '';

            return `
            <div class="p-4 bg-white dark:bg-gray-950 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3 min-w-0">
                        <div class="h-10 w-10 rounded-xl bg-primary-50 dark:bg-primary-950/50 text-primary-500 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-house text-base"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-black text-gray-900 dark:text-white truncate">${escapeHtml(service.name)}</p>
                            <p class="text-[10px] text-gray-400 font-medium truncate">${escapeHtml(service.short_description || '')}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        <a href="/access-tokens?property_id=${encodeURIComponent(encodedId)}&service_id=${service.id}" title="View Access Tokens"
                            class="h-8 w-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-primary-500 hover:bg-primary-50 dark:hover:bg-primary-950/40 transition-all">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </a>
                        ${createOrStatusControl}
                    </div>
                </div>
                ${tokenRow}
            </div>
        `;
        }).join('');
    },

    mapMetadata(data) {
        const createdEl = document.getElementById('view-property-created');
        const viewsEl = document.getElementById('view-property-views-count');
        if (createdEl) createdEl.textContent = data.created || '---';
        if (viewsEl) viewsEl.textContent = data.viewsCount || '0';
    },

    syncEditButton(data) {
        const editBtn = document.getElementById('view-property-edit-btn');
        if (!editBtn) return;
        editBtn.onclick = () => {
            this.closeModal();
            const card = document.querySelector(`.property-card-wrapper[data-encoded-id="${data.encodedId}"]`);
            card?.querySelector('.edit-property-btn')?.click();
        };
    }
};

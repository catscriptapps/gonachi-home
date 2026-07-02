// /resources/js/utils/access-tokens/access-token-modal.js

import { showToast } from '../../ui/toast.js';

const MODAL_ID = 'create-access-token-modal';

let modalEl, overlayEl, propertyLabelEl, serviceLabelEl, submitBtn, cancelBtn, errorEl;
let current = { propertyId: null, serviceId: null };
let onCreated = null;

function buildModal() {
    if (document.getElementById(MODAL_ID)) return;

    const wrapper = document.createElement('div');
    wrapper.innerHTML = `
        <div id="${MODAL_ID}-overlay" class="fixed inset-0 z-[10001] bg-gray-900/60 backdrop-blur-sm hidden"></div>
        <div id="${MODAL_ID}" class="fixed inset-0 z-[10001] hidden flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-900 w-full max-w-md rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-800 p-6">
                <h3 class="text-lg font-black text-gray-900 dark:text-white mb-1">Create Access Token</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                    Grant <span id="access-token-service-label" class="font-bold text-primary-600 dark:text-primary-400"></span>
                    access for <span id="access-token-property-label" class="font-bold text-gray-900 dark:text-white"></span>.
                    Only one active token can exist per property and service &mdash; revoke it later to disable access.
                </p>

                <div id="access-token-error" class="hidden mb-4 px-3 py-2 text-xs rounded bg-red-100 text-red-800"></div>

                <div class="flex justify-end gap-3">
                    <button type="button" id="access-token-cancel-btn" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition">Cancel</button>
                    <button type="button" id="access-token-submit-btn" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition">Create Token</button>
                </div>
            </div>
        </div>
    `;
    document.body.append(...wrapper.childNodes);

    modalEl = document.getElementById(MODAL_ID);
    overlayEl = document.getElementById(`${MODAL_ID}-overlay`);
    propertyLabelEl = document.getElementById('access-token-property-label');
    serviceLabelEl = document.getElementById('access-token-service-label');
    submitBtn = document.getElementById('access-token-submit-btn');
    cancelBtn = document.getElementById('access-token-cancel-btn');
    errorEl = document.getElementById('access-token-error');

    cancelBtn.addEventListener('click', closeModal);
    overlayEl.addEventListener('click', closeModal);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modalEl.classList.contains('hidden')) closeModal();
    });

    submitBtn.addEventListener('click', handleSubmit);
}

function closeModal() {
    modalEl.classList.add('hidden');
    overlayEl.classList.add('hidden');
    document.body.style.overflow = '';
}

async function handleSubmit() {
    submitBtn.disabled = true;
    submitBtn.textContent = 'Creating...';
    errorEl.classList.add('hidden');

    try {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const res = await fetch(`${baseUrl}api/access-tokens`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                property_id: current.propertyId,
                service_id: current.serviceId
            })
        });

        const result = await res.json();

        if (result.success) {
            showToast(result.messages?.[0] || 'Access token created successfully', 'success');
            closeModal();
            if (typeof onCreated === 'function') onCreated(result.data);
        } else {
            errorEl.textContent = result.messages?.[0] || 'Unable to create access token.';
            errorEl.classList.remove('hidden');
        }
    } catch (err) {
        console.error('Create access token error:', err);
        errorEl.textContent = 'A network error occurred. Please try again.';
        errorEl.classList.remove('hidden');
    }

    submitBtn.disabled = false;
    submitBtn.textContent = 'Create Token';
}

/**
 * Opens the Create Access Token modal for a given property + subscribed service.
 * @param {{propertyId: string, propertyName: string, serviceId: string|number, serviceName: string}} context
 * @param {function} [successCallback] - Invoked with the created token data on success.
 */
export function openCreateAccessTokenModal(context, successCallback) {
    buildModal();

    current = { propertyId: context.propertyId, serviceId: context.serviceId };
    onCreated = successCallback || null;

    propertyLabelEl.textContent = context.propertyName || 'this property';
    serviceLabelEl.textContent = context.serviceName || 'this service';
    errorEl.classList.add('hidden');

    modalEl.classList.remove('hidden');
    overlayEl.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

// /resources/js/utils/access-tokens/revoke-access-token.js

import { showToast } from '../../ui/toast.js';

const MODAL_ID = 'revoke-access-token-modal';

let modalEl, tokenLabelEl, confirmBtn, cancelBtn;
let currentId = null;
let currentRow = null;

function buildModal() {
    if (document.getElementById(MODAL_ID)) return;

    const wrapper = document.createElement('div');
    wrapper.innerHTML = `
        <div id="${MODAL_ID}" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900 bg-opacity-70 dark:bg-opacity-80 transition-opacity duration-300 opacity-0 pointer-events-none">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-sm transform transition-all duration-300 translate-y-4 opacity-0 p-6">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Revoke Access Token</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    Revoke <span id="revoke-access-token-code" class="font-mono font-bold text-gray-900 dark:text-white"></span>?
                    The service will no longer be usable for this property until a new token is created.
                </p>

                <div class="flex justify-end space-x-3">
                    <button type="button" id="revoke-access-token-cancel-btn" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                        Cancel
                    </button>
                    <button type="button" id="revoke-access-token-confirm-btn" class="px-4 py-2 text-sm font-medium text-white bg-rose-600 rounded-md hover:bg-rose-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
                        Yes, Revoke
                    </button>
                </div>
            </div>
        </div>
    `;
    document.body.append(...wrapper.childNodes);

    modalEl = document.getElementById(MODAL_ID);
    tokenLabelEl = document.getElementById('revoke-access-token-code');
    confirmBtn = document.getElementById('revoke-access-token-confirm-btn');
    cancelBtn = document.getElementById('revoke-access-token-cancel-btn');

    cancelBtn.addEventListener('click', hideModal);
    confirmBtn.addEventListener('click', handleConfirm);
}

function showModal(id, tokenCode, row) {
    currentId = id;
    currentRow = row;
    tokenLabelEl.textContent = tokenCode;

    confirmBtn.disabled = false;
    confirmBtn.textContent = 'Yes, Revoke';

    setTimeout(() => {
        modalEl.classList.add('opacity-100', 'pointer-events-auto');
        modalEl.querySelector('.transform').classList.remove('translate-y-4', 'opacity-0');
    }, 10);
}

function hideModal() {
    modalEl.classList.remove('opacity-100', 'pointer-events-auto');
    modalEl.querySelector('.transform').classList.add('translate-y-4', 'opacity-0');
    currentId = null;
    currentRow = null;
}

async function handleConfirm() {
    if (!currentId) return;

    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Revoking...';

    try {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const res = await fetch(`${baseUrl}api/access-tokens`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ _method: 'REVOKE', id: currentId })
        });

        const result = await res.json();

        if (result.success) {
            showToast(result.messages?.[0] || 'Access token revoked', 'success');

            const badge = currentRow?.querySelector('td:nth-child(4) span');
            if (badge) {
                badge.textContent = 'revoked';
                badge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/50 border border-current/10';
            }

            const actionCell = currentRow?.querySelector('td:last-child');
            if (actionCell) {
                actionCell.innerHTML = '<span class="text-gray-300 dark:text-gray-700 text-[10px] font-black uppercase tracking-widest">&mdash;</span>';
            }

            hideModal();
        } else {
            showToast(result.messages?.[0] || 'Unable to revoke this access token.', 'error');
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Yes, Revoke';
        }
    } catch (err) {
        console.error('Revoke access token error:', err);
        showToast('A network error occurred. Please try again.', 'error');
        confirmBtn.disabled = false;
        confirmBtn.textContent = 'Yes, Revoke';
    }
}

/**
 * Attaches Revoke click handling to the access tokens table via delegation.
 */
export function initRevokeAccessToken(tableSelector = '#access-tokens-tbody') {
    const tbody = document.querySelector(tableSelector);
    if (!tbody) return;

    buildModal();

    tbody.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-action="revoke-access-token"]');
        if (!btn) return;

        const row = btn.closest('tr[data-id]');
        const id = row?.dataset.id;
        const tokenCode = row?.querySelector('td:first-child span')?.textContent?.trim();

        if (!id || !row) {
            console.error('Revoke failed: Missing ID or row element.');
            return;
        }

        showModal(id, tokenCode, row);
    });
}

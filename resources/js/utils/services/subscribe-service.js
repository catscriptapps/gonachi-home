// /resources/js/utils/services/subscribe-service.js

import { Modal } from '../../factories/modal-factory.js';
import { showToast } from '../../ui/toast.js';

// Cache modal instances per service so the Modal factory's DOM/listeners
// are only ever created once per service id (re-creating would stack
// duplicate click listeners on the footer buttons).
const modalCache = new Map();

function renderCardAsSubscribed(card) {
    if (!card) return;

    card.classList.remove(
        'bg-gray-50', 'dark:bg-gray-900/40', 'grayscale', 'opacity-70',
        'hover:grayscale-0', 'hover:opacity-100', 'hover:shadow-xl', 'hover:border-primary-500/30',
        'border', 'border-gray-200', 'dark:border-gray-800'
    );
    card.classList.add('bg-white', 'dark:bg-gray-900', 'shadow-lg', 'border-2', 'border-primary-500/50');

    const titleRow = card.querySelector('h3')?.parentElement;
    if (titleRow && !titleRow.querySelector('.subscribed-badge')) {
        const badge = document.createElement('span');
        badge.className = 'subscribed-badge px-3 py-1 rounded-full text-[10px] font-bold uppercase bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400';
        badge.textContent = 'Subscribed';
        titleRow.appendChild(badge);
    }

    card.querySelector('[data-action="subscribe-service"]')?.closest('div.mt-6')?.remove();
}

function findServiceCard(serviceId) {
    return document
        .querySelector(`[data-action="subscribe-service"][data-service-id="${serviceId}"]`)
        ?.closest('.group') || null;
}

async function handleSubscribe(serviceId, confirmBtnId, modal) {
    const confirmBtn = document.getElementById(confirmBtnId);
    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.textContent = 'Subscribing...';
    }

    try {
        const baseUrl = window.APP_CONFIG?.baseUrl || '/';
        const res = await fetch(`${baseUrl}api/landlord-services`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ service_id: serviceId })
        });

        const result = await res.json();

        if (result.success) {
            showToast(result.messages?.[0] || 'Service subscribed successfully', 'success');
            renderCardAsSubscribed(findServiceCard(serviceId));
            modal.close();
            return;
        }

        showToast(result.messages?.[0] || 'Unable to subscribe to this service.', 'error');
    } catch (err) {
        console.error('Subscribe service error:', err);
        showToast('A network error occurred. Please try again.', 'error');
    }

    if (confirmBtn) {
        confirmBtn.disabled = false;
        confirmBtn.textContent = 'Subscribe';
    }
}

function getOrCreateModal(serviceId, serviceName, price) {
    if (modalCache.has(serviceId)) return modalCache.get(serviceId);

    const priceLabel = Number(price) > 0 ? `$${Number(price).toFixed(2)}` : 'Free';
    const confirmBtnId = `confirm-subscribe-btn-${serviceId}`;

    const modal = new Modal({
        id: `subscribe-service-modal-${serviceId}`,
        title: 'Confirm Subscription',
        size: 'sm',
        content: `
            <p class="text-sm text-gray-600 dark:text-gray-400">
                You are about to subscribe to <span class="font-bold text-gray-900 dark:text-white">${serviceName}</span>.
            </p>
            <div class="mt-4 flex items-center justify-between p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                <span class="text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">Price</span>
                <span class="text-lg font-black text-primary-600 dark:text-primary-400">${priceLabel}</span>
            </div>
        `,
        footerButtons: [
            {
                id: `cancel-subscribe-btn-${serviceId}`,
                text: 'Cancel',
                classes: 'px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition mr-3',
                onClick: () => modal.close()
            },
            {
                id: confirmBtnId,
                text: 'Subscribe',
                classes: 'px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition',
                onClick: () => handleSubscribe(serviceId, confirmBtnId, modal)
            }
        ]
    });

    modalCache.set(serviceId, modal);
    return modal;
}

/**
 * Attaches the Subscribe Now click handler to every service card, scoped to
 * the services grid so listeners don't accumulate across SPA page loads.
 */
export function initSubscribeService(gridSelector = '#services-grid') {
    const grid = document.querySelector(gridSelector);
    if (!grid) return;

    grid.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-action="subscribe-service"]');
        if (!btn) return;

        const serviceId = btn.dataset.serviceId;
        const serviceName = btn.dataset.serviceName || 'this service';
        const price = btn.dataset.price || 0;

        getOrCreateModal(serviceId, serviceName, price).open();
    });
}

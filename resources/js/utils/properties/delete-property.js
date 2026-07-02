// /resources/js/utils/properties/delete-property.js

import { createDeleteHandler } from '../../factories/delete-factory.js';
import { showToast } from '../../ui/toast.js';
import { updateCount } from '../../components/table-pagination-count.js';

/**
 * Attaches delete functionality to the properties card grid via delegation.
 */
export function initDeleteProperty(tableSelector = '#properties-tbody') {
    const tbody = document.querySelector(tableSelector);
    if (!tbody) return;

    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    const deleteHandler = createDeleteHandler(`${baseUrl}api/properties`, 'Property');

    tbody.addEventListener('click', (e) => {
        const btn = e.target.closest('.delete-property-btn');
        if (!btn) return;

        e.stopPropagation();

        // Use .property-card-wrapper — only on the outer card div, never on inner divs
        // that also carry data-encoded-id (e.g. .view-property-trigger).
        const card = btn.closest('.property-card-wrapper');
        const encodedId = card?.dataset.encodedId;

        if (!encodedId || !card) {
            console.error('Delete failed: Missing encoded ID or card element.');
            return;
        }

        deleteHandler.showConfirmation(encodedId, card, (result) => {
            if (!result?.success) return;

            // Remove the card. Calling remove() on a detached element is a safe no-op,
            // so this works whether or not the factory already removed it.
            card.remove();

            showToast('Property asset successfully deleted', 'success');
            updateCount('property', tableSelector, '#properties-count');

            const remainingCards = tbody.querySelectorAll('.property-card-wrapper').length;
            if (remainingCards === 0) {
                tbody.classList.add('hidden');

                const emptyState = document.createElement('div');
                emptyState.id = 'empty-properties-state';
                emptyState.className = 'px-6 py-20 text-center';
                emptyState.innerHTML = `
                    <div class="flex flex-col items-center">
                        <p class="font-medium font-sans text-gray-500 dark:text-gray-400">No corporate property assets found</p>
                    </div>
                `;
                tbody.parentElement.insertBefore(emptyState, tbody);
            }
        });
    });
}

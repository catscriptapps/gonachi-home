// /resources/js/utils/properties/form-submit.js

import { FormValidator } from '../../utils/form-validator.js';
import { buttonSpinner } from '../../utils/spinner-utils.js';
import { loadPartial } from '../../utils/spa-router.js';
import { updateCount } from '../../components/table-pagination-count.js';

/**
 * Maps form data to an API payload for Properties matching Property.php fillables
 */
function getPayload(form) {
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    return {
        encoded_id: form.dataset.encodedId || null,
        property_name: data.propertyName?.trim(),
        unit_number: data.unitNumber?.trim() || null,
        address_line1: data.addressLine1?.trim(),
        city: data.city?.trim(),
        // Use form.dataset as a reliable fallback for disabled fields
        country_id: parseInt(data.countryId || form.dataset.countryId, 10),
        region_id: parseInt(data.regionId || '0', 10),
        postal_code: data.postalCode?.trim(),
        is_active: form.querySelector('input[name="isActive"]')?.checked ? 1 : 0,
    };
}

export function handlePropertyFormSubmission(form, mode, modalInstance, tableSelector = '#properties-tbody') {
    if (form._propertyFormListenerAttached) return;
    form._propertyFormListenerAttached = true;

    const validator = new FormValidator(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    let apiMsg = form.querySelector('.api-message') || (() => {
        const div = document.createElement('div');
        div.className = 'api-message mt-4 transition-all duration-300';
        form.appendChild(div);
        return div;
    })();

    const originalLabel = submitBtn.innerHTML;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        // 1. Standard Field Validation
        if (!validator.validateForEmptyFields(e)) return;

        submitBtn.disabled = true;
        submitBtn.innerHTML = buttonSpinner;
        apiMsg.innerHTML = '';

        try {
            const payload = getPayload(form);
            if (mode === 'edit') payload._method = 'PUT';

            const baseUrl = window.APP_CONFIG?.baseUrl || '/';
            const response = await fetch(`${baseUrl}api/properties`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });

            const result = await response.json();

            if (result.success) {
                if (result.redirect_url) {
                    modalInstance?.close();
                    loadPartial(result.redirect_url);
                    return;
                }

                // UPDATE GRID (Properties Card Grid)
                const tbody = document.querySelector(tableSelector);
                if (tbody) {
                    if (mode === 'edit' && result.cardHtml) {
                        const existingCard = document.getElementById(`property-card-${result.data?.id}`) ||
                            document.querySelector(`[data-encoded-id="${payload.encoded_id}"]`);
                        if (existingCard) existingCard.outerHTML = result.cardHtml;
                    } else if (result.cardHtml) {
                        // Remove the empty state placeholder and show the grid
                        const emptyState = document.getElementById('empty-properties-state');
                        if (emptyState) emptyState.remove();
                        tbody.classList.remove('hidden');

                        tbody.insertAdjacentHTML('afterbegin', result.cardHtml);
                    }
                    updateCount('property', tableSelector, '#properties-count');
                }

                apiMsg.innerHTML = `
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded-xl font-bold text-sm mt-2">
                        ${result.messages?.[0] || 'Asset saved successfully.'}
                    </div>
                `;

                submitBtn.style.visibility = 'hidden';
                setTimeout(() => modalInstance?.close(), (mode === 'add') ? 1200 : 800);

            } else {
                apiMsg.innerHTML = (result.messages || ['Error']).map(msg => `
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-xl font-bold text-sm mt-2">${msg}</div>
                `).join('');
            }

        } catch (err) {
            console.error('Submission Error:', err);
            apiMsg.innerHTML = `<div class="bg-red-100 text-red-700 px-4 py-2 rounded-xl font-bold text-sm mt-2">Unexpected error handling asset tracking.</div>`;
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalLabel;
            }
        }
    });
}
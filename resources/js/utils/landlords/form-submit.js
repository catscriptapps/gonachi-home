// /resources/js/utils/landlords/form-submit.js

import { showToast } from '../../ui/toast.js';
import { FormValidator } from '../../utils/form-validator.js';
import { buttonSpinner } from '../../utils/spinner-utils.js';
import { loadPartial } from '../../utils/spa-router.js';
import { updateCount } from '../../components/table-pagination-count.js';

/**
 * Maps form data to an API payload for Landlords matching Landlord.php fillables
 */
function getPayload(form) {
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    const userTypeIds = formData.getAll('userTypeIds[]');

    return {
        encoded_id: form.dataset.encodedId || null,
        company_name: data.companyName?.trim(),
        tax_id: data.taxId ? data.taxId.trim() : null,
        email: data.email?.trim(),
        password: data.password || null,
        password_confirmation: data.password_confirmation || null,
        phone: data.phone?.trim(),
        address_line1: data.addressLine1?.trim(),
        address_line2: data.addressLine2?.trim(),
        city: data.city?.trim(),
        postal_code: data.postalCode?.trim(),
        // Use form.dataset as a reliable fallback for disabled fields
        country_id: parseInt(data.countryId || form.dataset.countryId, 10),
        region_id: parseInt(data.regionId || '0', 10),
        user_type_ids: userTypeIds.map(id => parseInt(id)),
        status_id: form.querySelector('input[name="isActive"]')?.checked ? 1 : 0,
    };
}

export function handleLandlordFormSubmission(form, mode, modalInstance, tableSelector = '#landlords-tbody') {
    if (form._landlordFormListenerAttached) return;
    form._landlordFormListenerAttached = true;

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

        // 2. Password Match Validation (Only for Add Mode)
        if (mode === 'add') {
            const pass = form.querySelector('input[name="password"]');
            const confirm = form.querySelector('input[name="password_confirmation"]');

            if (pass.value !== confirm.value) {
                showToast('Passwords do not match', 'error');
                [pass, confirm].forEach(el => {
                    el.classList.add('border-red-500', 'animate-shake');
                    setTimeout(() => el.classList.remove('border-red-500', 'animate-shake'), 600);
                });
                return;
            }
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = buttonSpinner;
        apiMsg.innerHTML = '';

        try {
            const payload = getPayload(form);
            if (mode === 'edit') payload._method = 'PUT';

            const baseUrl = window.APP_CONFIG?.baseUrl || '/';
            const response = await fetch(`${baseUrl}api/landlords`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });

            const result = await response.json();

            if (result.success) {
                // --- REDIRECT LOGIC FOR LOCAL REGISTRATION ---
                // If the server sends a redirect_url, it means it was a successful
                // local registration. We close the modal and navigate to the success page.
                if (result.redirect_url) {
                    modalInstance?.close();
                    loadPartial(result.redirect_url);
                    // Return early to prevent the rest of the success logic from running
                    return;
                }

                // UPDATE TABLE (Landlords List Datatable/View pane)
                const tbody = document.querySelector(tableSelector);
                if (tbody) {
                    if (mode === 'edit' && result.rowHtml) {
                        const existingRow = document.getElementById(`landlord-row-${result.data?.id}`) ||
                            document.querySelector(`tr[data-encoded-id="${payload.encoded_id}"]`);
                        if (existingRow) existingRow.outerHTML = result.rowHtml;
                    } else if (result.rowHtml) {
                        tbody.insertAdjacentHTML('afterbegin', result.rowHtml);
                    }
                    updateCount('landlord', tableSelector, '#landlords-count');
                }

                apiMsg.innerHTML = `
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded-xl font-bold text-sm mt-2">
                        ${result.messages?.[0] || 'Saved successfully.'}
                    </div>
                `;

                submitBtn.style.visibility = 'hidden';
                setTimeout(() => modalInstance?.close(), (mode === 'add') ? 5000 : 800);

            } else {
                apiMsg.innerHTML = (result.messages || ['Error']).map(msg => `
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-xl font-bold text-sm mt-2">${msg}</div>
                `).join('');
            }

        } catch (err) {
            console.error('Submission Error:', err);
            apiMsg.innerHTML = `<div class="bg-red-100 text-red-700 px-4 py-2 rounded-xl font-bold text-sm mt-2">Unexpected error.</div>`;
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalLabel;
            }
        }
    });
}
// /resources/js/utils/tenants/form-submit.js

import { showToast } from '../../ui/toast.js';
import { FormValidator } from '../../utils/form-validator.js';
import { buttonSpinner } from '../../utils/spinner-utils.js';
import { loadPartial } from '../../utils/spa-router.js';

/**
 * Maps form data to an API payload for Tenants matching Tenant.php fillables
 */
function getPayload(form) {
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    return {
        first_name: data.firstName?.trim(),
        last_name: data.lastName?.trim(),
        email: data.email?.trim(),
        phone: data.phone?.trim() || null,
        password: data.password || null,
        password_confirmation: data.password_confirmation || null,
        return_to: form.dataset.returnTo || '/home',
    };
}

export function handleTenantFormSubmission(form, modalInstance) {
    if (!form || form._tenantFormListenerAttached) return;
    form._tenantFormListenerAttached = true;

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

        // 2. Password Match Validation
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

        submitBtn.disabled = true;
        submitBtn.innerHTML = buttonSpinner;
        apiMsg.innerHTML = '';

        try {
            const payload = getPayload(form);

            const baseUrl = window.APP_CONFIG?.baseUrl || '/';
            const response = await fetch(`${baseUrl}api/tenants`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });

            const result = await response.json();

            if (result.success) {
                // --- REDIRECT LOGIC FOR LOCAL REGISTRATION ---
                // If the server sends a redirect_url, it means it was a successful
                // local registration (already logged in). Close the modal and navigate back.
                if (result.redirect_url) {
                    modalInstance?.close();
                    loadPartial(result.redirect_url);
                    return;
                }

                apiMsg.innerHTML = `
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded-xl font-bold text-sm mt-2">
                        ${result.messages?.[0] || 'Account created successfully.'}
                    </div>
                `;

                submitBtn.style.visibility = 'hidden';
                setTimeout(() => modalInstance?.close(), 5000);
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

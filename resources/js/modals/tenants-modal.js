// /resources/js/modals/tenants-modal.js

import { Modal } from '../factories/modal-factory.js';
import { tenantForm } from '../forms/tenant-form.js';
import { handleTenantFormSubmission } from '../utils/tenants/form-submit.js';

let tenantModal = null;

// --- Add Tenant (self-service registration) ---
export function openAddTenantModal({ returnTo = '/home' } = {}) {
    if (tenantModal) tenantModal.destroy();

    tenantModal = new Modal({
        id: 'add-tenant-modal',
        title: 'Create Tenant Account',
        content: tenantForm({
            formId: 'tenants-add-form',
            buttonLabel: 'Create Account',
        }),
        size: 'md',
        showFooter: false,
    });

    tenantModal.open();

    const form = document.getElementById('tenants-add-form');
    if (form) form.dataset.returnTo = returnTo;

    handleTenantFormSubmission(form, tenantModal);
}

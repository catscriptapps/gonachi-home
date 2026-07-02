// /resources/js/modals/landlords-modal.js

import { Modal } from '../factories/modal-factory.js';
import { landlordForm } from '../forms/landlord-form.js';
import { fetchRegions } from '../api/regions-api.js';
import { fetchCountries } from '../api/countries-api.js';
import { fetchUserTypes } from '../api/user-types-api.js';
import { enableDynamicRegionLoading } from '../components/regions-component.js';
import { handleLandlordFormSubmission } from '../utils/landlords/form-submit.js';
import { loadDefaultRegions } from '../utils/helpers.js';
import { attachPhoneFormatter } from '../utils/phone-formatter.js';
import { attachPostalFormatter } from '../utils/postal-formatter.js'; // Imported formatter

let landlordModal = null;

/**
 * Initialize form features after the modal opens
 */
function initFormFeatures(formId, mode, modalInstance, initialRegionId = 866) {
    const form = document.getElementById(formId);
    if (!form) return;

    const idPrefix = mode === 'add' ? 'landlords' : 'landlords-edit';

    // 1. Handle Submission (API calls, spinners, etc.)
    handleLandlordFormSubmission(form, mode, modalInstance);

    // Attach the phone formatter once globally
    attachPhoneFormatter();

    // 2. Setup Dynamic Region/State dropdowns
    enableDynamicRegionLoading(formId);

    // 3. Automatically load regional structural defaults
    loadDefaultRegions(idPrefix, form, 'landlords');

    // 4. Attach Postal Input Formatter
    attachPostalFormatter(formId);

    // 5. Force trigger the dynamic loading listener to catch and select our default region ID
    const countrySelect = form.querySelector('select[name="countryId"]');
    if (countrySelect && countrySelect.value) {
        countrySelect.dispatchEvent(new CustomEvent('change', {
            detail: { preSelectedRegionId: initialRegionId }
        }));
    }
}

// --- Add Landlord ---
export async function openAddLandlordModal() {
    const countryId = 39; // Defaulting directly to Canada (ID = 39)
    const defaultRegionId = 866; // Ontario Default ID

    const [countries, regions, availableRoles] = await Promise.all([
        fetchCountries(),
        fetchRegions(countryId),
        fetchUserTypes()
    ]);

    if (landlordModal) landlordModal.destroy();

    landlordModal = new Modal({
        id: 'add-landlord-modal',
        title: 'New Landlord Account',
        content: landlordForm({
            mode: 'add',
            formId: 'landlords-add-form',
            buttonLabel: 'Register',
            countries,
            regions,
            availableRoles,
            countryId,
            regionId: defaultRegionId
        }),
        size: 'lg',
        showFooter: false,
    });

    landlordModal.open();
    initFormFeatures('landlords-add-form', 'add', landlordModal, defaultRegionId);
}

// --- Edit Landlord ---
export async function openEditLandlordModal(trigger) {
    const btn = trigger.closest('.edit-landlord-btn') || trigger;
    if (!btn?.dataset) return;

    const data = btn.dataset;
    const countryId = parseInt(data.countryId || '1');
    const regionId = parseInt(data.regionId || '866');

    let userTypeIds = [];
    try {
        userTypeIds = JSON.parse(data.userTypeIds || '[]').map(id => Number(id));
    } catch (e) {
        console.error("Error parsing landlord user roles:", e);
    }

    const [countries, regions, availableRoles] = await Promise.all([
        fetchCountries(),
        fetchRegions(countryId),
        fetchUserTypes()
    ]);

    if (landlordModal) landlordModal.destroy();

    landlordModal = new Modal({
        id: 'edit-landlord-modal',
        title: 'Edit Landlord Profile',
        content: landlordForm({
            mode: 'edit',
            formId: 'landlords-edit-form',
            companyName: data.companyName || '',
            email: data.email || '',
            phone: data.phone || '',
            addressLine1: data.addressLine1 || '',
            addressLine2: data.addressLine2 || '',
            postalCode: data.postalCode || '',
            countryId: countryId,
            regionId: regionId,
            city: data.city,
            isActive: data.isActive === "1",
            userTypes: userTypeIds,
            countries,
            regions,
            availableRoles,
            buttonLabel: 'Save Changes',
            encodedId: data.encodedId
        }),
        size: 'lg',
        showFooter: false,
    });

    landlordModal.open();
    initFormFeatures('landlords-edit-form', 'edit', landlordModal, regionId);
}

let listenersAttached = false;
export function initLandlordsModal() {
    if (listenersAttached) return;

    document.addEventListener('click', (e) => {
        const addBtn = e.target.closest('#add-landlord-btn');
        if (addBtn) {
            e.preventDefault();
            openAddLandlordModal();
            return;
        }

        const editBtn = e.target.closest('.edit-landlord-btn');
        if (editBtn && editBtn.dataset.action === 'edit-landlord-profile') return;

        if (editBtn) {
            e.preventDefault();
            e.stopPropagation();
            openEditLandlordModal(editBtn);
        }
    });

    listenersAttached = true;
}
// /resources/js/modals/properties-modal.js

import { Modal } from '../factories/modal-factory.js';
import { propertyForm } from '../forms/property-form.js';
import { fetchRegions } from '../api/regions-api.js';
import { fetchCountries } from '../api/countries-api.js';
import { enableDynamicRegionLoading } from '../components/regions-component.js';
import { handlePropertyFormSubmission } from '../utils/properties/form-submit.js';
import { loadDefaultRegions } from '../utils/helpers.js';
import { attachPostalFormatter } from '../utils/postal-formatter.js';

let propertyModal = null;

/**
 * Initialize form features after the modal opens
 */
function initFormFeatures(formId, mode, modalInstance, initialRegionId = 866) {
    const form = document.getElementById(formId);
    if (!form) return;

    const idPrefix = mode === 'add' ? 'properties' : 'properties-edit';

    // 1. Handle Submission (API calls, spinners, etc.)
    handlePropertyFormSubmission(form, mode, modalInstance);

    // 2. Setup Dynamic Region/State dropdowns
    enableDynamicRegionLoading(formId);

    // 3. Automatically load regional structural defaults
    loadDefaultRegions(idPrefix, form, 'properties');

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

// --- Add Property ---
export async function openAddPropertyModal(trigger) {
    const btn = trigger?.closest('#add-property-btn') || trigger;
    const landlordId = btn?.dataset?.landlordId ? parseInt(btn.dataset.landlordId) : null;

    const countryId = 39; // Defaulting directly to Canada (ID = 39)
    const defaultRegionId = 866; // Ontario Default ID

    const [countries, regions] = await Promise.all([
        fetchCountries(),
        fetchRegions(countryId)
    ]);

    if (propertyModal) propertyModal.destroy();

    propertyModal = new Modal({
        id: 'add-property-modal',
        title: 'New Property Asset',
        content: propertyForm({
            mode: 'add',
            formId: 'properties-add-form',
            buttonLabel: 'Register Property',
            countries,
            regions,
            landlordId, // Contextually assigned from active session/view state
            landlords: [], // Dropping list requirement as context is preset
            countryId,
            regionId: defaultRegionId
        }),
        size: 'lg',
        showFooter: false,
    });

    propertyModal.open();
    initFormFeatures('properties-add-form', 'add', propertyModal, defaultRegionId);
}

// --- Edit Property ---
export async function openEditPropertyModal(trigger) {
    const btn = trigger.closest('.edit-property-btn') || trigger;
    if (!btn?.dataset) return;

    const data = btn.dataset;
    const countryId = parseInt(data.countryId || '39');
    const regionId = parseInt(data.regionId || '866');
    const landlordId = data.landlordId ? parseInt(data.landlordId) : null;

    const [countries, regions] = await Promise.all([
        fetchCountries(),
        fetchRegions(countryId)
    ]);

    if (propertyModal) propertyModal.destroy();

    propertyModal = new Modal({
        id: 'edit-property-modal',
        title: 'Edit Property Details',
        content: propertyForm({
            mode: 'edit',
            formId: 'properties-edit-form',
            propertyName: data.propertyName || '',
            unitNumber: data.unitNumber || '',
            landlordId,
            addressLine1: data.addressLine1 || '',
            city: data.city || '',
            postalCode: data.postalCode || '',
            countryId: countryId,
            regionId: regionId,
            isActive: data.isActive === "1",
            countries,
            regions,
            landlords: [], // Bypassing list generation entirely
            buttonLabel: 'Save Changes',
            encodedId: data.encodedId
        }),
        size: 'lg',
        showFooter: false,
    });

    propertyModal.open();
    initFormFeatures('properties-edit-form', 'edit', propertyModal, regionId);
}

let listenersAttached = false;
export function initPropertiesModal() {
    if (listenersAttached) return;

    document.addEventListener('click', (e) => {
        const addBtn = e.target.closest('#add-property-btn');
        if (addBtn) {
            e.preventDefault();
            openAddPropertyModal(addBtn);
            return;
        }

        const editBtn = e.target.closest('.edit-property-btn');
        if (editBtn && editBtn.dataset.action === 'edit-property-profile') return;

        if (editBtn) {
            e.preventDefault();
            e.stopPropagation();
            openEditPropertyModal(editBtn);
        }
    });

    listenersAttached = true;
}
// /resources/js/modals/users-modal.js

import { Modal } from '../factories/modal-factory.js';
import { userForm } from '../forms/user-form.js';
import { fetchRegions } from '../api/regions-api.js';
import { fetchCountries } from '../api/countries-api.js';
import { fetchUserTypes } from '../api/user-types-api.js';
import { enableDynamicRegionLoading } from '../components/regions-component.js';
import { handleUserFormSubmission } from '../utils/users/form-submit.js';

let userModal = null;

/**
 * Initialize form features after the modal opens
 */
function initFormFeatures(formId, mode, modalInstance) {
    const form = document.getElementById(formId);
    if (!form) return;

    // 1. Handle Submission (API calls, spinners, etc.)
    handleUserFormSubmission(form, mode, modalInstance);
    
    // 2. Setup Dynamic Region/State dropdowns
    enableDynamicRegionLoading(formId);
}

// --- Add User ---
export async function openAddUserModal() {
    const countryId = ''; // No country selected by default for Add form
    const [countries, regions, availableRoles] = await Promise.all([
        fetchCountries(),
        fetchRegions(countryId),
        fetchUserTypes()
    ]);

    if (userModal) userModal.destroy();

    userModal = new Modal({
        id: 'add-user-modal',
        title: 'New User Account',
        content: userForm({
            mode: 'add',
            formId: 'users-add-form',
            buttonLabel: 'Register',
            countries,
            regions,
            availableRoles,
            countryId
        }),
        size: 'lg',
        showFooter: false,
    });

    userModal.open();
    initFormFeatures('users-add-form', 'add', userModal);
}

// --- Edit User ---
export async function openEditUserModal(trigger) {
    // Ensure we are hitting the button even if an icon inside was clicked
    const btn = trigger.closest('.edit-user-btn') || trigger;
    if (!btn?.dataset) return;

    const data = btn.dataset;
    const countryId = parseInt(data.countryId || '');
    
    // 1. Parse the JSON string from data-user-type-ids
    // 2. Force them into Numbers to match the database IDs exactly
    let userTypeIds = [];
    try {
        userTypeIds = JSON.parse(data.userTypeIds || '[]').map(id => Number(id));
    } catch (e) {
        console.error("Error parsing user roles:", e);
    }

    const [countries, regions, availableRoles] = await Promise.all([
        fetchCountries(),
        fetchRegions(countryId),
        fetchUserTypes()
    ]);

    if (userModal) userModal.destroy();

    const firstName = data.firstName || '';
    const lastName = data.lastName || '';

    userModal = new Modal({
        id: 'edit-user-modal',
        title: 'Edit User Profile',
        content: userForm({
            mode: 'edit',
            formId: 'users-edit-form',
            firstName: firstName,
            lastName: lastName,
            email: data.email,
            countryId: countryId,
            regionId: parseInt(data.regionId || 0),
            city: data.city,
            isActive: data.isActive === "1", 
            // Pass the cleaned array of numbers
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

    userModal.open();
    initFormFeatures('users-edit-form', 'edit', userModal);
}

let listenersAttached = false;
export function initUsersModal() {
    if (listenersAttached) return;

    document.addEventListener('click', (e) => {
        // Handle Add Button
        const addBtn = e.target.closest('#add-user-btn');
        if (addBtn) {
            e.preventDefault();
            openAddUserModal();
            return;
        }

        // Handle Edit Button (Delegated for dynamic table rows)
        const editBtn = e.target.closest('.edit-user-btn');
        
        // Prevention for profile-specific edits
        if (editBtn && editBtn.dataset.action === 'edit-user-profile') return;

        if (editBtn) {
            e.preventDefault();
            e.stopPropagation(); 
            openEditUserModal(editBtn);
        }
    });

    listenersAttached = true;
}
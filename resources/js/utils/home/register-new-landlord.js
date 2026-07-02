// /resources/js/utils/home/register-new-landlord.js

import { openAddLandlordModal } from '../../modals/landlords-modal.js';

/**
 * Initialize the registration trigger for home page buttons.
 */
export function initRegisterNewLandlord() {
    document.addEventListener('click', (e) => {

        // Look for the specific button class
        const registerBtn = e.target.closest('.register-btn');

        if (registerBtn) {
            e.preventDefault();
            e.stopImmediatePropagation();

            // Trigger the upcoming logic from our landlords modal
            openAddLandlordModal();
        }
    });
}
// /resources/js/pages/apply-page.js

import { AnimationEngine } from '../utils/animations';
import { openAddTenantModal } from '../modals/tenants-modal.js';
import { showToast } from '../ui/toast.js';

let bound = false;

/**
 * Initialize the Tenant Portal landing page events
 */
export function init() {
    AnimationEngine.refresh();

    // Guard against duplicate document-level listeners: init() re-runs after
    // every SPA partial load (e.g. the redirect following tenant account
    // creation), and stacked listeners double-fire the toggle handler below,
    // making it appear to do nothing.
    if (bound) return;
    bound = true;

    document.addEventListener('click', (e) => {
        const createAccountBtn = e.target.closest('#apply-create-account-btn');
        if (createAccountBtn) {
            e.preventDefault();
            openAddTenantModal({ returnTo: createAccountBtn.dataset.returnTo || '/home' });
            return;
        }

        const startApplicationBtn = e.target.closest('#start-new-application-btn');
        if (startApplicationBtn) {
            e.preventDefault();
            showToast('Application submission is coming soon.', 'success');
            return;
        }

        const togglePicsBtn = e.target.closest('#toggle-property-pics-btn');
        if (togglePicsBtn) {
            e.preventDefault();

            const gallery = document.getElementById('property-pics-gallery');
            const extras = gallery?.querySelectorAll('.extra-property-pic') || [];
            const label = document.getElementById('toggle-property-pics-label');
            const icon = document.getElementById('toggle-property-pics-icon');
            const isExpanded = togglePicsBtn.dataset.expanded === 'true';

            extras.forEach(el => el.classList.toggle('hidden', isExpanded));

            togglePicsBtn.dataset.expanded = isExpanded ? 'false' : 'true';
            if (label) {
                label.textContent = isExpanded
                    ? `View All Photos (${togglePicsBtn.dataset.total})`
                    : 'Show Fewer Photos';
            }
            icon?.classList.toggle('rotate-180', !isExpanded);
        }
    });
}

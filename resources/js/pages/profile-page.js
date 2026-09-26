// /resources/js/pages/profile-page.js

import { initProfileModal } from '../modals/profile-modal.js';
import { initProfileAvatar } from '../utils/profile/profile-avatar.js';
import { AnimationEngine } from '../utils/animations';
import { resendActivationLink } from '../utils/login/resend-activation.js';

function initVerifyEmailButton() {
    const btn = document.getElementById('profile-verify-email-btn');
    if (!btn || btn.dataset.initialized) return;
    btn.dataset.initialized = 'true';

    btn.addEventListener('click', async () => {
        const originalLabel = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Sending…';

        await resendActivationLink(btn.dataset.email, document.getElementById('profile-verify-email-message'));

        btn.textContent = originalLabel;
        btn.disabled = false;
    });
}

/**
 * Initialize the Profile page JS with CatScript animations
 */
export function init() {
    // 1. Fire AOS to animate the new profile cards and hero section
    AnimationEngine.refresh();

    // 2. Initialize the modal bridge for "Edit Profile"
    initProfileModal();

    // 3. Initialize avatar upload/delete logic (preserved functional integrity)
    initProfileAvatar();

    // 4. "Verify email" button (only rendered for unverified accounts)
    initVerifyEmailButton();
}
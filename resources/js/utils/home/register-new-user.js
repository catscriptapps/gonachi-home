// /resources/js/utils/home/register-new-user.js
//
// Global "Create Account" trigger — any element with class .register-btn,
// anywhere in the app, opens the same Add User modal used by the admin
// Users page (resources/js/modals/users-modal.js), matching the legacy
// gonachi/ platform exactly: there is no separate /signup page, guest
// self-registration reuses the Users modal (Location section included:
// City, Country, Region), and server/api/users.php already allows an
// unauthenticated POST-create for this reason.

import { openAddUserModal } from '../../modals/users-modal.js';

export function initRegisterNewUser() {
  if (document._registerNewUserAttached) return;
  document._registerNewUserAttached = true;

  document.addEventListener('click', (e) => {
    const registerBtn = e.target.closest('.register-btn');
    if (!registerBtn) return;

    e.preventDefault();
    e.stopImmediatePropagation();

    openAddUserModal();
  });
}

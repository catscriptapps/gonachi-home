// /resources/js/pages/landlord-tenant-validation-page.js
//
// Landlord & Tenant Validation landing/search page: wires "Unlock Contact"
// on each search result — spends 1 credit from Src\Service\
// LandlordCreditService's own wallet (landlord_and_tenant_validation.pdf's
// Step 8), same pattern as Real Estate Leads/Contractor Discovery's own
// contact-reveal AJAX flows. Guests get the shared auth-gate modal instead
// (wired globally in app.js via .auth-gate-btn — nothing to do here for them).
// Handles both directions: .unlock-contact-btn (landlord results) and
// .unlock-tenant-btn (tenant results, the mirror-image flow) — same wallet,
// different endpoint/id field.

import { showToast } from '../ui/toast.js';

export function init() {
  // Delegated on document (not a specific container) — this page has
  // multiple `.grid` sections (search results, confidence showcase, rental
  // opportunities), so scoping to "the first .grid" would be fragile.
  if (document._landlordUnlockAttached) return;
  document._landlordUnlockAttached = true;

  document.addEventListener('click', async (e) => {
    const landlordBtn = e.target.closest('.unlock-contact-btn');
    const tenantBtn = e.target.closest('.unlock-tenant-btn');
    const btn = landlordBtn || tenantBtn;
    if (!btn) return;

    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    const endpoint = landlordBtn ? 'api/landlord-contact-unlock' : 'api/tenant-unlock';
    const idField = landlordBtn ? 'landlord_id' : 'tenant_id';
    const id = landlordBtn ? btn.dataset.landlordId : btn.dataset.tenantId;
    if (!id) return;

    const originalLabel = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Unlocking...';

    try {
      const response = await fetch(`${baseUrl}${endpoint}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ [idField]: Number(id) }),
      });
      const result = await response.json();

      if (result.success) {
        const reveal = document.createElement('span');
        reveal.className = 'text-sm font-bold text-gray-900 dark:text-white';
        reveal.textContent = result.phone;
        btn.replaceWith(reveal);
        showToast(`Contact unlocked. ${result.balance} credit${result.balance === 1 ? '' : 's'} left.`, 'success');
      } else {
        showToast(result.message || 'Could not unlock this contact.', 'error');
        btn.disabled = false;
        btn.textContent = originalLabel;
      }
    } catch (err) {
      console.error('Contact unlock error:', err);
      showToast('Unexpected error. Please try again.', 'error');
      btn.disabled = false;
      btn.textContent = originalLabel;
    }
  });
}

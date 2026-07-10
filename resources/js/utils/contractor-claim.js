// /resources/js/utils/contractor-claim.js
//
// Shared "Claim This Profile" AJAX handler for the Contractor Discovery
// project — used by both the directory grid (contractor-discovery-page.js)
// and the profile detail page (contractor-page.js), since a claim button
// can appear on either.

import { showToast } from '../ui/toast.js';

export function wireContractorClaimButtons() {
  if (document._contractorClaimAttached) return;
  document._contractorClaimAttached = true;

  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-claim-contractor]');
    if (!btn) return;

    const contractorId = Number(btn.dataset.claimContractor);
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    const messageBox = document.getElementById('contractor-claim-message');

    const contactPhone = window.prompt('Confirm a contact phone number so we can verify your claim:');
    if (!contactPhone || !contactPhone.trim()) return;

    btn.disabled = true;
    const originalLabel = btn.textContent;
    btn.textContent = 'Submitting...';
    if (messageBox) messageBox.innerHTML = '';

    try {
      const response = await fetch(`${baseUrl}api/contractor-claim`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ contractor_id: contractorId, contact_phone: contactPhone.trim() }),
      });
      const result = await response.json();

      if (result.success) {
        showToast(result.messages?.[0] || 'Claim submitted.', 'success');
        const currentUrl = window.location.pathname + window.location.search;
        if (window.loadPartial) {
          window.loadPartial(currentUrl, false);
        }
      } else {
        showToast(result.messages?.[0] || 'Failed to submit claim.', 'error');
        btn.disabled = false;
        btn.textContent = originalLabel;
      }
    } catch (err) {
      console.error('Contractor claim submission error:', err);
      showToast('Unexpected error. Please try again.', 'error');
      btn.disabled = false;
      btn.textContent = originalLabel;
    }
  });
}

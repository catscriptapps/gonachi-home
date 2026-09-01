// /resources/js/utils/contractor-claim.js
//
// Shared "Claim This Profile" AJAX handler for the Contractor Discovery
// project — used by both the directory grid (contractor-discovery-page.js)
// and the profile detail page (contractor-page.js), since a claim button
// can appear on either. Uses the app's own Modal factory (never native
// browser confirm/alert/prompt) to collect the contact phone number, same
// pattern as modals/reset-modal.js.

import { showToast } from '../ui/toast.js';
import { Modal } from '../factories/modal-factory.js';
import { FormValidator } from './form-validator.js';

const claimFormHTML = `
  <form id="contractor-claim-form" class="space-y-4" novalidate>
    <p class="text-sm text-gray-600 dark:text-gray-400">Confirm a contact phone number so we can verify your claim.</p>
    <input type="tel" required id="contractor-claim-phone" name="contact_phone" placeholder="e.g. 08011111111"
      class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-secondary-500 focus:border-secondary-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100" />
    <button type="submit" class="w-full px-4 py-2 bg-secondary-600 hover:bg-secondary-700 text-white font-bold text-sm rounded-md transition-colors">
      Submit Claim
    </button>
  </form>
`;

let claimModal = null;
let pendingContractorId = null;
let pendingButton = null;

/**
 * Lazily built (and reused) singleton — the modal element lives in
 * #modal-zone, a sibling of #main-content that survives SPA partial-load
 * navigations, so it only ever needs to be created once.
 */
function getClaimModal() {
  if (claimModal) return claimModal;

  claimModal = new Modal({
    id: 'contractor-claim-modal',
    title: 'Claim This Profile',
    content: claimFormHTML,
    size: 'sm',
    showFooter: false,
  });

  const form = document.getElementById('contractor-claim-form');
  const validator = new FormValidator(form);

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!validator.validateForEmptyFields(e)) return;

    const input = document.getElementById('contractor-claim-phone');
    const contactPhone = input.value.trim();

    claimModal.close();
    await submitClaim(pendingContractorId, pendingButton, contactPhone);
  });

  return claimModal;
}

async function submitClaim(contractorId, btn, contactPhone) {
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';
  const messageBox = document.getElementById('contractor-claim-message');

  btn.disabled = true;
  const originalLabel = btn.textContent;
  btn.textContent = 'Submitting...';
  if (messageBox) messageBox.innerHTML = '';

  try {
    const response = await fetch(`${baseUrl}api/contractor-claim`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ contractor_id: contractorId, contact_phone: contactPhone }),
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
}

export function wireContractorClaimButtons() {
  if (document._contractorClaimAttached) return;
  document._contractorClaimAttached = true;

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-claim-contractor]');
    if (!btn) return;

    pendingContractorId = Number(btn.dataset.claimContractor);
    pendingButton = btn;

    const modal = getClaimModal();
    const input = document.getElementById('contractor-claim-phone');
    if (input) input.value = '';

    modal.open();
  });
}

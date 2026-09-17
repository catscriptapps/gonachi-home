// /resources/js/utils/contractor-claim.js
//
// Shared "Claim This Profile" AJAX handler for the Contractor Discovery
// project — used by both the directory grid (contractor-discovery-page.js)
// and the profile detail page (contractor-page.js), since a claim button
// can appear on either. Uses the app's own Modal factory (never native
// browser confirm/alert/prompt) to collect a contact phone number and a
// required photo of a business document — the document itself is uploaded
// via the shared upload modal (resources/js/modals/upload-modal.js, same
// WorkerPool-backed compression engine as every other picture upload in
// this app) rather than a plain <input type="file">, then its resulting
// filename is submitted as JSON alongside the rest of the claim (see
// server/api/contractor-claim-document-upload.php /
// server/api/contractor-claim.php / ContractorClaimController::submit()).

import { showToast } from '../ui/toast.js';
import { Modal } from '../factories/modal-factory.js';
import { FormValidator } from './form-validator.js';
import { uploadModal, createUploadHandler } from '../modals/upload-modal.js';

const claimFormHTML = `
  <form id="contractor-claim-form" class="space-y-4" novalidate>
    <p class="text-sm text-gray-600 dark:text-gray-400">Confirm a contact phone number so we can verify your claim.</p>
    <input type="tel" required id="contractor-claim-phone" name="contact_phone" placeholder="e.g. 08011111111"
      class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-secondary-500 focus:border-secondary-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100" />

    <div>
      <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1.5">
        Photo of a business document showing your business name (registration certificate, letterhead, signage, etc.)
      </label>
      <div id="contractor-claim-document-slot">
        <button type="button" id="contractor-claim-upload-btn" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-sm font-semibold text-gray-500 dark:text-gray-400 hover:border-secondary-500 hover:text-secondary-600 dark:hover:text-secondary-400 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0l-4 4m4-4l4 4M17 8v12m0 0l-4-4m4 4l4-4" /></svg>
          Upload Document
        </button>
      </div>
    </div>

    <textarea id="contractor-claim-note" name="message" rows="2" placeholder="Anything else we should know? (optional)"
      class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-secondary-500 focus:border-secondary-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100"></textarea>

    <button type="submit" class="w-full px-4 py-2 bg-secondary-600 hover:bg-secondary-700 text-white font-bold text-sm rounded-md transition-colors">
      Submit Claim
    </button>
  </form>
`;

let claimModal = null;
let pendingContractorId = null;
let pendingButton = null;
let uploadedDocument = null; // { url, fileName }

function renderDocumentSlot() {
  const slot = document.getElementById('contractor-claim-document-slot');
  if (!slot) return;

  if (!uploadedDocument) {
    slot.innerHTML = `
      <button type="button" id="contractor-claim-upload-btn" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-sm font-semibold text-gray-500 dark:text-gray-400 hover:border-secondary-500 hover:text-secondary-600 dark:hover:text-secondary-400 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0l-4 4m4-4l4 4M17 8v12m0 0l-4-4m4 4l4-4" /></svg>
        Upload Document
      </button>
    `;
    wireUploadButton();
    return;
  }

  slot.innerHTML = `
    <div class="flex items-center gap-3 p-2 border border-gray-200 dark:border-gray-800 rounded-lg">
      <img src="${uploadedDocument.url}" alt="Business document" class="w-14 h-14 object-cover rounded-md flex-shrink-0" />
      <span class="flex-1 text-xs font-medium text-emerald-600 dark:text-emerald-400">Document attached ✓</span>
      <button type="button" id="contractor-claim-replace-btn" class="text-xs font-semibold text-gray-500 hover:text-secondary-600 dark:hover:text-secondary-400">
        Replace
      </button>
    </div>
  `;
  document.getElementById('contractor-claim-replace-btn').addEventListener('click', openUploadModal);
}

function openUploadModal() {
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';

  uploadModal.open();
  // The shared upload modal is a singleton appended to <body> — give it a
  // tick to (re)mount before createUploadHandler queries its DOM, same
  // pattern every other caller of it uses.
  setTimeout(() => {
    createUploadHandler(
      `${baseUrl}api/contractor-claim-document-upload`,
      'contractor-claim-document',
      (files) => {
        if (!files.length) return;
        uploadedDocument = { url: files[0].url, fileName: files[0].fileName };
        renderDocumentSlot();
      },
      1,
      true,
      { single: true, maxFiles: 1 }
    );
  }, 50);
}

function wireUploadButton() {
  const btn = document.getElementById('contractor-claim-upload-btn');
  if (btn) btn.addEventListener('click', openUploadModal);
}

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
  wireUploadButton();

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!validator.validateForEmptyFields(e)) return;

    if (!uploadedDocument) {
      showToast('Please upload a photo of a business document first.', 'error');
      return;
    }

    const input = document.getElementById('contractor-claim-phone');
    const noteInput = document.getElementById('contractor-claim-note');
    const contactPhone = input.value.trim();
    const message = noteInput.value.trim();
    const documentFileName = uploadedDocument.fileName;

    claimModal.close();
    await submitClaim(pendingContractorId, pendingButton, { contactPhone, message, documentFileName });
  });

  return claimModal;
}

async function submitClaim(contractorId, btn, { contactPhone, message, documentFileName }) {
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
      body: JSON.stringify({
        contractor_id: contractorId,
        contact_phone: contactPhone,
        message,
        document_path: documentFileName,
      }),
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
    uploadedDocument = null;

    const modal = getClaimModal();
    const form = document.getElementById('contractor-claim-form');
    if (form) form.reset();
    renderDocumentSlot();

    modal.open();
  });
}

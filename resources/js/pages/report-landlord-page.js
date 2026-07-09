// /resources/js/pages/report-landlord-page.js

/**
 * Report A Landlord form logic:
 *  - Building Pictures reuse the shared upload modal (resources/js/modals/upload-modal.js),
 *    which runs every image through its WorkerPool (client-side resize/compress)
 *    before uploading — same engine as the profile avatar uploader.
 *  - Supporting Evidence is PDF-only. The image WorkerPool can't process PDFs
 *    (createImageBitmap() only handles image formats), so these upload via a
 *    plain immediate fetch instead, with both a client-side and a server-side
 *    (authoritative) PDF check.
 *  - The main form submits as JSON via fetch — no page reload, matching this
 *    app's SPA convention (see resources/js/utils/users/form-submit.js for
 *    the established pattern this mirrors).
 *
 * Exported `init()` is called by app.js on full load and after partial-load
 * navigation (see spa-router.js).
 */

import { FormValidator } from '../utils/form-validator.js';
import { uploadModal, createUploadHandler } from '../modals/upload-modal.js';
import { showToast } from '../ui/toast.js';

const MAX_PICTURES = 6;

export function init() {
  const form = document.getElementById('report-landlord-form');
  if (!form || form.dataset.initialized) return;
  form.dataset.initialized = 'true';

  const baseUrl = window.APP_CONFIG?.baseUrl || '/';
  const validator = new FormValidator(form);
  const messageBox = document.getElementById('report-landlord-message');
  const submitBtn = document.getElementById('report-landlord-submit');

  let buildingPictures = []; // { url, fileName }
  let supportingEvidence = []; // { url, fileName }

  // --- Building Pictures ---
  const addPicturesBtn = document.getElementById('add-building-pictures-btn');
  const picturesPreview = document.getElementById('building-pictures-preview');

  function renderPictures() {
    picturesPreview.innerHTML = buildingPictures.map((file, i) => `
      <div class="relative rounded-lg overflow-hidden border border-gray-200 dark:border-gray-800 h-20">
        <img src="${file.url}" class="w-full h-full object-cover" alt="Building picture" />
        <button type="button" data-remove-picture="${i}" class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs shadow">&times;</button>
      </div>
    `).join('');
  }

  picturesPreview.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-remove-picture]');
    if (!btn) return;
    buildingPictures.splice(Number(btn.dataset.removePicture), 1);
    renderPictures();
  });

  addPicturesBtn.addEventListener('click', () => {
    if (buildingPictures.length >= MAX_PICTURES) {
      showToast(`You can attach up to ${MAX_PICTURES} pictures.`, 'error');
      return;
    }

    uploadModal.open();
    setTimeout(() => {
      createUploadHandler(
        `${baseUrl}api/report-landlord-photo-upload`,
        'landlord-report-photos',
        (files) => {
          buildingPictures.push(...files.map((f) => ({ url: f.url, fileName: f.fileName })));
          renderPictures();
        },
        6,
        true,
        { maxFiles: MAX_PICTURES - buildingPictures.length }
      );
    }, 50);
  });

  // --- Supporting Evidence (PDF only) ---
  const evidenceInput = document.getElementById('supporting-evidence-input');
  const evidenceList = document.getElementById('supporting-evidence-list');

  function renderEvidence() {
    evidenceList.innerHTML = supportingEvidence.map((file, i) => `
      <div class="flex items-center justify-between gap-2 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg px-3 py-2">
        <div class="flex items-center gap-2 min-w-0">
          <svg class="h-4 w-4 text-red-500 flex-shrink-0" viewBox="0 0 24 24" fill="none"><path stroke="currentColor" stroke-width="1.5" d="M7 2h7l5 5v13a2 2 0 01-2 2H7a2 2 0 01-2-2V4a2 2 0 012-2z" /></svg>
          <span class="text-xs text-gray-600 dark:text-gray-300 truncate">${file.fileName}</span>
        </div>
        <button type="button" data-remove-evidence="${i}" class="text-gray-400 hover:text-red-500 text-xs font-semibold flex-shrink-0">Remove</button>
      </div>
    `).join('');
  }

  evidenceList.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-remove-evidence]');
    if (!btn) return;
    supportingEvidence.splice(Number(btn.dataset.removeEvidence), 1);
    renderEvidence();
  });

  evidenceInput.addEventListener('change', async () => {
    const files = Array.from(evidenceInput.files);
    if (!files.length) return;

    // Client-side check is a UX nicety only — server re-verifies actual file
    // content via finfo before accepting anything (see report-landlord-document-upload.php).
    const invalid = files.filter((f) => f.type !== 'application/pdf');
    if (invalid.length) {
      showToast('Only PDF files are allowed in Supporting Evidence.', 'error');
      evidenceInput.value = '';
      return;
    }

    const fd = new FormData();
    files.forEach((f) => fd.append('documents[]', f));

    try {
      const response = await fetch(`${baseUrl}api/report-landlord-document-upload`, {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      });
      const data = await response.json();

      if (data.success) {
        supportingEvidence.push(...data.files);
        renderEvidence();
      } else {
        showToast(data.message || 'Upload failed.', 'error');
      }
    } catch (err) {
      console.error('Supporting evidence upload error:', err);
      showToast('Upload failed. Please try again.', 'error');
    } finally {
      evidenceInput.value = '';
    }
  });

  // --- Form submission (AJAX, no reload) ---
  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (!validator.validateForEmptyFields(e)) return;

    const formData = new FormData(form);
    const payload = {
      address: (formData.get('address') || '').trim(),
      landlord_name: (formData.get('landlord_name') || '').trim(),
      property_type: formData.get('property_type') || '',
      duration_of_tenancy: (formData.get('duration_of_tenancy') || '').trim(),
      issue_type: formData.get('issue_type') || '',
      notes: (formData.get('notes') || '').trim(),
      building_picture_urls: buildingPictures.map((f) => f.url),
      supporting_evidence_urls: supportingEvidence.map((f) => f.url),
    };

    submitBtn.disabled = true;
    const originalLabel = submitBtn.textContent;
    submitBtn.textContent = 'Submitting...';
    messageBox.innerHTML = '';

    try {
      const response = await fetch(`${baseUrl}api/report-landlord`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      const result = await response.json();

      if (result.success) {
        messageBox.innerHTML = `
          <div class="flex items-start gap-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-900/30 rounded-xl p-4">
            <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <div>
              <h4 class="text-sm font-bold text-emerald-800 dark:text-emerald-300">Report submitted</h4>
              <p class="text-xs text-emerald-700 dark:text-emerald-400 mt-0.5">${result.messages?.[0] || 'Thank you for your contribution.'}</p>
            </div>
          </div>
        `;

        form.reset();
        buildingPictures = [];
        supportingEvidence = [];
        renderPictures();
        renderEvidence();
        window.scrollTo({ top: 0, behavior: 'smooth' });
      } else {
        messageBox.innerHTML = `
          <div class="flex items-start gap-3 bg-red-50 dark:bg-red-950/40 border border-red-100 dark:border-red-900/30 rounded-xl p-4">
            <svg class="h-5 w-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M4.93 4.93a10 10 0 0114.14 0 10 10 0 010 14.14 10 10 0 01-14.14 0 10 10 0 010-14.14z" /></svg>
            <div>
              <h4 class="text-sm font-bold text-red-800 dark:text-red-300">Couldn't submit that report</h4>
              <p class="text-xs text-red-700 dark:text-red-400 mt-0.5">${(result.messages || ['Please try again.']).join(' ')}</p>
            </div>
          </div>
        `;
      }
    } catch (err) {
      console.error('Report submission error:', err);
      messageBox.innerHTML = `
        <div class="flex items-start gap-3 bg-red-50 dark:bg-red-950/40 border border-red-100 dark:border-red-900/30 rounded-xl p-4">
          <p class="text-xs text-red-700 dark:text-red-400">Unexpected error. Please try again.</p>
        </div>
      `;
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = originalLabel;
    }
  });
}

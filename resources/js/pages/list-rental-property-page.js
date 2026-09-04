// /resources/js/pages/list-rental-property-page.js

/**
 * "List A Property For Rent" form logic — mirrors report-landlord-page.js:
 *  - Property Pictures reuse the shared upload modal (resources/js/modals/upload-modal.js),
 *    which runs every image through its WorkerPool (client-side resize/compress)
 *    before uploading — same engine as the profile avatar uploader.
 *  - The main form submits as JSON via fetch — no page reload, matching this
 *    app's SPA convention.
 *
 * Exported `init()` is called by app.js on full load and after partial-load
 * navigation (see spa-router.js).
 */

import { FormValidator } from '../utils/form-validator.js';
import { uploadModal, createUploadHandler } from '../modals/upload-modal.js';
import { showToast } from '../ui/toast.js';
import { registerImagePreview } from '../utils/globals/preview.js';

const MAX_PICTURES = 6;

export function init() {
  const form = document.getElementById('list-rental-property-form');
  if (!form || form.dataset.initialized) return;
  form.dataset.initialized = 'true';

  const baseUrl = window.APP_CONFIG?.baseUrl || '/';
  const validator = new FormValidator(form);
  const messageBox = document.getElementById('list-rental-property-message');
  const submitBtn = document.getElementById('list-rental-property-submit');

  let listingPictures = []; // { url, fileName }

  registerImagePreview();

  // --- Property Pictures ---
  const addPicturesBtn = document.getElementById('add-listing-pictures-btn');
  const picturesPreview = document.getElementById('listing-pictures-preview');

  function renderPictures() {
    picturesPreview.innerHTML = listingPictures.map((file, i) => `
      <div class="relative rounded-lg overflow-hidden border border-gray-200 dark:border-gray-800 h-20">
        <img src="${file.url}" class="w-full h-full object-cover" alt="Property picture" />
        <div class="absolute top-1 right-1 flex items-center gap-1">
          <button type="button" data-img-src="${file.url}" title="Preview" class="bg-white/90 dark:bg-gray-900/90 text-gray-700 dark:text-gray-200 rounded-full w-5 h-5 flex items-center justify-center shadow">
            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
          </button>
          <button type="button" data-remove-picture="${i}" title="Remove" class="bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs shadow">&times;</button>
        </div>
      </div>
    `).join('');
  }

  picturesPreview.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-remove-picture]');
    if (!btn) return;
    listingPictures.splice(Number(btn.dataset.removePicture), 1);
    renderPictures();
  });

  addPicturesBtn.addEventListener('click', () => {
    if (listingPictures.length >= MAX_PICTURES) {
      showToast(`You can attach up to ${MAX_PICTURES} pictures.`, 'error');
      return;
    }

    uploadModal.open();
    setTimeout(() => {
      createUploadHandler(
        `${baseUrl}api/rental-listing-photo-upload`,
        'rental-listing-photos',
        (files) => {
          listingPictures.push(...files.map((f) => ({ url: f.url, fileName: f.fileName })));
          renderPictures();
        },
        6,
        true,
        { maxFiles: MAX_PICTURES - listingPictures.length }
      );
    }, 50);
  });

  // --- Form submission (AJAX, no reload) ---
  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (!validator.validateForEmptyFields(e)) return;

    const formData = new FormData(form);
    const payload = {
      address: (formData.get('address') || '').trim(),
      landlord_name: (formData.get('landlord_name') || '').trim(),
      area: formData.get('area') || '',
      property_type: formData.get('property_type') || '',
      bedrooms: (formData.get('bedrooms') || '').trim(),
      rent_amount: (formData.get('rent_amount') || '').trim(),
      rent_period: formData.get('rent_period') || 'year',
      description: (formData.get('description') || '').trim(),
      photo_urls: listingPictures.map((f) => f.url),
    };

    submitBtn.disabled = true;
    const originalLabel = submitBtn.textContent;
    submitBtn.textContent = 'Submitting...';
    messageBox.innerHTML = '';

    try {
      const response = await fetch(`${baseUrl}api/rental-listing-submit`, {
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
              <h4 class="text-sm font-bold text-emerald-800 dark:text-emerald-300">Listing submitted</h4>
              <p class="text-xs text-emerald-700 dark:text-emerald-400 mt-0.5">${result.messages?.[0] || 'Thank you for your submission.'}</p>
            </div>
          </div>
        `;

        form.reset();
        listingPictures = [];
        renderPictures();
        window.scrollTo({ top: 0, behavior: 'smooth' });
      } else {
        messageBox.innerHTML = `
          <div class="flex items-start gap-3 bg-red-50 dark:bg-red-950/40 border border-red-100 dark:border-red-900/30 rounded-xl p-4">
            <svg class="h-5 w-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M4.93 4.93a10 10 0 0114.14 0 10 10 0 010 14.14 10 10 0 01-14.14 0 10 10 0 010-14.14z" /></svg>
            <div>
              <h4 class="text-sm font-bold text-red-800 dark:text-red-300">Couldn't submit that listing</h4>
              <p class="text-xs text-red-700 dark:text-red-400 mt-0.5">${(result.messages || ['Please try again.']).join(' ')}</p>
            </div>
          </div>
        `;
      }
    } catch (err) {
      console.error('Rental listing submission error:', err);
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

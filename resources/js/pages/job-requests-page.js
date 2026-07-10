// /resources/js/pages/job-requests-page.js

/**
 * Job Requests page logic (Contractor Discovery):
 *  - "+ Post A Job Request" toggles the (login-gated) submission form.
 *  - Photos reuse the shared upload modal (resources/js/modals/upload-modal.js),
 *    same WorkerPool-backed compression engine as Report A Landlord's
 *    Building Pictures.
 *  - The form submits as JSON via fetch — no page reload. On success it
 *    refreshes the page's real data (new request, updated counter) via the
 *    SPA router's own window.loadPartial(), the same pattern
 *    users/form-submit.js uses to refresh the profile page after an edit —
 *    never a full browser navigation.
 *  - "Mark As Filled" (visible only to a request's own owner) works the
 *    same way.
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
  registerImagePreview();
  wireToggleButton();
  wireMarkAsFilled();

  const form = document.getElementById('job-request-form');
  if (!form || form.dataset.initialized) return;
  form.dataset.initialized = 'true';

  const baseUrl = window.APP_CONFIG?.baseUrl || '/';
  const validator = new FormValidator(form);
  const messageBox = document.getElementById('job-request-message');
  const submitBtn = document.getElementById('job-request-submit');

  let pictures = []; // { url, fileName }

  const addPicturesBtn = document.getElementById('add-job-request-pictures-btn');
  const picturesPreview = document.getElementById('job-request-pictures-preview');

  function renderPictures() {
    picturesPreview.innerHTML = pictures.map((file, i) => `
      <div class="relative rounded-lg overflow-hidden border border-gray-200 dark:border-gray-800 h-20">
        <img src="${file.url}" class="w-full h-full object-cover" alt="Job request picture" />
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
    pictures.splice(Number(btn.dataset.removePicture), 1);
    renderPictures();
  });

  addPicturesBtn.addEventListener('click', () => {
    if (pictures.length >= MAX_PICTURES) {
      showToast(`You can attach up to ${MAX_PICTURES} pictures.`, 'error');
      return;
    }

    uploadModal.open();
    setTimeout(() => {
      createUploadHandler(
        `${baseUrl}api/job-request-photo-upload`,
        'job-request-photos',
        (files) => {
          pictures.push(...files.map((f) => ({ url: f.url, fileName: f.fileName })));
          renderPictures();
        },
        6,
        true,
        { maxFiles: MAX_PICTURES - pictures.length }
      );
    }, 50);
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (!validator.validateForEmptyFields(e)) return;

    const formData = new FormData(form);
    const payload = {
      service_category: formData.get('service_category') || '',
      location: (formData.get('location') || '').trim(),
      budget: formData.get('budget') || '',
      description: (formData.get('description') || '').trim(),
      timeline: formData.get('timeline') || '',
      contact_phone: (formData.get('contact_phone') || '').trim(),
      photo_urls: pictures.map((f) => f.url),
    };

    submitBtn.disabled = true;
    const originalLabel = submitBtn.textContent;
    submitBtn.textContent = 'Posting...';
    messageBox.innerHTML = '';

    try {
      const response = await fetch(`${baseUrl}api/job-requests`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      const result = await response.json();

      if (result.success) {
        showToast(result.messages?.[0] || 'Job request posted!', 'success');
        form.reset();
        pictures = [];
        renderPictures();

        // Refresh the page's real data (new request, updated counter) via
        // the SPA router — never a full browser reload.
        const currentUrl = window.location.pathname + window.location.search;
        if (window.loadPartial) {
          window.loadPartial(currentUrl, false);
        }
      } else {
        messageBox.innerHTML = `
          <div class="flex items-start gap-3 bg-red-50 dark:bg-red-950/40 border border-red-100 dark:border-red-900/30 rounded-xl p-4">
            <svg class="h-5 w-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M4.93 4.93a10 10 0 0114.14 0 10 10 0 010 14.14 10 10 0 01-14.14 0 10 10 0 010-14.14z" /></svg>
            <div>
              <h4 class="text-sm font-bold text-red-800 dark:text-red-300">Couldn't post that request</h4>
              <p class="text-xs text-red-700 dark:text-red-400 mt-0.5">${(result.messages || ['Please try again.']).join(' ')}</p>
            </div>
          </div>
        `;
        submitBtn.disabled = false;
        submitBtn.textContent = originalLabel;
      }
    } catch (err) {
      console.error('Job request submission error:', err);
      messageBox.innerHTML = `
        <div class="flex items-start gap-3 bg-red-50 dark:bg-red-950/40 border border-red-100 dark:border-red-900/30 rounded-xl p-4">
          <p class="text-xs text-red-700 dark:text-red-400">Unexpected error. Please try again.</p>
        </div>
      `;
      submitBtn.disabled = false;
      submitBtn.textContent = originalLabel;
    }
  });
}

function wireToggleButton() {
  const toggleBtn = document.getElementById('toggle-post-job-request-btn');
  const section = document.getElementById('post-job-request-section');
  if (!toggleBtn || !section || toggleBtn.dataset.initialized) return;
  toggleBtn.dataset.initialized = 'true';

  toggleBtn.addEventListener('click', () => {
    section.classList.toggle('hidden');
  });
}

function wireMarkAsFilled() {
  if (document._jobRequestMarkFilledAttached) return;
  document._jobRequestMarkFilledAttached = true;

  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-mark-filled]');
    if (!btn) return;

    const id = Number(btn.dataset.markFilled);
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';

    btn.disabled = true;
    btn.textContent = 'Marking...';

    try {
      const response = await fetch(`${baseUrl}api/job-request-close`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id }),
      });
      const result = await response.json();

      if (result.success) {
        showToast('Marked as filled.', 'success');
        const currentUrl = window.location.pathname + window.location.search;
        if (window.loadPartial) {
          window.loadPartial(currentUrl, false);
        }
      } else {
        showToast(result.messages?.[0] || 'Failed to update.', 'error');
        btn.disabled = false;
        btn.textContent = 'Mark As Filled';
      }
    } catch (err) {
      console.error('Mark as filled error:', err);
      showToast('Unexpected error.', 'error');
      btn.disabled = false;
      btn.textContent = 'Mark As Filled';
    }
  });
}

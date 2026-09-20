// /resources/js/pages/report-tenant-page.js

/**
 * Report A Tenant form logic — mirror-image of report-landlord-page.js,
 * minus the photo/document upload code: the Tenant Profile spec has no
 * image attachments, so this form is just the star picker plus a plain
 * JSON submit via fetch (no page reload, matching this app's SPA
 * convention).
 *
 * Exported `init()` is called by app.js on full load and after partial-load
 * navigation (see spa-router.js).
 */

import { FormValidator } from '../utils/form-validator.js';
import { showToast } from '../ui/toast.js';

export function init() {
  const form = document.getElementById('report-tenant-form');
  if (!form || form.dataset.initialized) return;
  form.dataset.initialized = 'true';

  const baseUrl = window.APP_CONFIG?.baseUrl || '/';
  const validator = new FormValidator(form);
  const messageBox = document.getElementById('report-tenant-message');
  const submitBtn = document.getElementById('report-tenant-submit');

  // --- Tenant Rating (1-5 stars) ---
  const ratingPicker = document.getElementById('tenant-rating-picker');
  const ratingValueInput = document.getElementById('tenant-rating-value');
  const ratingError = document.getElementById('tenant-rating-error');

  function renderStars(selected) {
    ratingPicker.querySelectorAll('.star-btn').forEach((btn) => {
      const isFilled = Number(btn.dataset.star) <= selected;
      btn.classList.toggle('text-amber-400', isFilled);
      btn.classList.toggle('text-gray-300', !isFilled);
      btn.classList.toggle('dark:text-gray-600', !isFilled);
    });
  }

  ratingPicker.addEventListener('click', (e) => {
    const btn = e.target.closest('.star-btn');
    if (!btn) return;

    const value = Number(btn.dataset.star);
    const next = Number(ratingValueInput.value) === value ? 0 : value;
    ratingValueInput.value = String(next);
    renderStars(next);
    ratingError.classList.add('hidden');
  });

  // --- Form submission (AJAX, no reload) ---
  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (!validator.validateForEmptyFields(e)) return;

    const rating = Number(ratingValueInput.value);
    if (rating < 1) {
      ratingError.classList.remove('hidden');
      showToast('Please select a star rating.', 'error');
      return;
    }

    const formData = new FormData(form);
    const payload = {
      tenant_name: (formData.get('tenant_name') || '').trim(),
      property_address: (formData.get('property_address') || '').trim(),
      duration_of_tenancy: (formData.get('duration_of_tenancy') || '').trim(),
      conduct_type: formData.get('conduct_type') || '',
      notes: (formData.get('notes') || '').trim(),
      rating,
      reference_name: (formData.get('reference_name') || '').trim(),
      reference_phone: (formData.get('reference_phone') || '').trim(),
    };

    submitBtn.disabled = true;
    const originalLabel = submitBtn.textContent;
    submitBtn.textContent = 'Submitting...';
    messageBox.innerHTML = '';

    try {
      const response = await fetch(`${baseUrl}api/report-tenant`, {
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
        renderStars(0);
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
      console.error('Tenant report submission error:', err);
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

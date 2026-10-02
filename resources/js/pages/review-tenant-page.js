// /resources/js/pages/review-tenant-page.js
//
// "Review A Tenant" form logic — mirrors review-landlord-page.js exactly.

import { FormValidator } from '../utils/form-validator.js';
import { wireReviewCriteriaForm, collectCriteria, collectTags } from '../utils/review-criteria-form.js';

export function init() {
  const form = document.getElementById('review-tenant-form');
  if (!form || form.dataset.initialized) return;
  form.dataset.initialized = 'true';

  const baseUrl = window.APP_CONFIG?.baseUrl || '/';
  const validator = new FormValidator(form);
  const messageBox = document.getElementById('review-tenant-message');
  const submitBtn = document.getElementById('review-tenant-submit');
  const criteriaError = document.getElementById('review-tenant-criteria-error');

  wireReviewCriteriaForm(form);

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (!validator.validateForEmptyFields(e)) return;

    const { criteria, allAnswered } = collectCriteria(form);
    criteriaError.classList.toggle('hidden', allAnswered);
    if (!allAnswered) {
      window.scrollTo({ top: form.offsetTop - 100, behavior: 'smooth' });
      return;
    }

    const formData = new FormData(form);
    const payload = {
      other_party_name: (formData.get('other_party_name') || '').trim(),
      address: (formData.get('address') || '').trim(),
      country_code: formData.get('country_code') || 'ng',
      criteria,
      comment: (formData.get('comment') || '').trim(),
      tags: collectTags(form),
    };

    submitBtn.disabled = true;
    const originalLabel = submitBtn.textContent;
    submitBtn.textContent = 'Submitting...';
    messageBox.innerHTML = '';

    try {
      const response = await fetch(`${baseUrl}api/review-tenant`, {
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
              <h4 class="text-sm font-bold text-emerald-800 dark:text-emerald-300">Review submitted</h4>
              <p class="text-xs text-emerald-700 dark:text-emerald-400 mt-0.5">${result.messages?.[0] || 'Thank you for your review.'}</p>
            </div>
          </div>
        `;
        form.reset();
        form.querySelectorAll('[data-criterion-row]').forEach((row) => { row.dataset.selectedStars = '0'; });
        form.querySelectorAll('.star-btn').forEach((btn) => btn.classList.remove('text-amber-400'));
        form.querySelectorAll('.tag-chip').forEach((chip) => chip.classList.remove('bg-indigo-600', 'border-indigo-600', 'text-white'));
        window.scrollTo({ top: 0, behavior: 'smooth' });
      } else {
        messageBox.innerHTML = `
          <div class="flex items-start gap-3 bg-red-50 dark:bg-red-950/40 border border-red-100 dark:border-red-900/30 rounded-xl p-4">
            <svg class="h-5 w-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M4.93 4.93a10 10 0 0114.14 0 10 10 0 010 14.14 10 10 0 01-14.14 0 10 10 0 010-14.14z" /></svg>
            <div>
              <h4 class="text-sm font-bold text-red-800 dark:text-red-300">Couldn't submit that review</h4>
              <p class="text-xs text-red-700 dark:text-red-400 mt-0.5">${(result.messages || ['Please try again.']).join(' ')}</p>
            </div>
          </div>
        `;
      }
    } catch (err) {
      console.error('Review submission error:', err);
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

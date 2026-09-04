// /resources/js/utils/adverts/form-submit.js
//
// Handles create/edit advert form submission. Adapted from the legacy
// gonachi/ platform's form-submit.js (same payload shape, same validation
// order: required fields -> country targeting -> at least one user type).

import { FormValidator } from '../form-validator.js';
import { buttonSpinner } from '../spinner-utils.js';
import { AnimationEngine } from '../animations.js';

function getPayload(form) {
  const formData = new FormData(form);
  const data = Object.fromEntries(formData.entries());

  const idPrefix = form.id.includes('edit') ? 'ad-edit' : 'ad-add';
  const countryJsonInput = document.getElementById(`${idPrefix}-countries-hidden-json`);
  const isAllCountries = document.getElementById(`${idPrefix}-all-countries`)?.checked;

  let selectedCountries = [];
  if (isAllCountries) {
    selectedCountries = ['ALL'];
  } else if (countryJsonInput?.value) {
    try {
      selectedCountries = JSON.parse(countryJsonInput.value);
    } catch (e) {
      selectedCountries = [];
    }
  }

  return {
    encoded_id: form.dataset.encodedId || null,
    title: data.title?.trim(),
    description: data.description?.trim(),
    keywords: data.keywords?.trim(),
    call_to_action_id: parseInt(data.call_to_action_id || 0, 10),
    landing_page_url: data.landing_page_url?.trim(),
    advert_package: parseInt(data.advert_package || 0, 10),
    selected_countries: selectedCountries,
    // Audience-type targeting is hidden in the form for now (see
    // forms/advert-form.js) — every advert targets all users by default.
    selected_user_types: ['ALL'],
  };
}

export function handleAdFormSubmission(form, mode, modalInstance, gridSelector = '#ads-grid') {
  if (form._adFormListenerAttached) return;
  form._adFormListenerAttached = true;

  const validator = new FormValidator(form);
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalLabel = submitBtn.innerHTML;

  let apiMsg = form.querySelector('.api-message');
  if (!apiMsg) {
    apiMsg = document.createElement('div');
    apiMsg.className = 'api-message mt-4';
    form.appendChild(apiMsg);
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    apiMsg.innerHTML = '';

    if (!validator.validateForEmptyFields(e)) return;

    if (typeof window.validateCountryTargeting === 'function' && !window.validateCountryTargeting()) {
      return;
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = buttonSpinner;

    try {
      const payload = getPayload(form);
      const baseUrl = window.APP_CONFIG?.baseUrl || '/';
      const response = await fetch(`${baseUrl}api/adverts`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      const result = await response.json();

      if (result.success) {
        const grid = document.querySelector(gridSelector);

        if (grid) {
          grid.classList.remove('hidden');
          document.getElementById('empty-ads-state')?.classList.add('hidden');

          if (mode === 'edit') {
            const existingCard = grid.querySelector(`.ad-card-wrapper[data-encoded-id="${payload.encoded_id}"]`);
            if (existingCard) existingCard.outerHTML = result.cardHtml;
          } else {
            grid.insertAdjacentHTML('afterbegin', result.cardHtml);
          }

          AnimationEngine.refresh();
        }

        apiMsg.innerHTML = `<div class="bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/40 text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-xl font-bold text-sm mt-2 text-center">
          ${mode === 'edit' ? 'Advert updated.' : 'Advert submitted — it will go live once approved.'}
        </div>`;

        setTimeout(() => modalInstance?.close?.(), 1200);
      } else {
        apiMsg.innerHTML = (result.messages || ['Error saving advert.'])
          .map((msg) => `<div class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/40 text-red-700 dark:text-red-400 px-4 py-2 rounded-xl font-bold text-sm mt-2">${msg}</div>`)
          .join('');
      }
    } catch (err) {
      console.error('Advert submission error:', err);
      apiMsg.innerHTML = `<div class="bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-400 px-4 py-2 rounded-xl font-bold text-sm mt-2">Server communication failed.</div>`;
    } finally {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalLabel;
    }
  });
}

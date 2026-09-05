// /resources/js/utils/quotations/form-submit.js
//
// Handles create/edit quotation form submission. Adapted from the legacy
// gonachi/ platform's quotations form-submit.js (same payload shape),
// including the one custom validation rule beyond FormValidator's required-
// field check: unit type "House" (id 5) requires a House Style.

import { FormValidator } from '../form-validator.js';
import { AnimationEngine } from '../animations.js';

function getPayload(form) {
  const formData = new FormData(form);
  const data = Object.fromEntries(formData.entries());

  return {
    encoded_id: form.dataset.encodedId || null,
    quotation_title: data.quotation_title?.trim(),
    description_of_work_to_be_done: data.description_of_work_to_be_done?.trim(),
    country_id: parseInt(data.countryId || 0, 10),
    region_id: parseInt(data.regionId || 0, 10),
    city: data.city?.trim(),
    contractor_type_id: parseInt(data.contractor_type_id || 0, 10),
    skilled_trade_id: parseInt(data.skilled_trade_id || 0, 10),
    unit_type_id: parseInt(data.unit_type_id || 0, 10),
    house_type_id: parseInt(data.house_type_id || 0, 10),
    start_date: data.start_date,
    finish_date: data.finish_date,
    start_time: data.start_time,
    finish_time: data.finish_time,
    quotation_type_id: parseInt(data.quotation_type_id || 0, 10),
    quotation_budget: data.quotation_budget?.trim(),
    quotation_dest_id: parseInt(data.quotation_dest_id || 0, 10),
    youtube_url: data.youtube_url?.trim(),
    contact_phone: data.contact_phone?.trim(),
  };
}

export function handleQuoteFormSubmission(form, mode, modalInstance) {
  if (form._quoteFormListenerAttached) return;
  form._quoteFormListenerAttached = true;

  const validator = new FormValidator(form);
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalLabel = submitBtn.innerHTML;
  const errorSlot = document.getElementById('quote-form-error-slot');

  let apiMsg = form.querySelector('.api-message');
  if (!apiMsg) {
    apiMsg = document.createElement('div');
    apiMsg.className = 'api-message mt-4';
    form.appendChild(apiMsg);
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (errorSlot) errorSlot.innerHTML = '';
    apiMsg.innerHTML = '';

    if (!validator.validateForEmptyFields(e)) return;

    const unitTypeId = form.querySelector('[name="unit_type_id"]')?.value;
    const houseTypeInput = form.querySelector('[name="house_type_id"]');
    if (unitTypeId === '5' && !houseTypeInput?.value) {
      if (errorSlot) errorSlot.innerHTML = `<p class="text-xs text-red-600">Please select a House Style.</p>`;
      houseTypeInput?.scrollIntoView({ behavior: 'smooth', block: 'center' });
      return;
    }

    submitBtn.disabled = true;
    submitBtn.textContent = mode === 'edit' ? 'Saving...' : 'Posting...';

    try {
      const payload = getPayload(form);
      const baseUrl = window.APP_CONFIG?.baseUrl || '/';
      const response = await fetch(`${baseUrl}api/quotations`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      const result = await response.json();

      if (result.success) {
        const grid = document.getElementById('quotes-grid');

        if (grid) {
          grid.classList.remove('hidden');
          document.getElementById('empty-quotes-state')?.classList.add('hidden');

          if (mode === 'edit') {
            const existingCard = grid.querySelector(`.quote-card-wrapper[data-encoded-id="${payload.encoded_id}"]`);
            if (existingCard) existingCard.outerHTML = result.cardHtml;
          } else {
            grid.insertAdjacentHTML('afterbegin', result.cardHtml);
          }

          AnimationEngine.refresh();
        }

        apiMsg.innerHTML = `<div class="bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/40 text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-xl font-bold text-sm mt-2 text-center">
          ${mode === 'edit' ? 'Quotation updated.' : 'Quotation posted.'}
        </div>`;

        setTimeout(() => modalInstance?.close?.(), 1200);
      } else {
        apiMsg.innerHTML = (result.messages || ['Error saving quotation.'])
          .map((msg) => `<div class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/40 text-red-700 dark:text-red-400 px-4 py-2 rounded-xl font-bold text-sm mt-2">${msg}</div>`)
          .join('');
      }
    } catch (err) {
      console.error('Quotation submission error:', err);
      apiMsg.innerHTML = `<div class="bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-400 px-4 py-2 rounded-xl font-bold text-sm mt-2">Server communication failed.</div>`;
    } finally {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalLabel;
    }
  });
}

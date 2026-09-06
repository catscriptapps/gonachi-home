// /resources/js/utils/listings/listing-form-submit.js
//
// Handles create/edit listing form submission. Adapted from the legacy
// gonachi/ platform's listing form-submit.js (same payload shape). Since
// unchecked checkboxes are absent from FormData entirely, the four boolean
// flags and the amenities multi-select are read explicitly rather than via
// Object.fromEntries.

import { FormValidator } from '../form-validator.js';
import { AnimationEngine } from '../animations.js';

function getPayload(form) {
  const formData = new FormData(form);
  const data = Object.fromEntries(formData.entries());

  return {
    encoded_id: form.dataset.encodedId || null,
    listing_title: data.listing_title?.trim(),
    listing_description: data.listing_description?.trim() || '',
    category_id: parseInt(data.category_id || 0, 10),
    category_type_id: parseInt(data.category_type_id || 0, 10),
    unit_type_id: parseInt(data.unit_type_id || 0, 10),
    house_type_id: parseInt(data.house_type_id || 0, 10),
    bedroom_id: parseInt(data.bedroom_id || 0, 10),
    bathroom_id: parseInt(data.bathroom_id || 0, 10),
    city: data.city?.trim() || '',
    address: data.address?.trim() || '',
    country_id: parseInt(data.countryId || 0, 10),
    region_id: parseInt(data.regionId || 0, 10),
    agreement_type_id: parseInt(data.agreement_type_id || 0, 10),
    price: data.price?.trim() || '',
    property_size: data.property_size?.trim() || '',
    move_in_date: data.move_in_date || '',
    is_ac: formData.has('is_ac') ? 1 : 0,
    is_furnished: formData.has('is_furnished') ? 1 : 0,
    parking: formData.has('parking') ? 1 : 0,
    pets_allowed: formData.has('pets_allowed') ? 1 : 0,
    amenities: formData.getAll('amenities').map((v) => parseInt(v, 10)),
    youtube_url: data.youtube_url?.trim() || '',
    contact_phone: data.contact_phone?.trim() || '',
  };
}

export function handleListingFormSubmission(form, mode, modalInstance) {
  if (form._listingFormListenerAttached) return;
  form._listingFormListenerAttached = true;

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

    submitBtn.disabled = true;
    submitBtn.textContent = mode === 'edit' ? 'Saving...' : 'Posting...';

    try {
      const payload = getPayload(form);
      const baseUrl = window.APP_CONFIG?.baseUrl || '/';
      const response = await fetch(`${baseUrl}api/listings`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      const result = await response.json();

      if (result.success) {
        const grid = document.getElementById('listings-grid');

        if (grid) {
          grid.classList.remove('hidden');
          document.getElementById('empty-listings-state')?.classList.add('hidden');

          if (mode === 'edit') {
            const existingCard = grid.querySelector(`.listing-card-wrapper[data-encoded-id="${payload.encoded_id}"]`);
            if (existingCard) existingCard.outerHTML = result.cardHtml;
          } else {
            grid.insertAdjacentHTML('afterbegin', result.cardHtml);
          }

          AnimationEngine.refresh();
        }

        apiMsg.innerHTML = `<div class="bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/40 text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-xl font-bold text-sm mt-2 text-center">
          ${mode === 'edit' ? 'Listing updated.' : 'Listing posted!'}
        </div>`;

        setTimeout(() => modalInstance?.close?.(), 1200);
      } else {
        apiMsg.innerHTML = (result.messages || ['Error saving listing.'])
          .map((msg) => `<div class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/40 text-red-700 dark:text-red-400 px-4 py-2 rounded-xl font-bold text-sm mt-2">${msg}</div>`)
          .join('');
      }
    } catch (err) {
      console.error('Listing submission error:', err);
      apiMsg.innerHTML = `<div class="bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-400 px-4 py-2 rounded-xl font-bold text-sm mt-2">Server communication failed.</div>`;
    } finally {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalLabel;
    }
  });
}

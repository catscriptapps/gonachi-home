// /resources/js/modals/swap-listings-modal.js
//
// Add/Edit Listing compose modal for Swap Marketplace — mirrors Real Estate World's
// modals/listings-modal.js (Modal factory, capture-phase document click
// delegation so .edit-swap-listing-btn's stopPropagation doesn't swallow
// it) but folds form-building and submission into one file since Swap
// Marketplace's field set is small enough not to need three separate
// modules. Edit is prefilled entirely from the card's own data-*
// attributes — no API fetch, same as the Real Estate World original. Photo
// upload/remove/reorder is the shared compose-photo-strip.js (this was the
// original implementation those three Real Estate World modals were later
// modeled on, now consolidated onto the shared helper so this gets its
// orphan-upload cleanup too, instead of maintaining a second copy of that
// logic here).

import { Modal } from '../factories/modal-factory.js';
import { swapListingFormHtml } from '../forms/swap-listing-form.js';
import { FormValidator } from '../utils/form-validator.js';
import { showToast } from '../ui/toast.js';
import { createPhotoStrip } from '../utils/compose-photo-strip.js';

let lookupsCache = null;

// One strip shared between the add and edit modals (never open at the same
// time) — see compose-photo-strip.js's docblock for why this isn't a
// module-level singleton shared across *every* type's compose modal.
const photoStrip = createPhotoStrip({ uploadPath: 'api/swap-listing-photo-upload', altText: 'Listing photo' });

function getLookups() {
  if (lookupsCache) return lookupsCache;

  const el = document.getElementById('swap-listing-lookups');
  lookupsCache = el ? JSON.parse(el.textContent) : { categories: [], types: {}, conditions: {} };
  return lookupsCache;
}

function wireConditionalFields(p) {
  const typeSelect = document.getElementById(`${p}-type`);
  const priceField = document.getElementById(`${p}-price-field`);
  const tradePrefField = document.getElementById(`${p}-trade-pref-field`);

  const sync = () => {
    priceField.classList.toggle('hidden', typeSelect.value !== 'sale');
    tradePrefField.classList.toggle('hidden', typeSelect.value !== 'swap');
  };

  typeSelect.addEventListener('change', sync);
  sync();
}

function wireSubmit(p, mode, modalInstance) {
  const form = document.getElementById(`${p}-form`);
  if (!form || form._swapListingFormListenerAttached) return;
  form._swapListingFormListenerAttached = true;

  const validator = new FormValidator(form);
  const errorSlot = document.getElementById(`${p}-error-slot`);
  const submitBtn = document.getElementById(`${p}-submit`);

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!validator.validateForEmptyFields(e)) return;

    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    const formData = new FormData(form);
    const payload = {
      encoded_id: form.dataset.encodedId || null,
      title: (formData.get('title') || '').trim(),
      description: (formData.get('description') || '').trim(),
      category_id: formData.get('category_id') || null,
      listing_type: formData.get('listing_type') || 'swap',
      condition: formData.get('condition') || 'used',
      price: formData.get('price') || null,
      trade_pref: (formData.get('trade_pref') || '').trim(),
      city: (formData.get('city') || '').trim(),
      photo_urls: photoStrip.getUrls(),
    };

    submitBtn.disabled = true;
    const originalLabel = submitBtn.textContent;
    submitBtn.textContent = mode === 'edit' ? 'Saving...' : 'Posting...';
    errorSlot.innerHTML = '';

    try {
      const response = await fetch(`${baseUrl}api/swap-listings`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      const result = await response.json();

      if (result.success) {
        photoStrip.markSaved();
        applyCardToGrids(payload.encoded_id, result.encoded_id, result.cardHtml);
        showToast(mode === 'edit' ? 'Listing updated.' : 'Listing posted.', 'success');
        window.dispatchEvent(new CustomEvent('swap-listing:saved'));
        setTimeout(() => modalInstance?.close?.(), 600);
      } else {
        errorSlot.innerHTML = `<p class="text-xs text-red-600 dark:text-red-400">${(result.messages || ['Please try again.']).join(' ')}</p>`;
      }
    } catch (err) {
      console.error('Swap Marketplace listing save error:', err);
      errorSlot.innerHTML = `<p class="text-xs text-red-600 dark:text-red-400">Unexpected error. Please try again.</p>`;
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = originalLabel;
    }
  });
}

function applyCardToGrids(existingEncodedId, encodedId, cardHtml) {
  document.querySelectorAll('#swap-listings-grid').forEach((grid) => {
    if (existingEncodedId) {
      const card = grid.querySelector(`[data-listing-wrapper][data-encoded-id="${existingEncodedId}"]`);
      if (card) card.outerHTML = cardHtml;
    } else {
      grid.classList.remove('hidden');
      document.querySelectorAll('[data-swap-empty-state]').forEach((el) => el.classList.add('hidden'));

      const header = grid.querySelector(':scope > [data-swap-grid-header]');
      if (header) {
        header.insertAdjacentHTML('afterend', cardHtml);
      } else {
        grid.insertAdjacentHTML('afterbegin', cardHtml);
      }
    }
  });
}

export function openAddListingModal() {
  photoStrip.setPhotos([]);
  const lookups = getLookups();

  const modal = new Modal({
    id: 'add-swap-listing-modal',
    title: 'Post A Listing',
    content: swapListingFormHtml({ mode: 'add', lookups }),
    size: 'lg',
    showFooter: false,
    onDismiss: () => photoStrip.discardAbandoned(),
  });

  wireConditionalFields('swap-add');
  photoStrip.wire('swap-add');
  wireSubmit('swap-add', 'add', modal);
  modal.open();
}

export function openEditListingModal(cardEl) {
  const lookups = getLookups();

  const existing = {
    encodedId: cardEl.dataset.encodedId,
    title: cardEl.dataset.title,
    description: cardEl.dataset.description,
    categoryId: cardEl.dataset.categoryId,
    listingType: cardEl.dataset.listingType,
    condition: cardEl.dataset.condition,
    price: cardEl.dataset.price,
    tradePref: cardEl.dataset.tradePref,
    city: cardEl.dataset.city,
  };

  photoStrip.setPhotos(JSON.parse(cardEl.dataset.photos || '[]'));

  const modal = new Modal({
    id: 'edit-swap-listing-modal',
    title: 'Edit Listing',
    content: swapListingFormHtml({ mode: 'edit', lookups, existing }),
    size: 'lg',
    showFooter: false,
    onDismiss: () => photoStrip.discardAbandoned(),
  });

  wireConditionalFields('swap-edit');
  photoStrip.wire('swap-edit');
  wireSubmit('swap-edit', 'edit', modal);
  modal.open();
}

export function initSwapListingsModalTriggers() {
  if (document._swapListingsModalTriggersAttached) return;
  document._swapListingsModalTriggersAttached = true;

  // Capture phase: card action buttons carry no stopPropagation here (this
  // page has no "view" click-through to guard against), but capture keeps
  // this consistent with the rest of this app's modal-trigger wiring.
  document.addEventListener('click', (e) => {
    if (e.target.closest('.create-swap-listing-trigger')) {
      openAddListingModal();
      return;
    }

    const editBtn = e.target.closest('.edit-swap-listing-btn');
    if (editBtn) {
      const card = document.querySelector(`[data-listing-wrapper][data-encoded-id="${editBtn.dataset.encodedId}"]`);
      if (card) openEditListingModal(card);
    }
  }, true);
}

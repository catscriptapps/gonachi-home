// /resources/js/modals/swap-listings-modal.js
//
// Add/Edit Listing compose modal for Swap Marketplace — mirrors Real Estate World's
// modals/listings-modal.js (Modal factory, capture-phase document click
// delegation so .edit-swap-listing-btn's stopPropagation doesn't swallow
// it) but folds form-building, photo upload, and submission into one file
// since Swap Marketplace's field set is small enough not to need three separate
// modules. Edit is prefilled entirely from the card's own data-*
// attributes — no API fetch, same as the Real Estate World original.

import { Modal } from '../factories/modal-factory.js';
import { swapListingFormHtml } from '../forms/swap-listing-form.js';
import { uploadModal, createUploadHandler } from './upload-modal.js';
import { FormValidator } from '../utils/form-validator.js';
import { showToast } from '../ui/toast.js';

const MAX_PHOTOS = 12; // matches server/helpers.php's getMediaLimit()

let lookupsCache = null;
let photos = []; // { url, fileName }

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

function renderPhotosPreview(p) {
  const preview = document.getElementById(`${p}-photos-preview`);
  if (!preview) return;

  preview.innerHTML = photos.map((file, i) => `
    <div class="relative rounded-lg overflow-hidden border border-gray-200 dark:border-gray-800 h-20">
      <img src="${file.url}" class="w-full h-full object-cover" alt="Listing photo" />
      <button type="button" data-remove-photo="${i}" title="Remove" class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs shadow">&times;</button>
    </div>
  `).join('');
}

function wirePhotoUpload(p) {
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';
  const addBtn = document.getElementById(`${p}-add-photos-btn`);
  const preview = document.getElementById(`${p}-photos-preview`);

  renderPhotosPreview(p);

  preview.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-remove-photo]');
    if (!btn) return;
    photos.splice(Number(btn.dataset.removePhoto), 1);
    renderPhotosPreview(p);
  });

  addBtn.addEventListener('click', () => {
    if (photos.length >= MAX_PHOTOS) {
      showToast(`You can attach up to ${MAX_PHOTOS} photos.`, 'error');
      return;
    }

    uploadModal.open();
    setTimeout(() => {
      createUploadHandler(
        `${baseUrl}api/swap-listing-photo-upload`,
        'swap-listing-photos',
        (files) => {
          photos.push(...files.map((f) => ({ url: f.url, fileName: f.fileName })));
          renderPhotosPreview(p);
        },
        6,
        true,
        { maxFiles: MAX_PHOTOS - photos.length }
      );
    }, 50);
  });
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
      photo_urls: photos.map((f) => f.url),
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
  photos = [];
  const lookups = getLookups();

  const modal = new Modal({
    id: 'add-swap-listing-modal',
    title: 'Post A Listing',
    content: swapListingFormHtml({ mode: 'add', lookups }),
    size: 'lg',
    showFooter: false,
  });

  wireConditionalFields('swap-add');
  wirePhotoUpload('swap-add');
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

  photos = JSON.parse(cardEl.dataset.photos || '[]').map((p) => ({ url: p.url, fileName: p.url }));

  const modal = new Modal({
    id: 'edit-swap-listing-modal',
    title: 'Edit Listing',
    content: swapListingFormHtml({ mode: 'edit', lookups, existing }),
    size: 'lg',
    showFooter: false,
  });

  wireConditionalFields('swap-edit');
  wirePhotoUpload('swap-edit');
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

// /resources/js/utils/swap-listings/view-swap-listing-modal.js
//
// The shared view-listing modal used across /swap, /my-swap-listings, and
// /saved-swap-listings — mirrors Real Estate World's own
// view-quotation-modal.js, but populates the ENTIRE modal (including the
// full photo gallery) from the clicked card's data-* attributes with zero
// fetch: the card already carries every photo URL in data-photos (built
// for the edit modal's prefill), unlike quotations' cards which only carry
// a single thumbnail and need a GET to list the rest. Only the video
// upload/remove actions hit the network, exactly like quotations.

import { showToast } from '../../ui/toast.js';
import { confirmDialog } from '../../ui/confirm.js';
import { videoUploadModal, createVideoUploadHandler } from '../../modals/video-upload-modal.js';
import { openEditListingModal } from '../../modals/swap-listings-modal.js';
import { registerImagePreview } from '../globals/preview.js';
import { reorderButtonHtml, wirePicReorder } from '../pic-reorder.js';

export function initViewSwapListingModal() {
  const modal = document.getElementById('view-swap-modal');
  if (!modal || modal.dataset.initialized) return;
  modal.dataset.initialized = 'true';

  registerImagePreview();

  document.body.addEventListener('click', (e) => {
    const trigger = e.target.closest('.view-swap-trigger');
    if (trigger) openModal(trigger);
  });

  modal.querySelectorAll('.close-swap-modal').forEach((el) => el.addEventListener('click', closeModal));

  wirePicReorder(document.getElementById('swap-pics-wrapper'), (order) => reorderPhotos(modal, order));

  document.getElementById('swap-add-video-btn')?.addEventListener('click', () => triggerVideoUpload(modal));
  document.getElementById('swap-remove-video-btn')?.addEventListener('click', () => removeVideo(modal));

  document.getElementById('view-swap-edit-btn')?.addEventListener('click', () => {
    const card = document.querySelector(`[data-listing-wrapper][data-encoded-id="${modal.dataset.activeEncodedId}"]`);
    closeModal();
    if (card) openEditListingModal(card);
  });

  document.getElementById('view-swap-primary-btn')?.addEventListener('click', () => handlePrimaryAction(modal));
}

function closeModal() {
  document.getElementById('view-swap-modal').classList.add('hidden');
}

function openModal(el) {
  const modal = document.getElementById('view-swap-modal');
  const d = el.dataset;

  modal.dataset.activeEncodedId = d.encodedId;
  modal.dataset.ownerId = d.ownerId;
  modal.dataset.listingType = d.listingType;

  document.getElementById('view-swap-title').textContent = d.title;
  document.getElementById('view-swap-subtitle').textContent = `${d.typeLabel} · ${d.conditionLabel}`;
  document.getElementById('view-swap-status-badge').innerHTML = statusBadgeHtml(d.status);
  document.getElementById('view-swap-description').textContent = d.description || '—';

  document.getElementById('view-swap-category').textContent = d.categoryName || '—';
  document.getElementById('view-swap-condition').textContent = d.conditionLabel || '—';
  document.getElementById('view-swap-city').textContent = d.city || 'Remote / TBD';

  const priceRow = document.getElementById('view-swap-price-row');
  const tradePrefRow = document.getElementById('view-swap-trade-pref-row');
  if (d.listingType === 'sale' && d.price) {
    priceRow.classList.remove('hidden');
    document.getElementById('view-swap-price').textContent = `₦${Number(d.price).toLocaleString()}`;
  } else {
    priceRow.classList.add('hidden');
  }
  if (d.listingType === 'swap' && d.tradePref) {
    tradePrefRow.classList.remove('hidden');
    document.getElementById('view-swap-trade-pref').textContent = d.tradePref;
  } else {
    tradePrefRow.classList.add('hidden');
  }

  document.getElementById('view-swap-created').textContent = d.created || '—';
  document.getElementById('view-swap-updated').textContent = d.updated || '—';
  document.getElementById('view-swap-views-count').textContent = d.views || '0';

  // Owner block (shared components/ui/modal-detail-owner.php markup)
  const avatarContainer = document.getElementById('view-swap-owner-avatar-container');
  const initialEl = document.getElementById('view-swap-owner-initial');
  if (d.ownerAvatar) {
    avatarContainer.innerHTML = `<img src="${d.ownerAvatar}" class="w-full h-full object-cover">`;
  } else {
    avatarContainer.innerHTML = '';
    avatarContainer.appendChild(initialEl);
    initialEl.textContent = d.ownerInitial;
  }
  document.getElementById('view-swap-owner-name').textContent = d.ownerName;
  document.getElementById('view-swap-owner-location').textContent = d.ownerLocation || 'Location not set';

  const currentUserId = window.sessionUserId ? Number(window.sessionUserId) : null;
  const canManage = currentUserId !== null && Number(d.ownerId) === currentUserId;
  const isLoggedIn = currentUserId !== null;

  document.querySelectorAll('.swap-owner-only').forEach((el2) => el2.classList.toggle('hidden', !canManage));

  const primaryBtn = document.getElementById('view-swap-primary-btn');
  if (canManage) {
    primaryBtn.classList.remove('hidden');
    primaryBtn.textContent = d.status === 'completed' ? 'Reactivate Listing' : 'Mark As Completed';
  } else if (isLoggedIn) {
    primaryBtn.classList.remove('hidden');
    primaryBtn.textContent = d.isSaved === '1' ? 'Remove from Saved' : 'Save This Listing';
  } else {
    primaryBtn.classList.add('hidden');
  }

  renderPictures(JSON.parse(d.photos || '[]'), canManage);
  renderVideo(modal, d.videoUrl || '', canManage);

  modal.classList.remove('hidden');
}

function handlePrimaryAction(modal) {
  const currentUserId = window.sessionUserId ? Number(window.sessionUserId) : null;
  const canManage = currentUserId !== null && Number(modal.dataset.ownerId) === currentUserId;

  if (canManage) {
    quickToggleStatus(modal);
  } else {
    quickToggleSave(modal);
  }
}

async function quickToggleStatus(modal) {
  const isReactivate = document.getElementById('view-swap-primary-btn').textContent.includes('Reactivate');
  const confirmed = await confirmDialog(
    isReactivate ? 'Reactivate this listing?' : 'Mark this listing as completed?',
    'Confirm',
    'Cancel',
    'bg-purple-600 hover:bg-purple-700'
  );
  if (!confirmed) return;

  const baseUrl = window.APP_CONFIG?.baseUrl || '/';
  const encodedId = modal.dataset.activeEncodedId;

  try {
    const response = await fetch(`${baseUrl}api/swap-listings`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ encoded_id: encodedId, intent: isReactivate ? 'posted' : 'completed' }),
    });
    const result = await response.json();

    if (result.success) {
      document.querySelectorAll(`[data-listing-wrapper][data-encoded-id="${encodedId}"]`).forEach((el) => {
        el.outerHTML = result.cardHtml;
      });
      showToast(isReactivate ? 'Listing reactivated.' : 'Listing marked as completed.', 'success');
      closeModal();
    } else {
      showToast(result.message || 'Could not update listing.', 'error');
    }
  } catch (err) {
    console.error('Swap Marketplace listing status update error:', err);
    showToast('Unexpected error.', 'error');
  }
}

async function quickToggleSave(modal) {
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';
  const encodedId = modal.dataset.activeEncodedId;
  const primaryBtn = document.getElementById('view-swap-primary-btn');

  primaryBtn.disabled = true;

  try {
    const response = await fetch(`${baseUrl}api/swap-listing-save`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ encoded_id: encodedId }),
    });
    const result = await response.json();

    if (result.success) {
      primaryBtn.textContent = result.saved ? 'Remove from Saved' : 'Save This Listing';

      // Keeps data-is-saved in sync on the card wrapper itself — without
      // this, reopening this same modal (no full page reload in between)
      // would re-populate the primary button from the now-stale attribute
      // and show the wrong Save/Unsave label.
      document.querySelectorAll(`[data-listing-wrapper][data-encoded-id="${encodedId}"]`).forEach((card) => {
        card.dataset.isSaved = result.saved ? '1' : '0';
      });

      // Keep the underlying card's own bookmark icon in sync — same
      // element swap-listing-actions.js's handleSaveToggle() does, so
      // reopening the card's view later (or its own icon button) reflects
      // the change without a full grid re-render.
      document.querySelectorAll(`.save-swap-listing-btn[data-encoded-id="${encodedId}"]`).forEach((btn) => {
        const svg = btn.querySelector('svg');
        if (result.saved) {
          btn.classList.remove('text-gray-400', 'hover:text-amber-500');
          btn.classList.add('text-amber-500');
          svg?.setAttribute('fill', 'currentColor');
        } else {
          btn.classList.remove('text-amber-500');
          btn.classList.add('text-gray-400', 'hover:text-amber-500');
          svg?.setAttribute('fill', 'none');
        }
      });
      showToast(result.saved ? 'Saved.' : 'Removed from saved.', 'success');
    } else {
      showToast(result.message || 'Could not update saved listings.', 'error');
    }
  } catch (err) {
    console.error('Swap Marketplace listing save-toggle error:', err);
    showToast('Unexpected error.', 'error');
  } finally {
    primaryBtn.disabled = false;
  }
}

function statusBadgeHtml(status) {
  return status === 'completed'
    ? '<span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-800 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700">Completed</span>'
    : '<span class="inline-flex items-center rounded-full bg-green-50 dark:bg-green-900/20 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-green-600 dark:text-green-400 border border-green-100 dark:border-green-800/30">Active</span>';
}

// -------------------------------
// Pictures — read-only gallery (add/remove happens via the Edit modal's
// own photo picker, not here), sourced entirely from the card's
// data-photos JSON, no fetch.
// -------------------------------

function renderPictures(pics, canManage = false) {
  const wrapper = document.getElementById('swap-pics-wrapper');
  const countEl = document.getElementById('view-swap-pics-count');

  countEl.textContent = `${pics.length}/12`;

  wrapper.innerHTML = pics
    .map(
      (pic) => `
      <div data-pic-tile data-pic-id="${pic.id}" class="relative rounded-lg overflow-hidden border border-gray-200 dark:border-gray-800 h-20">
        <img src="${pic.url}" data-img-src="${pic.url}" class="w-full h-full object-cover cursor-pointer">
        ${canManage && pics.length > 1 ? reorderButtonHtml() : ''}
      </div>`
    )
    .join('') || '<p class="col-span-4 text-xs text-gray-400 text-center py-4">No photos yet.</p>';
}

async function reorderPhotos(modal, order) {
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';
  const encodedId = modal.dataset.activeEncodedId;

  try {
    const response = await fetch(`${baseUrl}api/swap-listing-pic-reorder`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: encodedId, order }),
    });
    const result = await response.json();

    if (result.success) {
      renderPictures(result.photos || [], true);
      updateCardInGrid(encodedId, result.cardHtml);
    } else {
      showToast(result.message || 'Could not reorder photos.', 'error');
    }
  } catch (err) {
    console.error('Swap Marketplace photo reorder error:', err);
    showToast('Unexpected error. Please try again.', 'error');
  }
}

// -------------------------------
// Video (owner-only, max 1 — SwapListingsController::attachVideo() always
// replaces whichever video already exists).
// -------------------------------

// Keeps the grid card behind the modal in sync after a video add/remove —
// without this, the card's data-video-url stays stale and reopening this
// same modal later (no full page reload in between) would wrongly show
// "No video yet." even though the video is really still there server-side.
function updateCardInGrid(encodedId, cardHtml) {
  if (!cardHtml) return;
  document.querySelectorAll(`[data-listing-wrapper][data-encoded-id="${encodedId}"]`).forEach((el) => {
    el.outerHTML = cardHtml;
  });
}

function renderVideo(modal, videoUrl, canManage) {
  const wrapper = document.getElementById('swap-video-wrapper');
  const addBtn = document.getElementById('swap-add-video-btn');
  const removeBtn = document.getElementById('swap-remove-video-btn');
  if (!wrapper || !addBtn || !removeBtn) return;

  modal.dataset.hasVideo = videoUrl ? '1' : '0';

  wrapper.innerHTML = videoUrl
    ? `<video src="${videoUrl}" controls class="w-full max-h-48 rounded-lg bg-black"></video>`
    : '<p class="text-xs text-gray-400">No video yet.</p>';

  addBtn.classList.toggle('hidden', !(canManage && !videoUrl));
  addBtn.classList.toggle('flex', canManage && !videoUrl);
  removeBtn.classList.toggle('hidden', !(canManage && videoUrl));
  removeBtn.classList.toggle('flex', canManage && !!videoUrl);
}

function triggerVideoUpload(modal) {
  const encodedId = modal.dataset.activeEncodedId;
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';

  videoUploadModal.open();
  setTimeout(() => {
    createVideoUploadHandler(`${baseUrl}api/swap-listing-upload-video?id=${encodeURIComponent(encodedId)}`, (files, cardHtml) => {
      const url = files[0]?.url;
      if (url) {
        showToast('Video added.', 'success');
        renderVideo(modal, url, true);
        updateCardInGrid(encodedId, cardHtml);
      }
    });
  }, 50);
}

async function removeVideo(modal) {
  const confirmed = await confirmDialog('Remove this video?', 'Remove', 'Cancel', 'bg-red-600 hover:bg-red-700');
  if (!confirmed) return;

  const baseUrl = window.APP_CONFIG?.baseUrl || '/';
  const encodedId = modal.dataset.activeEncodedId;

  try {
    const response = await fetch(`${baseUrl}api/swap-listing-video-delete`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: encodedId }),
    });
    const result = await response.json();

    if (result.success) {
      renderVideo(modal, '', true);
      updateCardInGrid(encodedId, result.cardHtml);
      showToast('Video removed.', 'success');
    } else {
      showToast(result.message || 'Could not remove video.', 'error');
    }
  } catch (err) {
    console.error('Remove swap listing video error:', err);
    showToast('Unexpected error.', 'error');
  }
}

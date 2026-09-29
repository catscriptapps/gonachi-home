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
import { initSwapListingResponseTriggers } from '../../modals/swap-listing-response-modal.js';
import { initSwapListingThreadTriggers } from '../../modals/swap-listing-thread-modal.js';
import { registerImagePreview } from '../globals/preview.js';
import { reorderButtonHtml, wirePicReorder } from '../pic-reorder.js';
import { setPendingBadge } from '../pending-badge.js';

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

  document.getElementById('swap-pics-wrapper')?.addEventListener('click', (e) => {
    const delBtn = e.target.closest('[data-delete-pic]');
    if (delBtn) deletePhoto(delBtn, modal);
  });
  wirePicReorder(document.getElementById('swap-pics-wrapper'), (order) => reorderPhotos(modal, order));

  document.getElementById('swap-add-video-btn')?.addEventListener('click', () => triggerVideoUpload(modal));
  document.getElementById('swap-remove-video-btn')?.addEventListener('click', () => removeVideo(modal));

  document.getElementById('view-swap-edit-btn')?.addEventListener('click', () => {
    const card = document.querySelector(`[data-listing-wrapper][data-encoded-id="${modal.dataset.activeEncodedId}"]`);
    closeModal();
    if (card) openEditListingModal(card);
  });

  document.getElementById('view-swap-primary-btn')?.addEventListener('click', () => handlePrimaryAction(modal));
  document.getElementById('view-swap-save-btn')?.addEventListener('click', () => quickToggleSave(modal));

  document.getElementById('view-swap-responses-list')?.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-response-action]');
    if (btn) handleResponseAction(btn, modal);
  });

  initSwapListingResponseTriggers();
  initSwapListingThreadTriggers();
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
  const saveBtn = document.getElementById('view-swap-save-btn');
  const isCompleted = d.status === 'completed';

  primaryBtn.classList.remove('is-connect', 'is-thread', 'auth-gate-btn');

  if (canManage) {
    primaryBtn.classList.remove('hidden');
    primaryBtn.textContent = isCompleted ? 'Reactivate Listing' : 'Mark As Completed';
  } else if (isLoggedIn && d.responseStatus) {
    // Already wrote to this owner: show the read-only conversation instead.
    primaryBtn.classList.remove('hidden');
    primaryBtn.textContent = 'View Conversation';
    primaryBtn.classList.add('is-thread');
  } else if (!isCompleted) {
    // Visitors' main action: message the owner. Signed-in visitors get the
    // "Connect with Owner" modal (swap-listing-response-modal.js, wired via
    // .is-connect); guests get the sign-in gate instead.
    primaryBtn.classList.remove('hidden');
    primaryBtn.textContent = 'Connect with Owner';
    primaryBtn.classList.add(isLoggedIn ? 'is-connect' : 'auth-gate-btn');
  } else {
    primaryBtn.classList.add('hidden');
  }

  saveBtn.classList.toggle('hidden', !isLoggedIn || canManage);
  saveBtn.textContent = d.isSaved === '1' ? 'Remove from Saved' : 'Save This Listing';

  const responsesWrapper = document.getElementById('view-swap-responses-wrapper');
  if (canManage) {
    responsesWrapper.classList.remove('hidden');
    loadResponses(d.encodedId);
  } else {
    responsesWrapper.classList.add('hidden');
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
  } else if (currentUserId === null) {
    // Guest: the sign-in gate (.auth-gate-btn) opens on its own — just get
    // this modal out of its way.
    closeModal();
  }
  // Signed-in visitors: the .is-connect capture-phase trigger already
  // opened the Connect modal.
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
  const primaryBtn = document.getElementById('view-swap-save-btn');

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

// -------------------------------
// Responses (the "Connect with Owner" messages — owner-only). Mirrors
// Real Estate World's quotation Responses list.
// -------------------------------

async function loadResponses(encodedId) {
  const list = document.getElementById('view-swap-responses-list');
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';

  list.innerHTML = `<div class="flex justify-center py-3"><div class="animate-spin rounded-full h-5 w-5 border-2 border-purple-500 border-t-transparent"></div></div>`;

  try {
    const response = await fetch(`${baseUrl}api/swap-listing-responses?id=${encodeURIComponent(encodedId)}`);
    const result = await response.json();
    const responses = result.responses || [];

    list.innerHTML = responses.map(renderResponseRow).join('') || '<p class="text-xs text-gray-400">No messages yet.</p>';

    const pendingCount = responses.filter((r) => r.status === 'pending').length;
    document.querySelectorAll(`[data-listing-wrapper][data-encoded-id="${encodedId}"]`).forEach((card) => setPendingBadge(card, pendingCount));
  } catch (err) {
    console.error('Load Swap Marketplace responses error:', err);
    list.innerHTML = '<p class="text-xs text-gray-400">Couldn\'t load messages.</p>';
  }
}

function renderResponseRow(r) {
  const statusMap = {
    pending: 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400 border-yellow-100 dark:border-yellow-800/30',
    accepted: 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 border-green-100 dark:border-green-800/30',
    declined: 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 border-red-100 dark:border-red-800/30',
  };
  const badge = `<span class="inline-flex items-center rounded-full px-2 py-0.5 text-[9px] font-black uppercase tracking-wider border ${statusMap[r.status] || statusMap.pending}">${escapeHtml(r.status)}</span>`;

  const actions = r.status === 'pending'
    ? `<div class="flex items-center gap-2 mt-2">
        <button type="button" data-response-action="accept" data-response-id="${r.id}" class="text-[11px] font-bold text-emerald-600 hover:text-emerald-700">Accept</button>
        <button type="button" data-response-action="decline" data-response-id="${r.id}" class="text-[11px] font-bold text-red-500 hover:text-red-600">Decline</button>
      </div>`
    : '';

  return `
    <div class="p-3 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30">
      <div class="flex items-center justify-between gap-2 mb-1">
        <span class="text-xs font-bold text-gray-800 dark:text-gray-200">${escapeHtml(r.sender_name)}</span>
        ${badge}
      </div>
      <p class="text-xs text-gray-600 dark:text-gray-400 whitespace-pre-line">${escapeHtml(r.message || '')}</p>
      <p class="text-[10px] text-gray-400 mt-1">${escapeHtml(r.created_at || '')}</p>
      ${actions}
    </div>`;
}

async function handleResponseAction(btn, modal) {
  const action = btn.dataset.responseAction;
  const responseId = btn.dataset.responseId;
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';

  try {
    const response = await fetch(`${baseUrl}api/swap-listing-responses`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action, response_id: responseId }),
    });
    const result = await response.json();

    if (result.success) {
      showToast(`Message ${action}ed.`, 'success');
      loadResponses(modal.dataset.activeEncodedId);
    } else {
      showToast(result.message || 'Could not update message.', 'error');
    }
  } catch (err) {
    console.error('Swap Marketplace response action error:', err);
    showToast('Unexpected error.', 'error');
  }
}

function escapeHtml(str) {
  return String(str ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

function statusBadgeHtml(status) {
  return status === 'completed'
    ? '<span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-800 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700">Completed</span>'
    : '<span class="inline-flex items-center rounded-full bg-green-50 dark:bg-green-900/20 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-green-600 dark:text-green-400 border border-green-100 dark:border-green-800/30">Active</span>';
}

// -------------------------------
// Pictures — sourced entirely from the card's data-photos JSON, no fetch.
// Owners can also reorder/remove photos here; adding new ones happens via
// the Edit modal's photo picker.
// -------------------------------

function renderPictures(pics, canManage = false) {
  const wrapper = document.getElementById('swap-pics-wrapper');
  const countEl = document.getElementById('view-swap-pics-count');

  countEl.textContent = `${pics.length}/12`;

  wrapper.innerHTML = pics
    .map(
      (pic) => `
      <div data-pic-tile data-pic-id="${pic.id}" class="relative rounded-lg overflow-hidden border border-gray-200 dark:border-gray-800 h-20 group">
        <img src="${pic.url}" data-img-src="${pic.url}" class="w-full h-full object-cover cursor-pointer">
        ${canManage && pics.length > 1 ? reorderButtonHtml() : ''}
        ${canManage ? `<button type="button" data-delete-pic="${pic.id}" title="Remove" class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs shadow opacity-0 group-hover:opacity-100 transition-opacity">&times;</button>` : ''}
      </div>`
    )
    .join('') || '<p class="col-span-4 text-xs text-gray-400 text-center py-4">No photos yet.</p>';
}

async function deletePhoto(btn, modal) {
  const confirmed = await confirmDialog('Remove this photo?', 'Remove', 'Cancel', 'bg-red-600 hover:bg-red-700');
  if (!confirmed) return;

  const baseUrl = window.APP_CONFIG?.baseUrl || '/';

  try {
    const response = await fetch(`${baseUrl}api/swap-listing-pic-delete`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ pic_id: btn.dataset.deletePic }),
    });
    const result = await response.json();

    if (result.success) {
      renderPictures(result.photos || [], true);
      updateCardInGrid(modal.dataset.activeEncodedId, result.cardHtml);
    } else {
      showToast(result.message || 'Could not remove photo.', 'error');
    }
  } catch (err) {
    console.error('Swap Marketplace photo delete error:', err);
    showToast('Unexpected error. Please try again.', 'error');
  }
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

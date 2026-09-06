// /resources/js/utils/listings/view-listing-modal.js
//
// The shared view-listing modal used across /listings and /my-listings:
// populates entirely from the clicked card's data-* attributes (no fetch
// needed to open it), then separately loads its picture grid and
// (owner-only) its Inquiries list. Ported from the legacy gonachi/
// platform's view-content-mapper.js + media-manager.js, combined into one
// module here. Legacy drives accept/decline through its Notification
// system; gonachi-home doesn't have one yet, so those actions are surfaced
// directly in the Inquiries list below instead. Property-specific sections
// are hidden entirely for "service" listings (category 2/3), matching
// legacy's isService split.

import { showToast } from '../../ui/toast.js';
import { confirmDialog } from '../../ui/confirm.js';
import { uploadModal, createUploadHandler } from '../../modals/upload-modal.js';
import { ViewCounter } from '../globals/view-counter.js';
import { openListingResponseModal } from '../../modals/listing-response-modal.js';
import { registerImagePreview } from '../globals/preview.js';

export function initViewListingModal() {
  const modal = document.getElementById('view-listing-modal');
  if (!modal || modal.dataset.initialized) return;
  modal.dataset.initialized = 'true';

  registerImagePreview();

  document.body.addEventListener('click', (e) => {
    const trigger = e.target.closest('.view-listing-trigger');
    if (trigger) openModal(trigger);
  });

  modal.querySelectorAll('.close-listing-modal').forEach((el) => el.addEventListener('click', closeModal));

  document.getElementById('listing-add-photo-btn')?.addEventListener('click', () => triggerPhotoUpload(modal));
  document.getElementById('listing-pics-wrapper')?.addEventListener('click', (e) => {
    const delBtn = e.target.closest('[data-delete-pic]');
    if (delBtn) deletePicture(delBtn, modal);
  });

  document.getElementById('view-listing-inquiries-list')?.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-response-action]');
    if (btn) handleInquiryAction(btn, modal);
  });

  document.getElementById('view-listing-primary-btn')?.addEventListener('click', () => handlePrimaryAction(modal));

  // Capture phase: the card's own edit/delete/deactivate buttons sit inside
  // wrappers with onclick="event.stopPropagation()" (so clicking them
  // doesn't also open the view modal underneath) — that stops the click
  // from ever reaching a bubble-phase document listener, so this one has to
  // run on the way down instead.
  document.addEventListener('click', (e) => {
    const deleteBtn = e.target.closest('.delete-listing-btn');
    if (deleteBtn) { deleteListing(deleteBtn); return; }

    const toggleBtn = e.target.closest('.deactivate-listing-trigger, .reactivate-listing-trigger');
    if (toggleBtn) { toggleStatus(toggleBtn); }
  }, true);
}

function closeModal() {
  document.getElementById('view-listing-modal').classList.add('hidden');
}

async function openModal(el) {
  const modal = document.getElementById('view-listing-modal');
  const d = el.dataset;

  modal.dataset.activeEncodedId = d.encodedId;
  modal.dataset.ownerId = d.ownerId;

  const categoryId = Number(d.categoryId);
  const isService = categoryId === 2 || categoryId === 3;

  document.getElementById('view-listing-title').textContent = d.listingTitle;
  document.getElementById('view-listing-category-sub').textContent = d.categoryTypeName ? `${d.categoryName} · ${d.categoryTypeName}` : d.categoryName;
  document.getElementById('view-listing-status-badge').innerHTML = statusBadgeHtml(d.statusId);
  document.getElementById('view-listing-description').textContent = d.listingDescription || '—';

  ['location', 'details', 'amenities', 'availability'].forEach((section) => {
    document.getElementById(`listing-section-${section}`)?.classList.toggle('hidden', isService);
  });

  document.getElementById('view-listing-address').textContent = d.address || '—';
  document.getElementById('view-listing-region').textContent = d.regionName || '—';
  document.getElementById('view-listing-country').textContent = d.countryName || '—';
  document.getElementById('view-listing-city').textContent = d.city || '—';

  document.getElementById('view-listing-unit-type').textContent = d.unitTypeName || '—';
  document.getElementById('view-listing-house-type').textContent = d.houseTypeName || '—';
  document.getElementById('view-listing-bedrooms').textContent = d.bedroomLabel || '0';
  document.getElementById('view-listing-bathrooms').textContent = d.bathroomLabel || '0';
  document.getElementById('view-listing-size').textContent = d.propertySize ? `${d.propertySize} sq ft` : '—';
  document.getElementById('view-listing-parking').textContent = Number(d.parking) === 1 ? 'Yes' : 'No';

  const amenitiesContainer = document.getElementById('view-listing-amenities-container');
  const amenitiesCollection = JSON.parse(d.amenitiesCollection || '[]');
  amenitiesContainer.innerHTML = amenitiesCollection.length
    ? amenitiesCollection
        .map((a) => `<span class="text-[10px] font-bold px-2 py-1 rounded-lg bg-teal-50 dark:bg-teal-950/30 text-teal-600 dark:text-teal-400 border border-teal-100 dark:border-teal-900/40">${escapeHtml(a.name)}</span>`)
        .join('')
    : '<p class="text-xs text-gray-400 italic">No specific amenities listed.</p>';

  document.getElementById('view-listing-is-ac').textContent = Number(d.isAc) === 1 ? 'Yes' : 'No';
  document.getElementById('view-listing-is-furnished').textContent = Number(d.isFurnished) === 1 ? 'Yes' : 'No';
  document.getElementById('view-listing-pets').textContent = Number(d.petsAllowed) === 1 ? 'Yes' : 'No';

  document.getElementById('view-listing-price').textContent = d.price || 'Contact for Price';
  document.getElementById('view-listing-agreement').textContent = d.agreementTypeName || 'N/A';
  document.getElementById('view-listing-move-in').textContent = d.moveInDate ? formatDate(d.moveInDate) : 'Available Now';

  const videoLink = document.getElementById('view-listing-video-link');
  if (d.youtubeUrl) {
    videoLink.classList.remove('hidden');
    videoLink.classList.add('flex');
    videoLink.href = d.youtubeUrl;
  } else {
    videoLink.classList.add('hidden');
    videoLink.classList.remove('flex');
  }
  document.getElementById('view-listing-phone').textContent = d.contactPhone || 'No phone provided';

  document.getElementById('view-listing-created').textContent = d.created;
  document.getElementById('view-listing-updated').textContent = d.updated;
  document.getElementById('view-listing-views-count').textContent = d.viewsCount;

  // Owner block (shared components/ui/modal-detail-owner.php markup)
  const avatarContainer = document.getElementById('view-listing-owner-avatar-container');
  const initialEl = document.getElementById('view-listing-owner-initial');
  if (d.ownerAvatar) {
    avatarContainer.innerHTML = `<img src="${d.ownerAvatar}" class="w-full h-full object-cover">`;
  } else {
    avatarContainer.innerHTML = '';
    avatarContainer.appendChild(initialEl);
    initialEl.textContent = d.ownerInitial;
  }
  document.getElementById('view-listing-owner-name').textContent = d.ownerName;
  document.getElementById('view-listing-owner-location').textContent = d.ownerLocation;

  const currentUserId = window.sessionUserId ? Number(window.sessionUserId) : null;
  const canManage = currentUserId !== null && Number(d.ownerId) === currentUserId;

  document.querySelectorAll('.listing-owner-only').forEach((el2) => el2.classList.toggle('hidden', !canManage));

  const primaryBtn = document.getElementById('view-listing-primary-btn');
  primaryBtn.classList.toggle('is-connect', !canManage);
  if (canManage) {
    primaryBtn.textContent = Number(d.statusId) === 2 ? 'Reactivate Listing' : 'End Listing';
  } else {
    primaryBtn.textContent = 'Contact Owner';
  }

  const inquiriesWrapper = document.getElementById('view-listing-inquiries-wrapper');
  if (canManage) {
    inquiriesWrapper.classList.remove('hidden');
    await loadInquiries(d.encodedId);
  } else {
    inquiriesWrapper.classList.add('hidden');
  }

  await loadPictures(d.encodedId, canManage);

  modal.classList.remove('hidden');
  ViewCounter.increment('listing', d.encodedId);
}

function handlePrimaryAction(modal) {
  const currentUserId = window.sessionUserId ? Number(window.sessionUserId) : null;
  const canManage = currentUserId !== null && Number(modal.dataset.ownerId) === currentUserId;

  if (canManage) {
    quickToggleStatus(modal);
    return;
  }

  closeModal();
  openListingResponseModal({
    encodedId: modal.dataset.activeEncodedId,
    ownerId: modal.dataset.ownerId,
    title: document.getElementById('view-listing-title').textContent,
  });
}

async function quickToggleStatus(modal) {
  const isArchived = document.getElementById('view-listing-primary-btn').textContent.includes('Reactivate');
  const confirmed = await confirmDialog(isArchived ? 'Reactivate this listing?' : 'End this listing?', 'Confirm', 'Cancel', 'bg-teal-600 hover:bg-teal-700');
  if (!confirmed) return;

  const baseUrl = window.APP_CONFIG?.baseUrl || '/';
  const encodedId = modal.dataset.activeEncodedId;

  try {
    const response = await fetch(`${baseUrl}api/listings`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ encoded_id: encodedId, intent: isArchived ? 'reactivate' : 'deactivate' }),
    });
    const result = await response.json();

    if (result.success) {
      const card = document.querySelector(`.listing-card-wrapper[data-encoded-id="${encodedId}"]`);
      if (card) card.outerHTML = result.cardHtml;
      showToast(isArchived ? 'Listing reactivated.' : 'Listing ended.', 'success');
      closeModal();
    } else {
      showToast(result.message || 'Could not update status.', 'error');
    }
  } catch (err) {
    console.error('Listing status update error:', err);
    showToast('Unexpected error.', 'error');
  }
}

function statusBadgeHtml(statusId) {
  return Number(statusId) === 2
    ? '<span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-800 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700">Archived</span>'
    : '<span class="inline-flex items-center rounded-full bg-green-50 dark:bg-green-900/20 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-green-600 dark:text-green-400 border border-green-100 dark:border-green-800/30">Active</span>';
}

function formatDate(iso) {
  const d = new Date(iso);
  if (isNaN(d.getTime())) return iso;
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

// -------------------------------
// Pictures
// -------------------------------

async function loadPictures(encodedId, canManage) {
  const wrapper = document.getElementById('listing-pics-wrapper');
  const countEl = document.getElementById('view-listing-pics-count');
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';

  wrapper.innerHTML = `<div class="col-span-4 flex justify-center py-4"><div class="animate-spin rounded-full h-5 w-5 border-2 border-teal-500 border-t-transparent"></div></div>`;

  try {
    const response = await fetch(`${baseUrl}api/listing-pictures?id=${encodeURIComponent(encodedId)}`);
    const result = await response.json();
    const pics = result.pictures || [];

    countEl.textContent = `${pics.length}/12`;

    wrapper.innerHTML = pics
      .map(
        (pic) => `
        <div class="relative rounded-lg overflow-hidden border border-gray-200 dark:border-gray-800 h-20 group">
          <img src="${pic.url}" data-img-src="${pic.url}" class="w-full h-full object-cover cursor-pointer">
          ${canManage ? `<button type="button" data-delete-pic="${pic.entry_id}" class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs shadow opacity-0 group-hover:opacity-100 transition-opacity">&times;</button>` : ''}
        </div>`
      )
      .join('') || '<p class="col-span-4 text-xs text-gray-400 text-center py-4">No pictures yet.</p>';
  } catch (err) {
    console.error('Load listing pictures error:', err);
    wrapper.innerHTML = '<p class="col-span-4 text-xs text-gray-400 text-center py-4">Couldn\'t load pictures.</p>';
  }
}

function triggerPhotoUpload(modal) {
  const encodedId = modal.dataset.activeEncodedId;
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';

  uploadModal.open();
  setTimeout(() => {
    createUploadHandler(
      `${baseUrl}api/listing-upload-pics?id=${encodeURIComponent(encodedId)}`,
      'listing-pics',
      () => {
        showToast('Picture(s) added.', 'success');
        loadPictures(encodedId, true);
      },
      6,
      true,
      { maxFiles: 12 }
    );
  }, 50);
}

async function deletePicture(btn, modal) {
  const confirmed = await confirmDialog('Remove this picture?', 'Remove', 'Cancel', 'bg-red-600 hover:bg-red-700');
  if (!confirmed) return;

  const baseUrl = window.APP_CONFIG?.baseUrl || '/';
  const entryId = btn.dataset.deletePic;

  try {
    const response = await fetch(`${baseUrl}api/listing-pic-delete`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ entry_id: entryId }),
    });
    const result = await response.json();

    if (result.success) {
      loadPictures(modal.dataset.activeEncodedId, true);
    } else {
      showToast(result.message || 'Could not remove picture.', 'error');
    }
  } catch (err) {
    console.error('Delete listing picture error:', err);
    showToast('Unexpected error.', 'error');
  }
}

// -------------------------------
// Inquiries (owner-only)
// -------------------------------

async function loadInquiries(encodedId) {
  const list = document.getElementById('view-listing-inquiries-list');
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';

  list.innerHTML = `<div class="flex justify-center py-3"><div class="animate-spin rounded-full h-5 w-5 border-2 border-teal-500 border-t-transparent"></div></div>`;

  try {
    const response = await fetch(`${baseUrl}api/listing-responses?id=${encodeURIComponent(encodedId)}`);
    const result = await response.json();
    const responses = result.responses || [];

    list.innerHTML = responses.map(renderInquiryRow).join('') || '<p class="text-xs text-gray-400">No inquiries yet.</p>';
  } catch (err) {
    console.error('Load listing inquiries error:', err);
    list.innerHTML = '<p class="text-xs text-gray-400">Couldn\'t load inquiries.</p>';
  }
}

function renderInquiryRow(r) {
  const statusMap = {
    pending: 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400 border-yellow-100 dark:border-yellow-800/30',
    accepted: 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 border-green-100 dark:border-green-800/30',
    declined: 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 border-red-100 dark:border-red-800/30',
  };
  const badge = `<span class="inline-flex items-center rounded-full px-2 py-0.5 text-[9px] font-black uppercase tracking-wider border ${statusMap[r.status] || statusMap.pending}">${r.status}</span>`;

  const actions = r.status === 'pending'
    ? `<div class="flex items-center gap-2 mt-2">
        <button type="button" data-response-action="accept" data-response-id="${r.id}" class="text-[11px] font-bold text-emerald-600 hover:text-emerald-700">Accept</button>
        <button type="button" data-response-action="decline" data-response-id="${r.id}" class="text-[11px] font-bold text-red-500 hover:text-red-600">Decline</button>
      </div>`
    : '';

  return `
    <div class="p-3 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30">
      <div class="flex items-center justify-between gap-2 mb-1">
        <span class="text-xs font-bold text-gray-800 dark:text-gray-200">${r.sender_name}</span>
        ${badge}
      </div>
      <p class="text-xs text-gray-600 dark:text-gray-400">${escapeHtml(r.message || '')}</p>
      <p class="text-[10px] text-gray-400 mt-1">${r.created_at || ''}</p>
      ${actions}
    </div>`;
}

async function handleInquiryAction(btn, modal) {
  const action = btn.dataset.responseAction;
  const responseId = btn.dataset.responseId;
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';

  try {
    const response = await fetch(`${baseUrl}api/listing-responses`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action, response_id: responseId }),
    });
    const result = await response.json();

    if (result.success) {
      showToast(`Inquiry ${action}ed.`, 'success');
      loadInquiries(modal.dataset.activeEncodedId);
    } else {
      showToast(result.message || 'Could not update inquiry.', 'error');
    }
  } catch (err) {
    console.error('Listing inquiry action error:', err);
    showToast('Unexpected error.', 'error');
  }
}

function escapeHtml(str) {
  return String(str ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

// -------------------------------
// Delete / status toggle (card buttons)
// -------------------------------

async function deleteListing(btn) {
  const confirmed = await confirmDialog('Delete this listing? This also removes all its pictures. This cannot be undone.', 'Delete', 'Cancel', 'bg-red-600 hover:bg-red-700');
  if (!confirmed) return;

  const encodedId = btn.dataset.encodedId;
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';
  const card = document.querySelector(`.listing-card-wrapper[data-encoded-id="${encodedId}"]`);

  try {
    const response = await fetch(`${baseUrl}api/listings`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ _method: 'DELETE', encoded_id: encodedId }),
    });
    const result = await response.json();

    if (result.success) {
      card?.remove();
      showToast('Listing deleted.', 'success');
    } else {
      showToast(result.message || 'Could not delete listing.', 'error');
    }
  } catch (err) {
    console.error('Delete listing error:', err);
    showToast('Unexpected error.', 'error');
  }
}

async function toggleStatus(btn) {
  const isReactivate = btn.classList.contains('reactivate-listing-trigger');
  const confirmed = await confirmDialog(isReactivate ? 'Reactivate this listing?' : 'End this listing?', 'Confirm', 'Cancel', 'bg-teal-600 hover:bg-teal-700');
  if (!confirmed) return;

  const encodedId = btn.dataset.encodedId;
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';

  try {
    const response = await fetch(`${baseUrl}api/listings`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ encoded_id: encodedId, intent: isReactivate ? 'reactivate' : 'deactivate' }),
    });
    const result = await response.json();

    if (result.success) {
      const card = document.querySelector(`.listing-card-wrapper[data-encoded-id="${encodedId}"]`);
      if (card) card.outerHTML = result.cardHtml;
      showToast(isReactivate ? 'Listing reactivated.' : 'Listing ended.', 'success');
      closeModal();
    } else {
      showToast(result.message || 'Could not update status.', 'error');
    }
  } catch (err) {
    console.error('Listing status update error:', err);
    showToast('Unexpected error.', 'error');
  }
}

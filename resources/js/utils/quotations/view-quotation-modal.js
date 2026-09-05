// /resources/js/utils/quotations/view-quotation-modal.js
//
// The shared view-quotation modal used across /quotations and
// /my-quotations: populates entirely from the clicked card's data-*
// attributes (no fetch needed to open it), then separately loads its
// picture grid and (owner-only) its bid Responses list. Ported from the
// legacy gonachi/ platform's view-content-mapper.js + media-manager.js,
// combined into one module here. Legacy drives accept/decline through its
// Notification system; gonachi-home doesn't have one yet, so those actions
// are surfaced directly in the Responses list below instead.

import { showToast } from '../../ui/toast.js';
import { confirmDialog } from '../../ui/confirm.js';
import { uploadModal, createUploadHandler } from '../../modals/upload-modal.js';
import { ViewCounter } from '../globals/view-counter.js';
import { openQuotationResponseModal } from '../../modals/quotation-response-modal.js';

export function initViewQuotationModal() {
  const modal = document.getElementById('view-quote-modal');
  if (!modal || modal.dataset.initialized) return;
  modal.dataset.initialized = 'true';

  document.body.addEventListener('click', (e) => {
    const trigger = e.target.closest('.view-quote-trigger');
    if (trigger) openModal(trigger);
  });

  modal.querySelectorAll('.close-quote-modal').forEach((el) => el.addEventListener('click', closeModal));

  document.getElementById('quote-add-photo-btn')?.addEventListener('click', () => triggerPhotoUpload(modal));
  document.getElementById('quote-pics-wrapper')?.addEventListener('click', (e) => {
    const delBtn = e.target.closest('[data-delete-pic]');
    if (delBtn) deletePicture(delBtn, modal);
  });

  document.getElementById('view-quote-responses-list')?.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-response-action]');
    if (btn) handleResponseAction(btn, modal);
  });

  document.getElementById('view-quote-primary-btn')?.addEventListener('click', () => handlePrimaryAction(modal));

  // Capture phase: the card's own edit/delete/deactivate buttons sit inside
  // wrappers with onclick="event.stopPropagation()" (so clicking them
  // doesn't also open the view modal underneath) — that stops the click
  // from ever reaching a bubble-phase document listener, so this one has to
  // run on the way down instead.
  document.addEventListener('click', (e) => {
    const deleteBtn = e.target.closest('.delete-quote-btn');
    if (deleteBtn) { deleteQuote(deleteBtn); return; }

    const toggleBtn = e.target.closest('.deactivate-quote-trigger, .reactivate-quote-trigger');
    if (toggleBtn) { toggleStatus(toggleBtn); }
  }, true);
}

function closeModal() {
  document.getElementById('view-quote-modal').classList.add('hidden');
}

async function openModal(el) {
  const modal = document.getElementById('view-quote-modal');
  const d = el.dataset;

  modal.dataset.activeEncodedId = d.encodedId;
  modal.dataset.ownerId = d.ownerId;

  document.getElementById('view-quote-title').textContent = d.title;
  document.getElementById('view-quote-trade').textContent = d.skilledTradeName || 'General';
  document.getElementById('view-quote-status-badge').innerHTML = statusBadgeHtml(d.statusId);
  document.getElementById('view-quote-description').textContent = d.description || '—';

  document.getElementById('view-quote-country').textContent = d.countryName || '—';
  document.getElementById('view-quote-region').textContent = d.regionName || '—';
  document.getElementById('view-quote-city').textContent = d.city || '—';

  document.getElementById('view-quote-contractor-type').textContent = d.contractorTypeName || '—';
  document.getElementById('view-quote-skilled-trade').textContent = d.skilledTradeName || '—';
  document.getElementById('view-quote-unit-type').textContent = d.unitTypeName || '—';

  const houseRow = document.getElementById('view-quote-house-type-row');
  if (Number(d.unitTypeId) === 5) {
    houseRow.classList.remove('hidden');
    document.getElementById('view-quote-house-type').textContent = d.houseTypeName || '—';
  } else {
    houseRow.classList.add('hidden');
  }

  document.getElementById('view-quote-timeline').textContent = `${d.startDate || 'TBD'} — ${d.finishDate || 'TBD'}`;
  document.getElementById('view-quote-hours').textContent = `${formatTime(d.startTime)} — ${formatTime(d.finishTime)}`;
  document.getElementById('view-quote-type').textContent = d.quotationTypeName || '—';
  document.getElementById('view-quote-budget').textContent = d.budget || 'Open';

  const videoLink = document.getElementById('view-quote-video-link');
  if (d.youtubeUrl) {
    videoLink.classList.remove('hidden');
    videoLink.classList.add('flex');
    videoLink.href = d.youtubeUrl;
  } else {
    videoLink.classList.add('hidden');
    videoLink.classList.remove('flex');
  }

  document.getElementById('view-quote-created').textContent = d.created;
  document.getElementById('view-quote-updated').textContent = d.updated;
  document.getElementById('view-quote-views-count').textContent = d.viewsCount;

  // Owner block (shared components/ui/modal-detail-owner.php markup)
  const avatarContainer = document.getElementById('view-quote-owner-avatar-container');
  const initialEl = document.getElementById('view-quote-owner-initial');
  if (d.ownerAvatar) {
    avatarContainer.innerHTML = `<img src="${d.ownerAvatar}" class="w-full h-full object-cover">`;
  } else {
    avatarContainer.innerHTML = '';
    avatarContainer.appendChild(initialEl);
    initialEl.textContent = d.ownerInitial;
  }
  document.getElementById('view-quote-owner-name').textContent = d.ownerName;
  document.getElementById('view-quote-owner-location').textContent = d.ownerLocation;

  const currentUserId = window.sessionUserId ? Number(window.sessionUserId) : null;
  const canManage = currentUserId !== null && Number(d.ownerId) === currentUserId;

  document.querySelectorAll('.quote-owner-only').forEach((el2) => el2.classList.toggle('hidden', !canManage));

  const primaryBtn = document.getElementById('view-quote-primary-btn');
  if (canManage) {
    primaryBtn.textContent = Number(d.statusId) === 2 ? 'Reactivate Quotation' : 'Deactivate Quotation';
  } else {
    primaryBtn.textContent = 'Connect with Owner';
  }

  const responsesWrapper = document.getElementById('view-quote-responses-wrapper');
  if (canManage) {
    responsesWrapper.classList.remove('hidden');
    await loadResponses(d.encodedId);
  } else {
    responsesWrapper.classList.add('hidden');
  }

  await loadPictures(d.encodedId, canManage);

  modal.classList.remove('hidden');
  ViewCounter.increment('quotation', d.encodedId);
}

function handlePrimaryAction(modal) {
  const currentUserId = window.sessionUserId ? Number(window.sessionUserId) : null;
  const canManage = currentUserId !== null && Number(modal.dataset.ownerId) === currentUserId;

  if (canManage) {
    quickToggleStatus(modal);
    return;
  }

  closeModal();
  openQuotationResponseModal({
    encodedId: modal.dataset.activeEncodedId,
    ownerId: modal.dataset.ownerId,
    title: document.getElementById('view-quote-title').textContent,
  });
}

async function quickToggleStatus(modal) {
  const isArchived = document.getElementById('view-quote-primary-btn').textContent.includes('Reactivate');
  const confirmed = await confirmDialog(isArchived ? 'Reactivate this quotation?' : 'Deactivate this quotation?', 'Confirm', 'Cancel', 'bg-teal-600 hover:bg-teal-700');
  if (!confirmed) return;

  const baseUrl = window.APP_CONFIG?.baseUrl || '/';
  const encodedId = modal.dataset.activeEncodedId;

  try {
    const response = await fetch(`${baseUrl}api/quotations`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ encoded_id: encodedId, intent: isArchived ? 'reactivate' : 'deactivate' }),
    });
    const result = await response.json();

    if (result.success) {
      const card = document.querySelector(`.quote-card-wrapper[data-encoded-id="${encodedId}"]`);
      if (card) card.outerHTML = result.cardHtml;
      showToast(isArchived ? 'Quotation reactivated.' : 'Quotation deactivated.', 'success');
      closeModal();
    } else {
      showToast(result.message || 'Could not update status.', 'error');
    }
  } catch (err) {
    console.error('Quotation status update error:', err);
    showToast('Unexpected error.', 'error');
  }
}

function statusBadgeHtml(statusId) {
  return Number(statusId) === 2
    ? '<span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-800 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700">Archived</span>'
    : '<span class="inline-flex items-center rounded-full bg-green-50 dark:bg-green-900/20 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-green-600 dark:text-green-400 border border-green-100 dark:border-green-800/30">Active</span>';
}

function formatTime(t) {
  if (!t) return 'N/A';
  const [h, m] = t.split(':');
  const hour = parseInt(h, 10);
  const period = hour >= 12 ? 'PM' : 'AM';
  const displayHour = hour % 12 === 0 ? 12 : hour % 12;
  return `${displayHour}:${m} ${period}`;
}

// -------------------------------
// Pictures
// -------------------------------

async function loadPictures(encodedId, canManage) {
  const wrapper = document.getElementById('quote-pics-wrapper');
  const countEl = document.getElementById('view-quote-pics-count');
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';

  wrapper.innerHTML = `<div class="col-span-4 flex justify-center py-4"><div class="animate-spin rounded-full h-5 w-5 border-2 border-teal-500 border-t-transparent"></div></div>`;

  try {
    const response = await fetch(`${baseUrl}api/quotation-pictures?id=${encodeURIComponent(encodedId)}`);
    const result = await response.json();
    const pics = result.pictures || [];

    countEl.textContent = `${pics.length}/12`;

    wrapper.innerHTML = pics
      .map(
        (pic) => `
        <div class="relative rounded-lg overflow-hidden border border-gray-200 dark:border-gray-800 h-20 group">
          <img src="${pic.url}" class="w-full h-full object-cover">
          ${canManage ? `<button type="button" data-delete-pic="${pic.entry_id}" class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs shadow opacity-0 group-hover:opacity-100 transition-opacity">&times;</button>` : ''}
        </div>`
      )
      .join('') || '<p class="col-span-4 text-xs text-gray-400 text-center py-4">No pictures yet.</p>';
  } catch (err) {
    console.error('Load quotation pictures error:', err);
    wrapper.innerHTML = '<p class="col-span-4 text-xs text-gray-400 text-center py-4">Couldn\'t load pictures.</p>';
  }
}

function triggerPhotoUpload(modal) {
  const encodedId = modal.dataset.activeEncodedId;
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';

  uploadModal.open();
  setTimeout(() => {
    createUploadHandler(
      `${baseUrl}api/quotation-upload-pics?id=${encodeURIComponent(encodedId)}`,
      'quotation-pics',
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
    const response = await fetch(`${baseUrl}api/quotation-pic-delete`, {
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
    console.error('Delete quotation picture error:', err);
    showToast('Unexpected error.', 'error');
  }
}

// -------------------------------
// Responses (bid handshake — owner-only)
// -------------------------------

async function loadResponses(encodedId) {
  const list = document.getElementById('view-quote-responses-list');
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';

  list.innerHTML = `<div class="flex justify-center py-3"><div class="animate-spin rounded-full h-5 w-5 border-2 border-teal-500 border-t-transparent"></div></div>`;

  try {
    const response = await fetch(`${baseUrl}api/quotation-responses?id=${encodeURIComponent(encodedId)}`);
    const result = await response.json();
    const responses = result.responses || [];

    list.innerHTML = responses.map(renderResponseRow).join('') || '<p class="text-xs text-gray-400">No responses yet.</p>';
  } catch (err) {
    console.error('Load quotation responses error:', err);
    list.innerHTML = '<p class="text-xs text-gray-400">Couldn\'t load responses.</p>';
  }
}

function renderResponseRow(r) {
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

async function handleResponseAction(btn, modal) {
  const action = btn.dataset.responseAction;
  const responseId = btn.dataset.responseId;
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';

  try {
    const response = await fetch(`${baseUrl}api/quotation-responses`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action, response_id: responseId }),
    });
    const result = await response.json();

    if (result.success) {
      showToast(`Response ${action}ed.`, 'success');
      loadResponses(modal.dataset.activeEncodedId);
    } else {
      showToast(result.message || 'Could not update response.', 'error');
    }
  } catch (err) {
    console.error('Quotation response action error:', err);
    showToast('Unexpected error.', 'error');
  }
}

function escapeHtml(str) {
  return String(str ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

// -------------------------------
// Delete / status toggle (card buttons)
// -------------------------------

async function deleteQuote(btn) {
  const confirmed = await confirmDialog('Delete this quotation? This also removes all its pictures. This cannot be undone.', 'Delete', 'Cancel', 'bg-red-600 hover:bg-red-700');
  if (!confirmed) return;

  const encodedId = btn.dataset.encodedId;
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';
  const card = document.querySelector(`.quote-card-wrapper[data-encoded-id="${encodedId}"]`);

  try {
    const response = await fetch(`${baseUrl}api/quotations`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ _method: 'DELETE', encoded_id: encodedId }),
    });
    const result = await response.json();

    if (result.success) {
      card?.remove();
      showToast('Quotation deleted.', 'success');
    } else {
      showToast(result.message || 'Could not delete quotation.', 'error');
    }
  } catch (err) {
    console.error('Delete quotation error:', err);
    showToast('Unexpected error.', 'error');
  }
}

async function toggleStatus(btn) {
  const isReactivate = btn.classList.contains('reactivate-quote-trigger');
  const confirmed = await confirmDialog(isReactivate ? 'Reactivate this quotation?' : 'Deactivate this quotation?', 'Confirm', 'Cancel', 'bg-teal-600 hover:bg-teal-700');
  if (!confirmed) return;

  const encodedId = btn.dataset.encodedId;
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';

  try {
    const response = await fetch(`${baseUrl}api/quotations`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ encoded_id: encodedId, intent: isReactivate ? 'reactivate' : 'deactivate' }),
    });
    const result = await response.json();

    if (result.success) {
      const card = document.querySelector(`.quote-card-wrapper[data-encoded-id="${encodedId}"]`);
      if (card) card.outerHTML = result.cardHtml;
      showToast(isReactivate ? 'Quotation reactivated.' : 'Quotation deactivated.', 'success');
      closeModal();
    } else {
      showToast(result.message || 'Could not update status.', 'error');
    }
  } catch (err) {
    console.error('Quotation status update error:', err);
    showToast('Unexpected error.', 'error');
  }
}

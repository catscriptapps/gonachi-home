// /resources/js/utils/mentors/view-mentor-modal.js
//
// The shared view-mentor modal used across the /mentors directory:
// populates entirely from the clicked card's data-* attributes (no fetch
// needed to open it), then separately loads its (owner-only) Requests
// list. Ported from the legacy gonachi/ platform's view-content-mapper.js,
// combined into one module here. Legacy drives accept/decline through its
// Notification system; gonachi-home doesn't have one yet, so those actions
// are surfaced directly in the Requests list below instead, and the
// mentor's optional reply is saved on the request row itself.

import { showToast } from '../../ui/toast.js';
import { confirmDialog } from '../../ui/confirm.js';
import { openMentorRequestModal } from '../../modals/mentor-request-modal.js';

export function initViewMentorModal() {
  const modal = document.getElementById('view-mentor-modal');
  if (!modal || modal.dataset.initialized) return;
  modal.dataset.initialized = 'true';

  document.body.addEventListener('click', (e) => {
    const trigger = e.target.closest('.view-mentor-trigger');
    if (trigger) openModal(trigger);
  });

  modal.querySelectorAll('.close-mentor-modal').forEach((el) => el.addEventListener('click', closeModal));

  document.getElementById('view-mentor-requests-list')?.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-request-action]');
    if (btn) handleRequestAction(btn, modal);
  });

  document.getElementById('view-mentor-primary-btn')?.addEventListener('click', () => handlePrimaryAction(modal));

  // Capture phase: the card's own delete button sits inside a wrapper with
  // onclick="event.stopPropagation()" (so clicking it doesn't also open the
  // view modal underneath) — that stops the click from ever reaching a
  // bubble-phase document listener, so this one has to run on the way down.
  document.addEventListener('click', (e) => {
    const deleteBtn = e.target.closest('.delete-mentor-btn');
    if (deleteBtn) deleteMentor(deleteBtn);
  }, true);
}

function closeModal() {
  document.getElementById('view-mentor-modal').classList.add('hidden');
}

async function openModal(el) {
  const modal = document.getElementById('view-mentor-modal');
  const d = el.dataset;

  modal.dataset.activeEncodedId = d.encodedId;
  modal.dataset.mentorId = d.id;
  modal.dataset.ownerId = d.ownerId;

  document.getElementById('view-mentor-name').textContent = d.ownerName;
  document.getElementById('view-mentor-headline').textContent = d.headline;
  document.getElementById('view-mentor-type-badge').textContent = d.targetUserType || 'Expert';
  document.getElementById('view-mentor-bio').textContent = d.bio || '—';

  document.getElementById('view-mentor-country').textContent = d.countryName || '—';
  document.getElementById('view-mentor-region').textContent = d.regionName || '—';
  document.getElementById('view-mentor-city').textContent = d.city || '—';

  document.getElementById('view-mentor-created').textContent = d.created;
  document.getElementById('view-mentor-updated').textContent = d.updated;

  const avatarImg = document.getElementById('view-mentor-avatar');
  const initialEl = document.getElementById('view-mentor-initial');
  if (d.ownerAvatar) {
    avatarImg.src = d.ownerAvatar;
    avatarImg.classList.remove('hidden');
    initialEl.classList.add('hidden');
  } else {
    avatarImg.classList.add('hidden');
    initialEl.classList.remove('hidden');
    initialEl.textContent = d.ownerInitial;
  }

  const youtubeLink = document.getElementById('view-mentor-youtube-link');
  if (d.youtubeUrl) {
    youtubeLink.href = d.youtubeUrl;
    youtubeLink.classList.remove('hidden');
    youtubeLink.classList.add('flex');
  } else {
    youtubeLink.classList.add('hidden');
    youtubeLink.classList.remove('flex');
  }

  const websiteLink = document.getElementById('view-mentor-website-link');
  if (d.websiteUrl) {
    websiteLink.href = d.websiteUrl;
    websiteLink.classList.remove('hidden');
    websiteLink.classList.add('flex');
  } else {
    websiteLink.classList.add('hidden');
    websiteLink.classList.remove('flex');
  }

  renderChips('view-mentor-skills-container', safeParse(d.skillsJson));

  document.getElementById('view-mentor-owner-name').textContent = `${d.experienceYears || 0}+ Years Experience`;
  document.getElementById('view-mentor-owner-location').textContent = d.ownerLocation;

  const detailAvatarContainer = document.getElementById('view-mentor-owner-avatar-container');
  const detailInitialEl = document.getElementById('view-mentor-owner-initial');
  if (d.ownerAvatar) {
    detailAvatarContainer.innerHTML = `<img src="${d.ownerAvatar}" class="w-full h-full object-cover">`;
  } else {
    detailAvatarContainer.innerHTML = '';
    detailAvatarContainer.appendChild(detailInitialEl);
    detailInitialEl.textContent = d.ownerInitial;
  }

  const currentUserId = window.sessionUserId ? Number(window.sessionUserId) : null;
  const canManage = currentUserId !== null && Number(d.ownerId) === currentUserId;

  document.querySelectorAll('.mentor-owner-only').forEach((el2) => el2.classList.toggle('hidden', !canManage));

  const primaryBtn = document.getElementById('view-mentor-primary-btn');
  if (canManage) {
    primaryBtn.textContent = 'Your Profile';
    primaryBtn.disabled = true;
  } else {
    primaryBtn.textContent = 'Message Mentor';
    primaryBtn.disabled = false;
  }

  const requestsWrapper = document.getElementById('view-mentor-requests-wrapper');
  if (canManage) {
    requestsWrapper.classList.remove('hidden');
    await loadRequests(d.encodedId);
  } else {
    requestsWrapper.classList.add('hidden');
  }

  modal.classList.remove('hidden');
}

function handlePrimaryAction(modal) {
  const currentUserId = window.sessionUserId ? Number(window.sessionUserId) : null;
  const canManage = currentUserId !== null && Number(modal.dataset.ownerId) === currentUserId;
  if (canManage) return;

  closeModal();
  openMentorRequestModal({
    encodedId: modal.dataset.activeEncodedId,
    mentorId: modal.dataset.mentorId,
    ownerId: modal.dataset.ownerId,
    ownerName: document.getElementById('view-mentor-name').textContent,
    targetUserType: document.getElementById('view-mentor-type-badge').textContent,
  });
}

function renderChips(containerId, items) {
  const container = document.getElementById(containerId);
  if (!items?.length) {
    container.innerHTML = '<span class="text-xs text-gray-400">—</span>';
    return;
  }
  container.innerHTML = items
    .map((skill) => `<span class="text-[10px] font-bold uppercase tracking-wide px-2 py-1 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300">#${skill}</span>`)
    .join('');
}

function safeParse(json) {
  try {
    return JSON.parse(json || '[]');
  } catch {
    return [];
  }
}

// -------------------------------
// Requests (mentorship handshake — owner-only)
// -------------------------------

async function loadRequests(encodedMentorId) {
  const list = document.getElementById('view-mentor-requests-list');
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';

  list.innerHTML = `<div class="flex justify-center py-3"><div class="animate-spin rounded-full h-5 w-5 border-2 border-teal-500 border-t-transparent"></div></div>`;

  try {
    const response = await fetch(`${baseUrl}api/mentor-requests?id=${encodeURIComponent(encodedMentorId)}`);
    const result = await response.json();
    const requests = result.requests || [];

    list.innerHTML = requests.map(renderRequestRow).join('') || '<p class="text-xs text-gray-400">No requests yet.</p>';
  } catch (err) {
    console.error('Load mentor requests error:', err);
    list.innerHTML = '<p class="text-xs text-gray-400">Couldn\'t load requests.</p>';
  }
}

function renderRequestRow(r) {
  const statusMap = {
    pending: 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400 border-yellow-100 dark:border-yellow-800/30',
    accepted: 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 border-green-100 dark:border-green-800/30',
    declined: 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 border-red-100 dark:border-red-800/30',
  };
  const badge = `<span class="inline-flex items-center rounded-full px-2 py-0.5 text-[9px] font-black uppercase tracking-wider border ${statusMap[r.status] || statusMap.pending}">${r.status}</span>`;

  const actions = r.status === 'pending'
    ? `<div class="mt-2 space-y-2">
        <textarea data-response-input placeholder="Optional reply to include..." rows="2" class="w-full text-xs rounded-lg border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 py-1.5 px-2 focus:border-teal-500 focus:ring-teal-500 resize-none"></textarea>
        <div class="flex items-center gap-2">
          <button type="button" data-request-action="accept" data-request-id="${r.id}" class="text-[11px] font-bold text-emerald-600 hover:text-emerald-700">Accept</button>
          <button type="button" data-request-action="decline" data-request-id="${r.id}" class="text-[11px] font-bold text-red-500 hover:text-red-600">Decline</button>
        </div>
      </div>`
    : (r.response_message ? `<p class="text-[10px] text-gray-400 mt-1 italic">Your reply: "${escapeHtml(r.response_message)}"</p>` : '');

  return `
    <div class="p-3 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30" data-request-row="${r.id}">
      <div class="flex items-center justify-between gap-2 mb-1">
        <span class="text-xs font-bold text-gray-800 dark:text-gray-200">${r.sender_name}</span>
        ${badge}
      </div>
      <p class="text-xs text-gray-600 dark:text-gray-400">${escapeHtml(r.message || '')}</p>
      <p class="text-[10px] text-gray-400 mt-1">${r.created_at || ''}</p>
      ${actions}
    </div>`;
}

async function handleRequestAction(btn, modal) {
  const action = btn.dataset.requestAction;
  const requestId = btn.dataset.requestId;
  const row = btn.closest('[data-request-row]');
  const responseMessage = row?.querySelector('[data-response-input]')?.value.trim() || '';
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';

  try {
    const response = await fetch(`${baseUrl}api/mentor-requests`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action, request_id: requestId, message: responseMessage }),
    });
    const result = await response.json();

    if (result.success) {
      showToast(`Request ${action}ed.`, 'success');
      loadRequests(modal.dataset.activeEncodedId);
    } else {
      showToast(result.message || 'Could not update request.', 'error');
    }
  } catch (err) {
    console.error('Mentor request action error:', err);
    showToast('Unexpected error.', 'error');
  }
}

function escapeHtml(str) {
  return String(str ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

// -------------------------------
// Delete (card button)
// -------------------------------

async function deleteMentor(btn) {
  const confirmed = await confirmDialog('Delete this mentor profile? This cannot be undone.', 'Delete', 'Cancel', 'bg-red-600 hover:bg-red-700');
  if (!confirmed) return;

  const encodedId = btn.dataset.encodedId;
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';
  const card = document.querySelector(`.mentor-card-wrapper[data-encoded-id="${encodedId}"]`);

  try {
    const response = await fetch(`${baseUrl}api/mentors`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ _method: 'DELETE', encoded_id: encodedId }),
    });
    const result = await response.json();

    if (result.success) {
      card?.remove();
      showToast('Mentor profile deleted.', 'success');
    } else {
      showToast(result.message || 'Could not delete profile.', 'error');
    }
  } catch (err) {
    console.error('Delete mentor error:', err);
    showToast('Unexpected error.', 'error');
  }
}

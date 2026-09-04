// /resources/js/utils/adverts/view-advert-modal.js
//
// The shared view-advert modal used across /adverts, /my-adverts, and
// /adverts-admin: populates entirely from the clicked card/row's data-*
// attributes (no fetch needed to open it), then separately loads its
// picture grid. Owner-only edit/upload/delete controls; admin-only
// approve/deactivate/reject controls when on /adverts-admin. Ported from
// the legacy gonachi/ platform's view-content-mapper.js + admin-actions.js
// + media-manager.js, combined into one module here.

import { showToast } from '../../ui/toast.js';
import { confirmDialog } from '../../ui/confirm.js';
import { uploadModal, createUploadHandler } from '../../modals/upload-modal.js';
import { videoUploadModal, createVideoUploadHandler } from '../../modals/video-upload-modal.js';
import { ViewCounter } from '../globals/view-counter.js';

// Full literal class strings (not built from interpolated fragments) so
// Tailwind's content scanner can actually find and keep them at build time.
const PACKAGE_ICON_BOX_CLASSES = {
  1: 'w-11 h-11 rounded-xl bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 flex items-center justify-center flex-shrink-0', // Free
  2: 'w-11 h-11 rounded-xl bg-secondary-50 dark:bg-secondary-950/40 text-secondary-600 dark:text-secondary-400 flex items-center justify-center flex-shrink-0', // Standard
  3: 'w-11 h-11 rounded-xl bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 flex items-center justify-center flex-shrink-0', // Pro
  4: 'w-11 h-11 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0', // Business
  5: 'w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0', // Ultimate
};

export function initViewAdvertModal() {
  const modal = document.getElementById('view-ad-modal');
  if (!modal || modal.dataset.initialized) return;
  modal.dataset.initialized = 'true';

  const isAdminPage = !!document.getElementById('adverts-administration');

  document.body.addEventListener('click', (e) => {
    // Ignore clicks on buttons that have their own handling (edit, delete,
    // options menus) — those call e.stopPropagation() in the card markup.
    const trigger = e.target.closest('.view-ad-trigger');
    if (trigger) openModal(trigger, isAdminPage);
  });

  modal.querySelectorAll('.close-ad-modal').forEach((el) => el.addEventListener('click', closeModal));

  document.getElementById('ad-add-photo-btn')?.addEventListener('click', () => triggerPhotoUpload(modal));
  document.getElementById('ad-pics-wrapper')?.addEventListener('click', (e) => {
    const delBtn = e.target.closest('[data-delete-pic]');
    if (delBtn) deletePicture(delBtn, modal);
  });

  document.getElementById('ad-add-video-btn')?.addEventListener('click', () => triggerVideoUpload(modal));
  document.getElementById('ad-remove-video-btn')?.addEventListener('click', () => removeVideo(modal));

  document.getElementById('admin-approve-ad-btn')?.addEventListener('click', () => setAdvertStatus(modal, 'active', 'Approve this advert?'));
  document.getElementById('admin-deactivate-ad-btn')?.addEventListener('click', () => setAdvertStatus(modal, 'inactive', 'Deactivate this advert?'));
  document.getElementById('admin-reject-ad-btn')?.addEventListener('click', () => setAdvertStatus(modal, 'rejected', 'Reject this advert?'));

  document.addEventListener('click', (e) => {
    const delBtn = e.target.closest('.delete-ad-btn');
    if (delBtn) deleteAdvert(delBtn);
  });
}

function closeModal() {
  document.getElementById('view-ad-modal').classList.add('hidden');
}

async function openModal(el, isAdminPage) {
  const modal = document.getElementById('view-ad-modal');
  const d = el.dataset;

  modal.dataset.activeEncodedId = d.encodedId;

  document.getElementById('view-ad-title').textContent = d.title;
  document.getElementById('view-ad-package-name').textContent = d.advertPackageName;
  document.getElementById('view-ad-package-description').textContent = d.advertPackageDescription;
  document.getElementById('view-ad-package-icon-path').setAttribute('d', d.advertPackageIcon || '');
  applyPackageColor(d.advertPackage);

  document.getElementById('view-ad-status-badge').innerHTML = statusBadgeHtml(d.status);
  document.getElementById('view-ad-description').textContent = d.description;
  document.getElementById('view-ad-keywords').textContent = d.keywords || '—';

  document.getElementById('view-ad-created').textContent = d.joined;
  document.getElementById('view-ad-updated').textContent = d.updated;
  document.getElementById('view-ad-views-count').textContent = d.viewsCount;

  renderChips('view-ad-countries', safeParse(d.countryNames));
  renderChips('view-ad-user-types', safeParse(d.userTypeNames));

  const link = document.getElementById('view-ad-landing-link');
  if (d.landingPageUrl) {
    link.classList.remove('hidden');
    link.classList.add('flex');
    link.href = d.landingPageUrl;
    document.getElementById('view-ad-landing-url').textContent = d.landingPageUrl;
    document.getElementById('view-ad-cta-label').textContent = d.callToAction;
  } else {
    link.classList.add('hidden');
    link.classList.remove('flex');
  }

  // Owner block (shared components/ui/modal-detail-owner.php markup)
  const avatarContainer = document.getElementById('view-ad-owner-avatar-container');
  const initialEl = document.getElementById('view-ad-owner-initial');
  if (d.ownerAvatar) {
    avatarContainer.innerHTML = `<img src="${d.ownerAvatar}" class="w-full h-full object-cover">`;
  } else {
    avatarContainer.innerHTML = '';
    avatarContainer.appendChild(initialEl);
    initialEl.textContent = d.ownerInitial;
  }
  document.getElementById('view-ad-owner-name').textContent = d.ownerName;
  document.getElementById('view-ad-owner-location').textContent = d.ownerLocation;

  // canManage: the ad's own owner (never true when opened from an admin
  // notification/context — not applicable here since we always open from a
  // real card/row) — admin mode takes over entirely on the admin page.
  const currentUserId = window.sessionUserId ? Number(window.sessionUserId) : null;
  const canManage = !isAdminPage && currentUserId !== null && Number(d.ownerId) === currentUserId;

  document.querySelectorAll('.ad-owner-only').forEach((el2) => el2.classList.toggle('hidden', !canManage));

  const adminActions = document.getElementById('view-ad-admin-actions');
  if (isAdminPage) {
    adminActions.classList.remove('hidden');
    toggleAdminButtonsForStatus(d.status);
  } else {
    adminActions.classList.add('hidden');
  }

  await loadPictures(d.encodedId, canManage);
  renderVideo(modal, d.videoUrl || '', canManage);

  modal.classList.remove('hidden');
  ViewCounter.increment('ad', d.encodedId);
}

function toggleAdminButtonsForStatus(status) {
  const approve = document.getElementById('admin-approve-ad-btn');
  const deactivate = document.getElementById('admin-deactivate-ad-btn');
  const reject = document.getElementById('admin-reject-ad-btn');

  approve.classList.toggle('hidden', status === 'active');
  deactivate.classList.toggle('hidden', status !== 'active');
  reject.classList.toggle('hidden', status === 'rejected');
}

function applyPackageColor(packageId) {
  const box = document.getElementById('view-ad-package-icon');
  box.className = PACKAGE_ICON_BOX_CLASSES[packageId] || PACKAGE_ICON_BOX_CLASSES[1];
}

function statusBadgeHtml(status) {
  const map = {
    active: 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 border-green-100 dark:border-green-800/30|Active',
    pending: 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400 border-yellow-100 dark:border-yellow-800/30|Pending',
    inactive: 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 border-red-100 dark:border-red-800/30|Expired',
    rejected: 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700|Rejected',
  };
  const [classes, label] = (map[status] || 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 border-gray-200 dark:border-gray-700|Archived').split('|');
  return `<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider border ${classes}">${label}</span>`;
}

function renderChips(containerId, items) {
  const container = document.getElementById(containerId);
  if (!items?.length) {
    container.innerHTML = '<span class="text-xs text-gray-400">—</span>';
    return;
  }
  container.innerHTML = items
    .map((name) => `<span class="text-[10px] font-bold uppercase tracking-wide px-2 py-1 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300">${name}</span>`)
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
// Media management
// -------------------------------

async function loadPictures(encodedId, canManage) {
  const wrapper = document.getElementById('ad-pics-wrapper');
  const countEl = document.getElementById('view-ad-pics-count');
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';

  wrapper.innerHTML = `<div class="col-span-4 flex justify-center py-4"><div class="animate-spin rounded-full h-5 w-5 border-2 border-teal-500 border-t-transparent"></div></div>`;

  try {
    const response = await fetch(`${baseUrl}api/advert-pictures?id=${encodeURIComponent(encodedId)}`);
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
    console.error('Load advert pictures error:', err);
    wrapper.innerHTML = '<p class="col-span-4 text-xs text-gray-400 text-center py-4">Couldn\'t load pictures.</p>';
  }
}

function triggerPhotoUpload(modal) {
  const encodedId = modal.dataset.activeEncodedId;
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';

  uploadModal.open();
  setTimeout(() => {
    createUploadHandler(
      `${baseUrl}api/advert-upload-pics?id=${encodeURIComponent(encodedId)}`,
      'advert-pics',
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
    const response = await fetch(`${baseUrl}api/advert-pic-delete`, {
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
    console.error('Delete advert picture error:', err);
    showToast('Unexpected error.', 'error');
  }
}

// -------------------------------
// Video (owner-only, max 1 — same cap enforced by AdvertsController::attachVideo()
// always replacing whichever video already exists)
// -------------------------------

function renderVideo(modal, videoUrl, canManage) {
  const wrapper = document.getElementById('ad-video-wrapper');
  const addBtn = document.getElementById('ad-add-video-btn');
  const removeBtn = document.getElementById('ad-remove-video-btn');

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
    createVideoUploadHandler(`${baseUrl}api/advert-upload-video?id=${encodeURIComponent(encodedId)}`, (files) => {
      const url = files[0]?.url;
      if (url) {
        showToast('Video added.', 'success');
        renderVideo(modal, url, true);
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
    const response = await fetch(`${baseUrl}api/advert-video-delete`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: encodedId }),
    });
    const result = await response.json();

    if (result.success) {
      renderVideo(modal, '', true);
      showToast('Video removed.', 'success');
    } else {
      showToast(result.message || 'Could not remove video.', 'error');
    }
  } catch (err) {
    console.error('Remove advert video error:', err);
    showToast('Unexpected error.', 'error');
  }
}

// -------------------------------
// Delete advert (owner)
// -------------------------------

async function deleteAdvert(btn) {
  const confirmed = await confirmDialog('Delete this advert? This also removes all its pictures. This cannot be undone.', 'Delete', 'Cancel', 'bg-red-600 hover:bg-red-700');
  if (!confirmed) return;

  const encodedId = btn.dataset.encodedId;
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';
  const card = document.querySelector(`.ad-card-wrapper[data-encoded-id="${encodedId}"]`);

  try {
    const response = await fetch(`${baseUrl}api/adverts`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ _method: 'DELETE', encoded_id: encodedId }),
    });
    const result = await response.json();

    if (result.success) {
      card?.remove();
      showToast('Advert deleted.', 'success');
    } else {
      showToast(result.message || 'Could not delete advert.', 'error');
    }
  } catch (err) {
    console.error('Delete advert error:', err);
    showToast('Unexpected error.', 'error');
  }
}

// -------------------------------
// Admin approve/deactivate/reject
// -------------------------------

async function setAdvertStatus(modal, status, confirmMessage) {
  const confirmed = await confirmDialog(confirmMessage, 'Confirm', 'Cancel', 'bg-teal-600 hover:bg-teal-700');
  if (!confirmed) return;

  const encodedId = modal.dataset.activeEncodedId;
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';

  try {
    const response = await fetch(`${baseUrl}api/advert-status`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: encodedId, status }),
    });
    const result = await response.json();

    if (result.success) {
      const row = document.getElementById(`ad-row-${encodedId}`);
      if (row) row.outerHTML = result.rowHtml;
      showToast(`Advert marked ${status}.`, 'success');
      closeModal();
    } else {
      showToast(result.message || 'Could not update status.', 'error');
    }
  } catch (err) {
    console.error('Advert status update error:', err);
    showToast('Unexpected error.', 'error');
  }
}

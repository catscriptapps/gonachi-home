// /resources/js/pages/contractor-page.js
//
// Contractor profile detail page (/contractor/{id}) logic: wires "Claim
// This Profile" and, for a verified profile's own owner, "Copy Profile
// Link" (see contractor/detail.php — lets them share their listing on
// other platforms). Matched via app.js's segment-reversed page-manifest
// lookup (the last URL segment is the numeric id, so the "contractor"
// segment is what resolves to this module).
//
// Exported `init()` is called by app.js on full load and after partial-load
// navigation (see spa-router.js).

import { wireContractorClaimButtons } from '../utils/contractor-claim.js';
import { showToast } from '../ui/toast.js';
import { uploadModal, createUploadHandler } from '../modals/upload-modal.js';
import { registerImagePreview } from '../utils/globals/preview.js';
import { confirmDialog } from '../ui/confirm.js';

/**
 * navigator.clipboard only exists in a "secure context" — https, or
 * literally the hostname "localhost". This app is commonly reached over a
 * plain-http LAN address (e.g. http://10.0.0.x:8013), which the browser
 * does NOT consider secure, so the Clipboard API is undefined there and a
 * bare navigator.clipboard.writeText() call throws immediately. This falls
 * back to the old-but-universal document.execCommand('copy') technique
 * (a temporary, invisible, selected textarea) in that case.
 */
async function copyToClipboard(text) {
  if (window.isSecureContext && navigator.clipboard) {
    try {
      await navigator.clipboard.writeText(text);
      return true;
    } catch (err) {
      console.warn('navigator.clipboard.writeText failed, falling back:', err);
    }
  }

  try {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.focus();
    textarea.select();
    const success = document.execCommand('copy');
    document.body.removeChild(textarea);
    return success;
  } catch (err) {
    console.error('execCommand copy fallback failed:', err);
    return false;
  }
}

function wireCopyProfileLink() {
  const btn = document.getElementById('copy-profile-link-btn');
  if (!btn) return;

  btn.addEventListener('click', async () => {
    const url = btn.dataset.profileUrl;
    if (!url) return;

    const copied = await copyToClipboard(url);
    showToast(
      copied ? 'Profile link copied!' : 'Could not copy the link — please copy it manually.',
      copied ? 'success' : 'error'
    );
  });
}

/**
 * Lets a claimed contractor's owner replace their generated-initials
 * placeholder with a real photo — single-file replace, routed through the
 * shared upload modal (client-side compression) exactly like Building
 * Pictures elsewhere, except the target contractor is identified via
 * ?contractor_id= on the endpoint URL (this upload replaces an existing
 * record in place rather than staging photos for a not-yet-submitted form).
 */
function wireAvatarUpload() {
  const btn = document.getElementById('contractor-avatar-change-btn');
  if (!btn) return;

  btn.addEventListener('click', () => {
    const contractorId = btn.dataset.contractorId;
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';

    uploadModal.open();
    setTimeout(() => {
      createUploadHandler(
        `${baseUrl}api/contractor-avatar-upload?contractor_id=${contractorId}`,
        'contractor-avatar',
        (files) => {
          const url = files?.[0]?.url;
          if (!url) return;

          const wrapper = document.getElementById(`contractor-avatar-${contractorId}`);
          if (wrapper) {
            wrapper.style.background = '';
            wrapper.classList.add('cursor-zoom-in');
            wrapper.setAttribute('data-img-src', url);
            wrapper.innerHTML = `<img src="${url}" alt="Profile photo" class="w-full h-full object-cover pointer-events-none" />`;
          }
          showToast('Profile photo updated.', 'success');

          // A photo now exists where there wasn't one before — the delete
          // button only renders server-side when avatar_url is already set,
          // so a full refresh is the simplest way to reveal it without
          // duplicating that button's markup here.
          if (window.loadPartial) {
            window.loadPartial(window.location.pathname, false);
          }
        },
        1,
        true,
        { single: true, maxFiles: 1 }
      );
    }, 50);
  });
}

/**
 * Admin-only: removes a contractor's uploaded photo, reverting their card
 * back to the generated initials placeholder. Uses the app's custom confirm
 * dialog (never a native confirm()) before deleting.
 */
function wireAvatarDelete() {
  const btn = document.getElementById('contractor-avatar-delete-btn');
  if (!btn) return;

  btn.addEventListener('click', async () => {
    const contractorId = btn.dataset.contractorId;
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';

    const confirmed = await confirmDialog(
      'Remove this contractor\'s profile photo? It will revert to the generated initials placeholder.',
      'Remove Photo',
      'Cancel',
      'bg-red-600 hover:bg-red-700'
    );
    if (!confirmed) return;

    try {
      const response = await fetch(`${baseUrl}api/contractor-avatar-delete`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ contractor_id: contractorId }),
      });
      const result = await response.json();

      if (result.success) {
        showToast(result.message || 'Photo removed.', 'success');
        if (window.loadPartial) {
          window.loadPartial(window.location.pathname, false);
        }
      } else {
        showToast(result.message || 'Could not remove the photo.', 'error');
      }
    } catch (err) {
      console.error('Contractor avatar delete error:', err);
      showToast('Unexpected error removing the photo.', 'error');
    }
  });
}

export function init() {
  wireContractorClaimButtons();
  wireCopyProfileLink();
  wireAvatarUpload();
  wireAvatarDelete();
  registerImagePreview();
}

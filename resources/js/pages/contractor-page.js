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

export function init() {
  wireContractorClaimButtons();
  wireCopyProfileLink();
}

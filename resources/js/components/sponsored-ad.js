// /resources/js/components/sponsored-ad.js
//
// Populates the "Sponsored Advertisement" slot present in layouts/app.php
// and layouts/contractor-app.php with a real, targeting-matched active
// advert from the Adverts module (server/api/sponsored-ad.php ->
// Src\Controller\AdvertsController::sponsoredFor()) — package tier weights
// which ad gets picked. Hides the whole card when nothing is available
// (logged out, or no advert currently targets this viewer). Called once on
// initial load and again after every SPA partial navigation so the slot
// behaves like a real ad placement (a fresh impression per page view).

import { confirmDialog } from '../ui/confirm.js';
import { showToast } from '../ui/toast.js';

let currentAdEncodedId = null;
let reportLinkWired = false;

export async function initSponsoredAd() {
  const feed = document.getElementById('gonachi-ad-feed');
  const slot = document.getElementById('ad-placement-slot-1');
  if (!feed || !slot) return;

  wireReportLink(feed);

  try {
    const res = await fetch(`${window.APP_CONFIG.baseUrl}api/sponsored-ad`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });
    const data = await res.json();
    const ad = data?.ad;

    if (!ad) {
      currentAdEncodedId = null;
      feed.classList.add('hidden');
      return;
    }

    currentAdEncodedId = ad.encoded_id;
    slot.innerHTML = adHtml(ad);
    feed.classList.remove('hidden');
    trackImpression(ad.encoded_id);
  } catch (err) {
    feed.classList.add('hidden');
  }
}

function adHtml(ad) {
  const assetBase = window.APP_CONFIG.assetBase || '/';
  const thumb = ad.thumbnail ? `${assetBase}images/uploads/adverts/${encodeURIComponent(ad.thumbnail)}` : null;

  let landingUrl = (ad.landing_page_url || '').trim();
  if (landingUrl && !/^https?:\/\//i.test(landingUrl)) landingUrl = `https://${landingUrl}`;

  return `
    <a href="${landingUrl ? escapeHtml(landingUrl) : '#'}" ${landingUrl ? 'target="_blank" rel="noopener noreferrer"' : 'onclick="return false"'}
       class="flex items-center gap-4 w-full h-full p-3 group">
      ${thumb ? `<img src="${thumb}" alt="" class="h-16 w-16 rounded-lg object-cover flex-shrink-0 bg-white">` : ''}
      <div class="flex-1 min-w-0 text-left">
        <p class="text-sm font-bold text-gray-900 dark:text-white truncate group-hover:underline">${escapeHtml(ad.title)}</p>
        <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-1">${escapeHtml(ad.description || '')}</p>
      </div>
      <span class="flex-shrink-0 px-3 py-1.5 bg-gray-900 dark:bg-teal-600 text-white text-xs font-bold rounded-lg">${escapeHtml(ad.cta_text || 'Learn More')}</span>
    </a>
  `;
}

function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str ?? '';
  return div.innerHTML;
}

function trackImpression(encodedId) {
  fetch(`${window.APP_CONFIG.baseUrl}api/views-increment`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    body: JSON.stringify({ type: 'ad', id: encodedId }),
  }).catch(() => {});
}

// The "Report Ad" link lives in the static layout markup (outside the
// JS-rendered slot), so it's wired once and just reads whichever ad is
// currently loaded at click time via the closured currentAdEncodedId.
function wireReportLink(feed) {
  if (reportLinkWired) return;
  const reportLink = feed.querySelector('.report-ad-link');
  if (!reportLink) return;
  reportLinkWired = true;

  reportLink.addEventListener('click', async (e) => {
    e.preventDefault();
    if (!currentAdEncodedId) return;

    const confirmed = await confirmDialog(
      'Report this ad as inappropriate or misleading?',
      'Report',
      'Cancel',
      'bg-red-600 hover:bg-red-700'
    );
    if (!confirmed) return;

    try {
      const res = await fetch(`${window.APP_CONFIG.baseUrl}api/report-ad`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ id: currentAdEncodedId }),
      });
      const data = await res.json();
      showToast(data.message || 'Thanks for the report.', data.success ? 'success' : 'error');
    } catch (err) {
      showToast('Could not submit report.', 'error');
    }
  });
}

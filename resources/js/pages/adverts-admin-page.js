// /resources/js/pages/adverts-admin-page.js
//
// Admin Adverts moderation page: search (server-rendered via a partial
// navigation that preserves the current status tab), tab switching (plain
// data-partial links, no JS needed), and the shared view-advert modal in
// admin mode (approve/deactivate/reject).

import { AnimationEngine } from '../utils/animations.js';
import { initViewAdvertModal } from '../utils/adverts/view-advert-modal.js';

let debounceTimer = null;

export function init() {
  AnimationEngine.refresh();

  if (!document.getElementById('adverts-administration')) return;

  initViewAdvertModal();
  wireSearch();
}

function wireSearch() {
  const input = document.getElementById('admin-ad-search-input');
  if (!input || input.dataset.searchInitialized) return;
  input.dataset.searchInitialized = 'true';

  input.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      const baseUrl = window.APP_CONFIG?.baseUrl || '/';
      const tab = input.dataset.currentTab || 'all';
      const q = input.value.trim();
      const url = `${baseUrl}adverts-admin?tab=${encodeURIComponent(tab)}${q ? `&q=${encodeURIComponent(q)}` : ''}`;

      if (window.loadPartial) {
        window.loadPartial(url, true);
      } else {
        window.location.href = url;
      }
    }, 400);
  });
}

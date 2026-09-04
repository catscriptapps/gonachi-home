// /resources/js/utils/adverts/search.js
//
// Debounced search box wiring shared by /adverts (public feed) and
// /my-adverts (owner's own ads) — same #ad-search-input / #ads-grid /
// #empty-ads-state contract, differing only in the query-string flag that
// selects which AdvertsController method the API calls.

import { AnimationEngine } from '../animations.js';

let debounceTimer = null;

export function initAdvertSearch({ endpointParam = '' } = {}) {
  const input = document.getElementById('ad-search-input');
  const grid = document.getElementById('ads-grid');
  const emptyState = document.getElementById('empty-ads-state');
  if (!input || !grid || input.dataset.searchInitialized) return;
  input.dataset.searchInitialized = 'true';

  input.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => runSearch(input.value.trim(), grid, emptyState, endpointParam), 350);
  });
}

async function runSearch(query, grid, emptyState, endpointParam) {
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';
  const params = new URLSearchParams();
  if (endpointParam) {
    endpointParam.split('&').forEach((pair) => {
      const [k, v] = pair.split('=');
      params.set(k, v);
    });
  }
  if (query) params.set('q', query);

  try {
    const response = await fetch(`${baseUrl}api/adverts?${params.toString()}`);
    const result = await response.json();

    if (!result.success) return;

    if (result.html.trim() === '') {
      grid.classList.add('hidden');
      grid.innerHTML = '';
      if (emptyState) {
        emptyState.classList.remove('hidden');
        emptyState.querySelector('h4').textContent = query ? 'No adverts match that search' : 'No adverts to show yet';
      }
    } else {
      grid.classList.remove('hidden');
      emptyState?.classList.add('hidden');
      grid.innerHTML = result.html;
      AnimationEngine.refresh();
    }
  } catch (err) {
    console.error('Advert search error:', err);
  }
}

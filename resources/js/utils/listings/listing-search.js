// /resources/js/utils/listings/listing-search.js
//
// Debounced search box wiring shared by /listings (public feed) and
// /my-listings (owner's own listings) — same #listing-search-input /
// #listings-grid / #empty-listings-state / #no-listings-found-state
// contract, differing only in the query-string flag that selects which
// ListingsController method the API calls. On every search this also resets
// the infinite-scroll module back to page 1 for the new query, via the
// listings-search-updated window event — matching legacy's actual
// paginated/infinite-scroll UX for this module.

import { AnimationEngine } from '../animations.js';

let debounceTimer = null;

export function initListingSearch({ endpointParam = '' } = {}) {
  const input = document.getElementById('listing-search-input');
  const grid = document.getElementById('listings-grid');
  if (!input || !grid || input.dataset.searchInitialized) return;
  input.dataset.searchInitialized = 'true';

  document.getElementById('clear-listing-search')?.addEventListener('click', () => {
    input.value = '';
    runSearch('', grid, endpointParam);
  });

  input.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => runSearch(input.value.trim(), grid, endpointParam), 400);
  });
}

async function runSearch(query, grid, endpointParam) {
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';
  const emptyState = document.getElementById('empty-listings-state');
  const noResultsState = document.getElementById('no-listings-found-state');
  const sentinel = document.getElementById('listings-load-more-sentinel');

  const params = new URLSearchParams();
  if (endpointParam) {
    endpointParam.split('&').forEach((pair) => {
      const [k, v] = pair.split('=');
      params.set(k, v);
    });
  }
  if (query) params.set('q', query);
  params.set('page', '1');

  try {
    const response = await fetch(`${baseUrl}api/listings?${params.toString()}`);
    const result = await response.json();

    if (!result.success) return;

    if (result.html.trim() === '') {
      grid.classList.add('hidden');
      grid.innerHTML = '';
      emptyState?.classList.add('hidden');
      noResultsState?.classList.toggle('hidden', !query);
      if (!query) emptyState?.classList.remove('hidden');
    } else {
      grid.classList.remove('hidden');
      emptyState?.classList.add('hidden');
      noResultsState?.classList.add('hidden');
      grid.innerHTML = result.html;
      AnimationEngine.refresh();
    }

    if (sentinel) {
      sentinel.dataset.page = '1';
      sentinel.dataset.hasMore = result.hasMore ? '1' : '0';
    }

    window.dispatchEvent(new CustomEvent('listings-search-updated', { detail: { query, endpointParam } }));
    window.dispatchEvent(new CustomEvent('listing:updated'));
  } catch (err) {
    console.error('Listing search error:', err);
  }
}

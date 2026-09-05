// /resources/js/utils/quotations/search.js
//
// Debounced search box wiring shared by /quotations (public feed) and
// /my-quotations (owner's own requests) — same #quote-search-input /
// #quotes-grid / #empty-quotes-state contract, differing only in the
// query-string flag that selects which QuotationsController method the API
// calls.

import { AnimationEngine } from '../animations.js';

let debounceTimer = null;

export function initQuotationSearch({ endpointParam = '' } = {}) {
  const input = document.getElementById('quote-search-input');
  const grid = document.getElementById('quotes-grid');
  const emptyState = document.getElementById('empty-quotes-state');
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
    const response = await fetch(`${baseUrl}api/quotations?${params.toString()}`);
    const result = await response.json();

    if (!result.success) return;

    if (result.html.trim() === '') {
      grid.classList.add('hidden');
      grid.innerHTML = '';
      if (emptyState) {
        emptyState.classList.remove('hidden');
        emptyState.querySelector('h4').textContent = query ? 'No quotations match that search' : 'No quotations to show yet';
      }
    } else {
      grid.classList.remove('hidden');
      emptyState?.classList.add('hidden');
      grid.innerHTML = result.html;
      AnimationEngine.refresh();
    }
  } catch (err) {
    console.error('Quotation search error:', err);
  }
}

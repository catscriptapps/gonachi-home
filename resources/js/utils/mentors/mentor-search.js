// /resources/js/utils/mentors/mentor-search.js
//
// Debounced search box + target-type filter wiring for the shared /mentors
// directory — #mentor-search-input / #mentor-type-filter / #mentors-grid /
// #empty-mentors-state / #no-mentors-found.

import { AnimationEngine } from '../animations.js';

let debounceTimer = null;

export function initMentorSearch() {
  const input = document.getElementById('mentor-search-input');
  const typeFilter = document.getElementById('mentor-type-filter');
  const grid = document.getElementById('mentors-grid');
  const clearBtn = document.getElementById('clear-mentor-filters');
  if (!input || !grid || input.dataset.searchInitialized) return;
  input.dataset.searchInitialized = 'true';

  const trigger = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(runSearch, 350);
  };

  input.addEventListener('input', trigger);
  typeFilter?.addEventListener('change', trigger);

  clearBtn?.addEventListener('click', () => {
    input.value = '';
    if (typeFilter) typeFilter.value = '0';
    runSearch();
  });
}

async function runSearch() {
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';
  const input = document.getElementById('mentor-search-input');
  const typeFilter = document.getElementById('mentor-type-filter');
  const grid = document.getElementById('mentors-grid');
  const emptyState = document.getElementById('empty-mentors-state');
  const noResultsState = document.getElementById('no-mentors-found');

  const query = input.value.trim();
  const targetType = typeFilter?.value || '0';

  const params = new URLSearchParams();
  if (query) params.set('q', query);
  if (targetType !== '0') params.set('target_type', targetType);

  try {
    const response = await fetch(`${baseUrl}api/mentors?${params.toString()}`);
    const result = await response.json();
    if (!result.success) return;

    const hasQuery = !!(query || targetType !== '0');

    if (result.html.trim() === '') {
      grid.classList.add('hidden');
      grid.innerHTML = '';
      emptyState?.classList.add('hidden');
      noResultsState?.classList.toggle('hidden', !hasQuery);
      if (!hasQuery) emptyState?.classList.remove('hidden');
    } else {
      grid.classList.remove('hidden');
      emptyState?.classList.add('hidden');
      noResultsState?.classList.add('hidden');
      grid.innerHTML = result.html;
      AnimationEngine.refresh();
    }
  } catch (err) {
    console.error('Mentor search error:', err);
  }
}

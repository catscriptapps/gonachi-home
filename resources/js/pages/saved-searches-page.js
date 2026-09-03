// /resources/js/pages/saved-searches-page.js
//
// Create/delete for the Saved Alerts page — pure AJAX, refreshes the
// current view via the SPA router afterward rather than hand-rebuilding
// the list DOM (same pattern as utils/contractor-claim.js and
// utils/review-queue.js).

import { showToast } from '../ui/toast.js';

function baseUrl() {
  return window.APP_CONFIG?.baseUrl || '/';
}

function refresh() {
  const currentUrl = window.location.pathname + window.location.search;
  if (window.loadPartial) {
    window.loadPartial(currentUrl, false);
  }
}

function wireCreateForm() {
  const form = document.getElementById('saved-search-form');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const qInput = document.getElementById('saved-search-q');
    const regionSelect = document.getElementById('saved-search-region');
    const submitBtn = document.getElementById('saved-search-submit');

    const q = qInput.value.trim();
    const region = regionSelect.value;

    if (!q && !region) {
      showToast('Enter a keyword or pick a region to save an alert.', 'error');
      return;
    }

    submitBtn.disabled = true;

    try {
      const res = await fetch(`${baseUrl()}api/saved-search-create`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ q, region }),
      });
      const data = await res.json();

      if (data.success) {
        showToast('Alert saved.', 'success');
        refresh();
      } else {
        showToast(data.messages?.[0] || 'Failed to save alert.', 'error');
        submitBtn.disabled = false;
      }
    } catch (err) {
      console.error('Saved search create failed:', err);
      showToast('Unexpected error. Please try again.', 'error');
      submitBtn.disabled = false;
    }
  });
}

function wireDeleteButtons() {
  const list = document.getElementById('saved-search-list');
  if (!list) return;

  list.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-delete-saved-search]');
    if (!btn) return;

    const id = Number(btn.dataset.deleteSavedSearch);
    btn.disabled = true;

    try {
      const res = await fetch(`${baseUrl()}api/saved-search-delete`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id }),
      });
      const data = await res.json();

      if (data.success) {
        showToast('Alert removed.', 'success');
        refresh();
      } else {
        showToast(data.messages?.[0] || 'Failed to remove alert.', 'error');
        btn.disabled = false;
      }
    } catch (err) {
      console.error('Saved search delete failed:', err);
      showToast('Unexpected error. Please try again.', 'error');
      btn.disabled = false;
    }
  });
}

export function init() {
  const page = document.getElementById('saved-search-form') || document.getElementById('saved-search-list');
  if (!page || page.dataset.initialized) return;
  page.dataset.initialized = 'true';

  wireCreateForm();
  wireDeleteButtons();
}

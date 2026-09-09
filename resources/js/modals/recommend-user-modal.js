// /resources/js/modals/recommend-user-modal.js
//
// "Recommend a User" — a two-step flow: search for the person first
// (shared rew-user-lookup endpoint), then the recommendation form itself.
// Ported from the legacy gonachi-old platform's /recommend/ "Recommend
// User" tab, condensed into one modal — mirrors rate-user-modal.js closely
// since gonachi-old's two apps share ~80% of the same scaffolding.

import { Modal } from '../factories/modal-factory.js';
import { recommendationForm } from '../forms/recommendation-form.js';
import { enableDynamicRegionLoading } from '../components/regions-component.js';
import { fetchCountries } from '../api/countries-api.js';
import { showToast } from '../ui/toast.js';

let modal = null;
let userTypesCache = null;

async function loadUserTypes() {
  if (userTypesCache) return userTypesCache;
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';
  const res = await fetch(`${baseUrl}api/user-types`).then((r) => r.json());
  userTypesCache = (res.data || []).filter((t) => String(t.user_type).toLowerCase() !== 'admin');
  return userTypesCache;
}

function searchStepHtml() {
  return `
    <div class="space-y-4">
      <div class="relative">
        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </span>
        <input type="text" id="rec-user-search-input" placeholder="Search by name or email..." autocomplete="off"
          class="w-full pl-9 pr-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none text-gray-900 dark:text-white" />
      </div>
      <div id="rec-user-search-results" class="space-y-2"></div>
    </div>
  `;
}

function userResultRow(u) {
  return `
    <button type="button" class="rec-user-result w-full flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-gray-800 hover:border-teal-400 dark:hover:border-teal-700 transition-colors text-left"
      data-id="${u.id}" data-name="${escapeAttr(u.name)}" data-initial="${escapeAttr(u.initial)}" data-avatar="${escapeAttr(u.avatar || '')}" data-city="${escapeAttr(u.city || '')}" data-country-id="${u.country_id || ''}" data-region-id="${u.region_id || ''}">
      <div class="w-9 h-9 rounded-full overflow-hidden bg-teal-50 dark:bg-teal-950/40 flex items-center justify-center text-xs font-black text-teal-500 flex-shrink-0">
        ${u.avatar ? `<img src="${u.avatar}" class="w-full h-full object-cover">` : u.initial}
      </div>
      <span class="text-sm font-bold text-gray-900 dark:text-white truncate">${escapeHtml(u.name)}</span>
    </button>
  `;
}

let searchDebounce = null;

function wireSearchStep() {
  const input = document.getElementById('rec-user-search-input');
  const results = document.getElementById('rec-user-search-results');

  input.addEventListener('input', () => {
    clearTimeout(searchDebounce);
    const query = input.value.trim();
    if (!query) {
      results.innerHTML = '';
      return;
    }
    searchDebounce = setTimeout(async () => {
      const baseUrl = window.APP_CONFIG?.baseUrl || '/';
      const res = await fetch(`${baseUrl}api/rew-user-lookup?q=${encodeURIComponent(query)}`).then((r) => r.json());
      const users = res.data || [];
      results.innerHTML = users.map(userResultRow).join('') || '<p class="text-xs text-gray-400 text-center py-3">No matching users.</p>';
    }, 300);
  });

  results.addEventListener('click', (e) => {
    const btn = e.target.closest('.rec-user-result');
    if (!btn) return;

    showFormStep({
      id: btn.dataset.id,
      name: btn.dataset.name,
      initial: btn.dataset.initial,
      avatar: btn.dataset.avatar,
      city: btn.dataset.city,
      countryId: btn.dataset.countryId,
      regionId: btn.dataset.regionId,
    });
  });
}

async function showFormStep(target) {
  const [countriesRes, userTypes] = await Promise.all([fetchCountries(), loadUserTypes()]);

  const body = document.querySelector(`#recommend-user-modal .modal-body`);
  body.innerHTML = recommendationForm({ target, lookups: { countries: countriesRes, userTypes } });

  const form = document.getElementById('recommendation-form');
  enableDynamicRegionLoading('recommendation-form');

  if (target.countryId) {
    const countrySelect = document.getElementById('rec-country-input');
    countrySelect.value = String(target.countryId);
    countrySelect.dispatchEvent(new CustomEvent('change', { detail: { preSelectedRegionId: target.regionId } }));
  }

  form.addEventListener('submit', (e) => handleSubmit(e, form));
}

async function handleSubmit(e, form) {
  e.preventDefault();
  const errorSlot = document.getElementById('recommendation-form-error-slot');
  errorSlot.innerHTML = '';

  const comment = form.querySelector('[name="comment"]').value.trim();
  const userTypeId = form.querySelector('[name="dest_user_type_id"]').value;
  const recUserTypeId = form.querySelector('[name="rec_user_type_id"]').value;

  if (!comment || !userTypeId || !recUserTypeId) {
    errorSlot.innerHTML = `<div class="bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-400 px-4 py-2 rounded-xl font-bold text-sm">A role, an audience, and a comment are required.</div>`;
    return;
  }

  const formData = new FormData(form);
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalLabel = submitBtn.textContent;
  submitBtn.disabled = true;
  submitBtn.textContent = 'Posting...';

  try {
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    const response = await fetch(`${baseUrl}api/recommendations`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        dest_user_id: formData.get('dest_user_id'),
        dest_user_type_id: userTypeId,
        rec_user_type_id: recUserTypeId,
        country_id: formData.get('countryId'),
        region_id: formData.get('regionId'),
        city: formData.get('city'),
        comment,
      }),
    });
    const result = await response.json();

    if (result.success) {
      showToast('Recommendation posted!', 'success');
      setTimeout(() => modal?.close(), 1000);
      window.dispatchEvent(new CustomEvent('recommendation:submitted'));
    } else {
      errorSlot.innerHTML = (result.messages || ['Could not post recommendation.']).map((m) => `<div class="bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-400 px-4 py-2 rounded-xl font-bold text-sm">${m}</div>`).join('');
      submitBtn.disabled = false;
      submitBtn.textContent = originalLabel;
    }
  } catch (err) {
    console.error('Submit recommendation error:', err);
    errorSlot.innerHTML = `<div class="bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-400 px-4 py-2 rounded-xl font-bold text-sm">Unexpected error.</div>`;
    submitBtn.disabled = false;
    submitBtn.textContent = originalLabel;
  }
}

export function openRecommendUserModal(prefill) {
  if (modal) modal.destroy();

  modal = new Modal({
    id: 'recommend-user-modal',
    title: 'Recommend a User',
    content: searchStepHtml(),
    size: 'md',
    showFooter: false,
  });

  modal.open();

  if (prefill) {
    showFormStep(prefill);
  } else {
    wireSearchStep();
  }
}

export function initRecommendUserTriggers() {
  if (document._recommendUserTriggersAttached) return;
  document._recommendUserTriggersAttached = true;

  document.addEventListener('click', (e) => {
    if (e.target.closest('.recommend-user-trigger')) {
      openRecommendUserModal();
    }
  });
}

function escapeHtml(str) {
  return String(str ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

function escapeAttr(str) {
  return escapeHtml(str);
}

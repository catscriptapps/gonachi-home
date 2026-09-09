// /resources/js/modals/rate-user-modal.js
//
// "Rate a User" — a two-step flow: search for the person first (shared
// rew-user-lookup endpoint), then the rating form itself. Ported from the
// legacy gonachi-old platform's /ratings/ "Rate User" tab (search widget →
// 3-stage wizard), condensed into one modal.

import { Modal } from '../factories/modal-factory.js';
import { ratingForm, starPickerHtml } from '../forms/rating-form.js';
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
        <input type="text" id="rate-user-search-input" placeholder="Search by name or email..." autocomplete="off"
          class="w-full pl-9 pr-3 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none text-gray-900 dark:text-white" />
      </div>
      <div id="rate-user-search-results" class="space-y-2"></div>
    </div>
  `;
}

function userResultRow(u) {
  return `
    <button type="button" class="rate-user-result w-full flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-gray-800 hover:border-teal-400 dark:hover:border-teal-700 transition-colors text-left"
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
  const input = document.getElementById('rate-user-search-input');
  const results = document.getElementById('rate-user-search-results');

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
    const btn = e.target.closest('.rate-user-result');
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

  const body = document.querySelector(`#rate-user-modal .modal-body`);
  body.innerHTML = ratingForm({ target, lookups: { countries: countriesRes, userTypes } });

  const form = document.getElementById('rating-form');
  enableDynamicRegionLoading('rating-form');

  if (target.countryId) {
    const countrySelect = document.getElementById('rating-country-input');
    countrySelect.value = String(target.countryId);
    countrySelect.dispatchEvent(new CustomEvent('change', { detail: { preSelectedRegionId: target.regionId } }));
  }

  document.getElementById('rating-user-type-input').addEventListener('change', (e) => loadCriteria(e.target.value));

  form.addEventListener('submit', (e) => handleSubmit(e, form));
}

async function loadCriteria(userTypeId) {
  const block = document.getElementById('rating-criteria-block');
  const list = document.getElementById('rating-criteria-list');

  if (!userTypeId) {
    block.classList.add('hidden');
    list.innerHTML = '';
    return;
  }

  const baseUrl = window.APP_CONFIG?.baseUrl || '/';
  const res = await fetch(`${baseUrl}api/rating-criteria?user_type_id=${encodeURIComponent(userTypeId)}`).then((r) => r.json());
  const criteria = res.data || [];

  if (criteria.length === 0) {
    block.classList.add('hidden');
    list.innerHTML = '';
    return;
  }

  block.classList.remove('hidden');
  list.innerHTML = criteria.map((c) => starPickerHtml(c.criteria_id, c.criteria)).join('');

  list.querySelectorAll('[data-star-picker]').forEach((picker) => {
    picker.addEventListener('click', (e) => {
      const starBtn = e.target.closest('.star-btn');
      if (!starBtn) return;

      const value = Number(starBtn.dataset.star);
      const hidden = picker.querySelector('[data-star-value]');
      const current = Number(hidden.value);
      const next = current === value ? 0 : value;
      hidden.value = next;

      picker.querySelectorAll('.star-btn').forEach((b) => {
        b.classList.toggle('text-amber-400', Number(b.dataset.star) <= next);
        b.classList.toggle('text-gray-300', Number(b.dataset.star) > next);
        b.classList.toggle('dark:text-gray-600', Number(b.dataset.star) > next);
      });
    });
  });
}

async function handleSubmit(e, form) {
  e.preventDefault();
  const errorSlot = document.getElementById('rating-form-error-slot');
  errorSlot.innerHTML = '';

  const comment = form.querySelector('[name="comment"]').value.trim();
  const userTypeId = form.querySelector('[name="dest_user_type_id"]').value;

  if (!comment || !userTypeId) {
    errorSlot.innerHTML = `<div class="bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-400 px-4 py-2 rounded-xl font-bold text-sm">A role and a review are required.</div>`;
    return;
  }

  const scores = Array.from(form.querySelectorAll('[data-criteria-row]')).map((row) => ({
    criteria_id: Number(row.dataset.criteriaId),
    stars: Number(row.querySelector('[data-star-value]').value),
  })).filter((s) => s.stars > 0);

  const formData = new FormData(form);
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalLabel = submitBtn.textContent;
  submitBtn.disabled = true;
  submitBtn.textContent = 'Posting...';

  try {
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    const response = await fetch(`${baseUrl}api/ratings`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        dest_user_id: formData.get('dest_user_id'),
        dest_user_type_id: userTypeId,
        country_id: formData.get('countryId'),
        region_id: formData.get('regionId'),
        city: formData.get('city'),
        comment,
        scores,
      }),
    });
    const result = await response.json();

    if (result.success) {
      showToast('Rating posted!', 'success');
      setTimeout(() => modal?.close(), 1000);
      window.dispatchEvent(new CustomEvent('rating:submitted'));
    } else {
      errorSlot.innerHTML = (result.messages || ['Could not post rating.']).map((m) => `<div class="bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-400 px-4 py-2 rounded-xl font-bold text-sm">${m}</div>`).join('');
      submitBtn.disabled = false;
      submitBtn.textContent = originalLabel;
    }
  } catch (err) {
    console.error('Submit rating error:', err);
    errorSlot.innerHTML = `<div class="bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-400 px-4 py-2 rounded-xl font-bold text-sm">Unexpected error.</div>`;
    submitBtn.disabled = false;
    submitBtn.textContent = originalLabel;
  }
}

export function openRateUserModal(prefill) {
  if (modal) modal.destroy();

  modal = new Modal({
    id: 'rate-user-modal',
    title: 'Rate a User',
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

export function initRateUserTriggers() {
  if (document._rateUserTriggersAttached) return;
  document._rateUserTriggersAttached = true;

  document.addEventListener('click', (e) => {
    if (e.target.closest('.rate-user-trigger')) {
      openRateUserModal();
    }
  });
}

function escapeHtml(str) {
  return String(str ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

function escapeAttr(str) {
  return escapeHtml(str);
}

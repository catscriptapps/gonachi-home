// /resources/js/utils/recommendations/browse.js
//
// "Look Up Recommendations" (search any user, see what they've received)
// and "My Recommendations Given" — mirrors utils/ratings/browse.js closely
// (no star scores here, plus an audience/"Recommend To" label).

let lookupDebounce = null;

export function initRecommendationsBrowse() {
  wireLookup();
  loadMyRecommendationsGiven();

  window.addEventListener('recommendation:submitted', () => loadMyRecommendationsGiven());
}

function wireLookup() {
  const input = document.getElementById('recommendations-lookup-input');
  if (!input || input.dataset.initialized) return;
  input.dataset.initialized = 'true';

  const results = document.getElementById('recommendations-lookup-results');

  input.addEventListener('input', () => {
    clearTimeout(lookupDebounce);
    const query = input.value.trim();
    if (!query) {
      results.innerHTML = '';
      return;
    }
    lookupDebounce = setTimeout(async () => {
      const baseUrl = window.APP_CONFIG?.baseUrl || '/';
      const res = await fetch(`${baseUrl}api/rew-user-lookup?q=${encodeURIComponent(query)}`).then((r) => r.json());
      const users = res.data || [];
      results.innerHTML = users.map(userResultRow).join('') || '<p class="text-xs text-gray-400 text-center py-2">No matching users.</p>';
    }, 300);
  });

  results.addEventListener('click', (e) => {
    const btn = e.target.closest('.recommendations-lookup-result');
    if (!btn) return;
    loadReceivedRecommendations(btn.dataset.id, btn.dataset.name);
  });
}

function userResultRow(u) {
  return `
    <button type="button" class="recommendations-lookup-result w-full flex items-center gap-3 p-2.5 rounded-lg border border-gray-200 dark:border-gray-800 hover:border-teal-400 dark:hover:border-teal-700 transition-colors text-left"
      data-id="${u.id}" data-name="${escapeAttr(u.name)}">
      <div class="w-8 h-8 rounded-full overflow-hidden bg-teal-50 dark:bg-teal-950/40 flex items-center justify-center text-xs font-black text-teal-500 flex-shrink-0">
        ${u.avatar ? `<img src="${u.avatar}" class="w-full h-full object-cover">` : u.initial}
      </div>
      <span class="text-xs font-bold text-gray-900 dark:text-white truncate">${escapeHtml(u.name)}</span>
    </button>
  `;
}

async function loadReceivedRecommendations(destUserId, name) {
  const list = document.getElementById('recommendations-lookup-list');
  const empty = document.getElementById('recommendations-lookup-empty');

  list.classList.remove('hidden');
  empty.classList.add('hidden');
  list.innerHTML = `<div class="flex justify-center py-4"><div class="animate-spin rounded-full h-5 w-5 border-2 border-teal-500 border-t-transparent"></div></div>`;

  const baseUrl = window.APP_CONFIG?.baseUrl || '/';
  const res = await fetch(`${baseUrl}api/recommendations?dest_user_id=${encodeURIComponent(destUserId)}`).then((r) => r.json());
  const recommendations = res.data || [];

  list.innerHTML = `<p class="text-xs font-bold text-gray-500 dark:text-gray-400 mb-2">Recommendations received by ${escapeHtml(name)}</p>` +
    (recommendations.map((r) => recommendationRowHtml(r, false)).join('') || '<p class="text-xs text-gray-400 text-center py-4">No recommendations yet.</p>');
}

async function loadMyRecommendationsGiven() {
  const list = document.getElementById('my-recommendations-given-list');
  const empty = document.getElementById('my-recommendations-given-empty');
  if (!list) return;

  const baseUrl = window.APP_CONFIG?.baseUrl || '/';
  const res = await fetch(`${baseUrl}api/recommendations?mine=1`).then((r) => r.json());
  const recommendations = res.data || [];

  if (recommendations.length === 0) {
    list.innerHTML = '';
    empty.classList.remove('hidden');
    return;
  }

  empty.classList.add('hidden');
  list.innerHTML = recommendations.map((r) => recommendationRowHtml(r, true)).join('');
}

function recommendationRowHtml(r, showAsGiven) {
  const location = [r.city, r.region_name, r.country_name].filter(Boolean).join(', ');

  return `
    <div class="p-4 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30">
      <div class="flex items-center justify-between gap-2 mb-1">
        <div class="flex items-center gap-2 min-w-0">
          <div class="w-7 h-7 rounded-full overflow-hidden bg-teal-50 dark:bg-teal-950/40 flex items-center justify-center text-[10px] font-black text-teal-500 flex-shrink-0">
            ${r.person_avatar ? `<img src="${r.person_avatar}" class="w-full h-full object-cover">` : r.person_initial}
          </div>
          <span class="text-xs font-bold text-gray-800 dark:text-gray-200 truncate">${showAsGiven ? 'You recommended ' : ''}${escapeHtml(r.person_name)}</span>
        </div>
        <span class="text-[9px] font-black uppercase tracking-wider text-teal-600 dark:text-teal-400 flex-shrink-0">${escapeHtml(r.user_type_name)}</span>
      </div>
      ${location ? `<p class="text-[10px] text-gray-400 mb-1">${escapeHtml(location)}</p>` : ''}
      <p class="text-xs text-gray-600 dark:text-gray-400 whitespace-pre-line">${escapeHtml(r.comment)}</p>
      <p class="text-[10px] font-bold text-gray-400 mt-2">Recommended to <span class="text-teal-600 dark:text-teal-400">${escapeHtml(r.rec_user_type_name)}s</span></p>
      <p class="text-[10px] text-gray-400 mt-1">${r.created_at || ''}</p>
    </div>
  `;
}

function escapeHtml(str) {
  return String(str ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

function escapeAttr(str) {
  return escapeHtml(str);
}

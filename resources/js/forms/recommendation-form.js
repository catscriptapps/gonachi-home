// /resources/js/forms/recommendation-form.js
//
// The "Recommend a User" form — role + location context, an audience
// group to recommend them to, and a free-text comment. Ported from the
// legacy gonachi-old platform's /recommend/ wizard (its "Individual"
// targeting mode was dead code there — the live UI only ever exposed
// "User Groups" — so this only offers that), condensed into one form.

export function recommendationForm({ target, lookups }) {
  const opt = (list, valueKey, labelKey) => list.map((row) => `<option value="${row[valueKey]}">${row[labelKey]}</option>`).join('');

  return `
    <form id="recommendation-form" class="space-y-6" novalidate>
      <input type="hidden" name="dest_user_id" value="${target.id}">

      <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-800">
        <div class="w-10 h-10 rounded-full overflow-hidden bg-teal-50 dark:bg-teal-950/40 flex items-center justify-center text-sm font-black text-teal-500 flex-shrink-0">
          ${target.avatar ? `<img src="${target.avatar}" class="w-full h-full object-cover">` : target.initial}
        </div>
        <div class="min-w-0">
          <p class="text-xs text-gray-400 uppercase tracking-wider font-bold">Recommending</p>
          <p class="text-sm font-bold text-gray-900 dark:text-white truncate">${escapeHtml(target.name)}</p>
        </div>
      </div>

      <div>
        <h4 class="text-xs font-black uppercase tracking-widest text-teal-600 mb-3">Role & Location</h4>
        <div class="grid sm:grid-cols-2 gap-4 mb-4">
          <div>
            <label for="rec-user-type-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Recommend them as</label>
            <select id="rec-user-type-input" name="dest_user_type_id" required class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
              <option value="">Select...</option>
              ${opt(lookups.userTypes, 'user_type_id', 'user_type')}
            </select>
          </div>
          <div>
            <label for="rec-city-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">City</label>
            <input type="text" id="rec-city-input" name="city" value="${escapeAttr(target.city)}" placeholder="e.g. Toronto"
              class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
          </div>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label for="rec-country-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Country</label>
            <select id="rec-country-input" name="countryId" class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
              <option value="">Select...</option>
              ${opt(lookups.countries, 'id', 'name')}
            </select>
          </div>
          <div>
            <label for="rec-region-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Region / State</label>
            <select id="rec-region-input" name="regionId" class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
              <option value="">Select Region</option>
            </select>
          </div>
        </div>
      </div>

      <div>
        <label for="rec-audience-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Recommend To <span class="font-normal text-gray-400">(who should see this?)</span></label>
        <select id="rec-audience-input" name="rec_user_type_id" required class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
          <option value="">Select an audience...</option>
          ${opt(lookups.userTypes, 'user_type_id', 'user_type')}
        </select>
      </div>

      <div>
        <label for="rec-comment-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Your Comment</label>
        <textarea id="rec-comment-input" name="comment" required rows="4" placeholder="Why do you recommend them?"
          class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500 resize-none"></textarea>
      </div>

      <div id="recommendation-form-error-slot"></div>

      <button type="submit" class="w-full py-3 bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm rounded-xl transition-colors">
        Post Recommendation
      </button>
    </form>
  `;
}

function escapeHtml(str) {
  return String(str ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

function escapeAttr(str) {
  return escapeHtml(str);
}

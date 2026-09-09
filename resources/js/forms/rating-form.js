// /resources/js/forms/rating-form.js
//
// The "Rate a User" form — role + location context, then a free-text
// review plus (if the chosen role has any) 1-5 star scores per criterion.
// Ported from the legacy gonachi-old platform's /ratings/ wizard, condensed
// from its 3-stage flow into one form matching this app's other modals.

export function ratingForm({ target, lookups }) {
  const opt = (list, valueKey, labelKey) => list.map((row) => `<option value="${row[valueKey]}">${row[labelKey]}</option>`).join('');

  return `
    <form id="rating-form" class="space-y-6" novalidate>
      <input type="hidden" name="dest_user_id" value="${target.id}">

      <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-800">
        <div class="w-10 h-10 rounded-full overflow-hidden bg-teal-50 dark:bg-teal-950/40 flex items-center justify-center text-sm font-black text-teal-500 flex-shrink-0">
          ${target.avatar ? `<img src="${target.avatar}" class="w-full h-full object-cover">` : target.initial}
        </div>
        <div class="min-w-0">
          <p class="text-xs text-gray-400 uppercase tracking-wider font-bold">Rating</p>
          <p class="text-sm font-bold text-gray-900 dark:text-white truncate">${escapeHtml(target.name)}</p>
        </div>
      </div>

      <div>
        <h4 class="text-xs font-black uppercase tracking-widest text-teal-600 mb-3">Role & Location</h4>
        <div class="grid sm:grid-cols-2 gap-4 mb-4">
          <div>
            <label for="rating-user-type-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Rate them as</label>
            <select id="rating-user-type-input" name="dest_user_type_id" required class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
              <option value="">Select...</option>
              ${opt(lookups.userTypes, 'user_type_id', 'user_type')}
            </select>
          </div>
          <div>
            <label for="rating-city-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">City</label>
            <input type="text" id="rating-city-input" name="city" value="${escapeAttr(target.city)}" placeholder="e.g. Toronto"
              class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
          </div>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label for="rating-country-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Country</label>
            <select id="rating-country-input" name="countryId" class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
              <option value="">Select...</option>
              ${opt(lookups.countries, 'id', 'name')}
            </select>
          </div>
          <div>
            <label for="rating-region-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Region / State</label>
            <select id="rating-region-input" name="regionId" class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
              <option value="">Select Region</option>
            </select>
          </div>
        </div>
      </div>

      <div id="rating-criteria-block" class="hidden">
        <h4 class="text-xs font-black uppercase tracking-widest text-teal-600 mb-3">Rate Their Performance</h4>
        <div id="rating-criteria-list" class="space-y-3"></div>
      </div>

      <div>
        <label for="rating-comment-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Your Review</label>
        <textarea id="rating-comment-input" name="comment" required rows="4" placeholder="Share your experience..."
          class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500 resize-none"></textarea>
      </div>

      <div id="rating-form-error-slot"></div>

      <button type="submit" class="w-full py-3 bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm rounded-xl transition-colors">
        Post Rating
      </button>
    </form>
  `;
}

export function starPickerHtml(criteriaId, label) {
  return `
    <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-800" data-criteria-row data-criteria-id="${criteriaId}">
      <span class="text-xs font-bold text-gray-700 dark:text-gray-300">${escapeHtml(label)}</span>
      <div class="flex items-center gap-1" data-star-picker>
        ${[1, 2, 3, 4, 5].map((n) => `
          <button type="button" data-star="${n}" class="star-btn text-gray-300 dark:text-gray-600 hover:text-amber-400 transition-colors">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 00-.363 1.118l1.287 3.957c.3.922-.755 1.688-1.539 1.118L10.6 15.63a1 1 0 00-1.176 0l-3.367 2.446c-.784.57-1.838-.196-1.539-1.118l1.287-3.957a1 1 0 00-.363-1.118L2.075 9.436c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 00.95-.69l1.273-3.958z"/></svg>
          </button>`).join('')}
        <input type="hidden" name="stars_${criteriaId}" data-star-value value="0">
      </div>
    </div>
  `;
}

function escapeHtml(str) {
  return String(str ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

function escapeAttr(str) {
  return escapeHtml(str);
}

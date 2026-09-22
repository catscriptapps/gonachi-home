// /resources/js/forms/swap-listing-form.js
//
// Builds the Add/Edit Listing form HTML for the Swap compose modal — same
// idPrefix/mode/existing shape as Real Estate World's own
// resources/js/forms/listing-form.js, simplified to Swap's own field set
// (no property/amenity fields).

export function swapListingFormHtml({ mode, lookups, existing = null }) {
  const p = mode === 'edit' ? 'swap-edit' : 'swap-add';
  const encodedId = existing?.encodedId || '';

  const categoryOptions = (lookups.categories || [])
    .map((c) => `<option value="${c.id}" ${String(existing?.categoryId) === String(c.id) ? 'selected' : ''}>${escapeHtml(c.name)}</option>`)
    .join('');

  const typeOptions = Object.entries(lookups.types || {})
    .map(([value, label]) => `<option value="${value}" ${existing?.listingType === value ? 'selected' : ''}>${escapeHtml(label)}</option>`)
    .join('');

  const conditionOptions = Object.entries(lookups.conditions || {})
    .map(([value, label]) => `<option value="${value}" ${existing?.condition === value ? 'selected' : ''}>${escapeHtml(label)}</option>`)
    .join('');

  const currentType = existing?.listingType || 'swap';

  return `
    <form id="${p}-form" novalidate data-encoded-id="${encodedId}" class="space-y-5">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div class="sm:col-span-2">
          <label for="${p}-title" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Title</label>
          <input type="text" id="${p}-title" name="title" required value="${escapeHtml(existing?.title || '')}" placeholder="e.g. Dell XPS 13 Laptop (2022)" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none text-gray-900 dark:text-white" />
        </div>

        <div>
          <label for="${p}-category" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Category</label>
          <select id="${p}-category" name="category_id" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none text-gray-700 dark:text-gray-300">
            <option value="">Select category&hellip;</option>
            ${categoryOptions}
          </select>
        </div>

        <div>
          <label for="${p}-type" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Listing Type</label>
          <select id="${p}-type" name="listing_type" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none text-gray-700 dark:text-gray-300">
            ${typeOptions}
          </select>
        </div>

        <div>
          <label for="${p}-condition" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Condition</label>
          <select id="${p}-condition" name="condition" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none text-gray-700 dark:text-gray-300">
            ${conditionOptions}
          </select>
        </div>

        <div>
          <label for="${p}-city" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Location</label>
          <input type="text" id="${p}-city" name="city" value="${escapeHtml(existing?.city || '')}" placeholder="e.g. Lekki, Lagos" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none text-gray-900 dark:text-white" />
        </div>

        <div id="${p}-price-field" class="${currentType === 'sale' ? '' : 'hidden'}">
          <label for="${p}-price" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Price (&#8358;)</label>
          <input type="number" min="0" step="1" id="${p}-price" name="price" value="${escapeHtml(existing?.price ?? '')}" placeholder="e.g. 85000" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none text-gray-900 dark:text-white" />
        </div>

        <div id="${p}-trade-pref-field" class="sm:col-span-2 ${currentType === 'swap' ? '' : 'hidden'}">
          <label for="${p}-trade-pref" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Looking For <span class="normal-case font-medium text-gray-400">(optional)</span></label>
          <input type="text" id="${p}-trade-pref" name="trade_pref" value="${escapeHtml(existing?.tradePref || '')}" placeholder="e.g. Games console, DSLR camera" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none text-gray-900 dark:text-white" />
        </div>

        <div class="sm:col-span-2">
          <label for="${p}-description" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Description</label>
          <textarea id="${p}-description" name="description" rows="3" placeholder="Describe the item's condition, why you're letting it go, etc.&hellip;" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none text-gray-900 dark:text-white resize-none">${escapeHtml(existing?.description || '')}</textarea>
        </div>
      </div>

      <div>
        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Photos <span class="normal-case font-medium text-gray-400">(up to 6)</span></label>
        <button type="button" id="${p}-add-photos-btn" class="w-full flex flex-col items-center justify-center gap-2 border border-dashed border-gray-300 dark:border-gray-700 rounded-xl p-5 text-sm text-gray-500 dark:text-gray-400 hover:border-purple-400 hover:text-purple-600 dark:hover:text-purple-400 transition-colors">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
          <span>Add Photos</span>
        </button>
        <div id="${p}-photos-preview" class="grid grid-cols-3 sm:grid-cols-4 gap-3 mt-3 empty:mt-0"></div>
      </div>

      <div id="${p}-error-slot"></div>

      <div class="flex items-center justify-end pt-2">
        <button type="submit" id="${p}-submit" class="inline-flex items-center px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-bold text-sm rounded-lg transition-colors shadow-sm whitespace-nowrap">
          ${mode === 'edit' ? 'Save Changes' : 'Post Listing'}
        </button>
      </div>
    </form>
  `;
}

function escapeHtml(value) {
  return String(value ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

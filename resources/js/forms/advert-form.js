// /resources/js/forms/advert-form.js
//
// Builds the create/edit advert form HTML — a single-step form (title,
// description, keywords, CTA dropdown, landing URL, package-tier picker,
// country targeting). Ported from the legacy gonachi/ platform's
// advert-form.js. Audience-type (user-type) targeting is hidden for now —
// the user_type list surfaced admin/staff types that don't make sense to
// show here — and every advert is targeted to all users behind the scenes
// (see getPayload() in utils/adverts/form-submit.js).

export function advertForm({ mode, ctas, packages, existing }) {
  const idPrefix = mode === 'edit' ? 'ad-edit' : 'ad-add';
  const isEdit = mode === 'edit';

  const ctaOptions = ctas
    .map((c) => `<option value="${c.id}" ${isEdit && existing.callToActionId === c.id ? 'selected' : ''}>${c.label}</option>`)
    .join('');

  // Package tiers beyond Free are frozen for now (no payment flow wired up
  // yet) — shown so users can see what's coming, but locked to Free.
  const packageCards = packages
    .map((p) => {
      const isFree = p.package_id === 1;
      const checked = isFree;
      return `
        <label class="package-option relative flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 transition-all ${isFree ? 'cursor-pointer' : 'cursor-not-allowed opacity-50'} ${checked ? 'border-teal-500 bg-teal-50 dark:bg-teal-950/30' : 'border-gray-200 dark:border-gray-700'}">
          ${!isFree ? '<span class="absolute -top-2 -right-2 px-1.5 py-0.5 rounded-full bg-gray-400 dark:bg-gray-600 text-white text-[8px] font-black uppercase tracking-wide">Soon</span>' : ''}
          <input type="radio" name="advert_package" value="${p.package_id}" class="sr-only" ${checked ? 'checked' : ''} ${isFree ? '' : 'disabled'}>
          <svg class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="${p.package_icon}" /></svg>
          <span class="text-[10px] font-black uppercase tracking-wide text-gray-800 dark:text-gray-200">${p.package_name}</span>
          <span class="text-[9px] text-gray-400">${p.package_description}</span>
        </label>`;
    })
    .join('');

  const allCountriesChecked = isEdit ? existing.countries.includes('ALL') : true;
  const initialCountries = isEdit && !allCountriesChecked ? existing.countries : [];

  return `
    <form id="${idPrefix}-form" data-encoded-id="${isEdit ? existing.encodedId : ''}" class="space-y-5" novalidate>
      <div>
        <label for="${idPrefix}-title-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Title</label>
        <input type="text" id="${idPrefix}-title-input" name="title" required value="${isEdit ? escapeAttr(existing.title) : ''}" placeholder="e.g. Premium Property Listings in Lagos"
          class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
      </div>

      <div>
        <label for="${idPrefix}-description-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Description</label>
        <textarea id="${idPrefix}-description-input" name="description" required rows="3" placeholder="What are you advertising?"
          class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500 resize-none">${isEdit ? escapeHtml(existing.description) : ''}</textarea>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Keywords <span class="font-normal text-gray-400">(comma-separated)</span></label>
        <input type="text" name="keywords" value="${isEdit ? escapeAttr(existing.keywords) : ''}" placeholder="real estate, property, rentals"
          class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
      </div>

      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label for="${idPrefix}-cta-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Call To Action</label>
          <select id="${idPrefix}-cta-input" name="call_to_action_id" required class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
            <option value="">Select...</option>
            ${ctaOptions}
          </select>
        </div>
        <div>
          <label for="${idPrefix}-landing-url-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Landing Page URL</label>
          <input type="text" id="${idPrefix}-landing-url-input" name="landing_page_url" value="${isEdit ? escapeAttr(existing.landingPageUrl) : ''}" placeholder="yourbusiness.com"
            class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">Package</label>
        <div class="grid grid-cols-5 gap-2">${packageCards}</div>
      </div>

      <div>
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Target Countries</label>
        <div class="flex items-center gap-3 mb-2">
          <select id="${idPrefix}-country-selector" class="flex-1 rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
            <option value="">Add a country...</option>
          </select>
          <label class="flex items-center gap-2 flex-shrink-0">
            <input type="checkbox" id="${idPrefix}-all-countries" ${allCountriesChecked ? 'checked' : ''} class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 whitespace-nowrap">Target all countries</span>
          </label>
        </div>
        <input type="hidden" id="${idPrefix}-countries-hidden-json" value="${escapeAttr(JSON.stringify(initialCountries))}">
        <div id="${idPrefix}-selected-bucket" class="flex flex-wrap gap-2 min-h-[2.5rem] p-2 rounded-xl border border-dashed border-gray-200 dark:border-gray-700"></div>
        <p id="${idPrefix}-country-error" class="hidden text-xs text-red-600 mt-1">Pick at least one country, or target all countries.</p>
      </div>

      <div id="country-error-slot"></div>
      <p class="text-[11px] text-gray-400 italic">Ad will be reviewed by Gonachi Admins before going live.</p>

      <button type="submit" class="w-full py-3 bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm rounded-xl transition-colors">
        ${isEdit ? 'Save Changes' : 'Submit Advert'}
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

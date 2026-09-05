// /resources/js/forms/mentor-form.js
//
// Builds the create/edit mentor profile form HTML — base location,
// professional identity (mentor category, experience, headline, bio,
// skills), and digital presence (YouTube/website). Ported from the legacy
// gonachi/ platform's mentor-form.js.

export function mentorForm({ mode, lookups, existing }) {
  const idPrefix = mode === 'edit' ? 'mentor-edit' : 'mentor-add';
  const isEdit = mode === 'edit';

  const opt = (list, valueKey, labelKey, selected) =>
    list.map((row) => `<option value="${row[valueKey]}" ${isEdit && String(selected) === String(row[valueKey]) ? 'selected' : ''}>${row[labelKey]}</option>`).join('');

  return `
    <form id="${idPrefix}-form" data-encoded-id="${isEdit ? existing.encodedId : ''}" class="space-y-6" novalidate>

      <div>
        <h4 class="text-xs font-black uppercase tracking-widest text-teal-600 mb-3">Base Location</h4>
        <div class="grid sm:grid-cols-3 gap-4">
          <div>
            <label for="${idPrefix}-country-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Country</label>
            <select id="${idPrefix}-country-input" name="countryId" required class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
              <option value="">Select...</option>
              ${opt(lookups.countries, 'id', 'country', existing?.countryId)}
            </select>
          </div>
          <div>
            <label for="${idPrefix}-region-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Region / State</label>
            <select id="${idPrefix}-region-input" name="regionId" required class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
              <option value="">Select Region</option>
            </select>
          </div>
          <div>
            <label for="${idPrefix}-city-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">City</label>
            <input type="text" id="${idPrefix}-city-input" name="city" required value="${isEdit ? escapeAttr(existing.city) : ''}" placeholder="e.g. Toronto"
              class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
          </div>
        </div>
      </div>

      <div>
        <h4 class="text-xs font-black uppercase tracking-widest text-teal-600 mb-3">Professional Identity</h4>
        <div class="grid sm:grid-cols-2 gap-4 mb-4">
          <div>
            <label for="${idPrefix}-type-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Mentor Category</label>
            <select id="${idPrefix}-type-input" name="target_stakeholder_type_id" required class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
              <option value="">I am a...</option>
              ${opt(lookups.mentorTypes, 'id', 'name', existing?.targetTypeId)}
            </select>
          </div>
          <div>
            <label for="${idPrefix}-exp-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Years of Experience</label>
            <input type="number" id="${idPrefix}-exp-input" name="years_experience" required min="0" max="60" value="${isEdit ? existing.experienceYears : ''}" placeholder="e.g. 15"
              class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
          </div>
        </div>
        <div class="space-y-4">
          <div>
            <label for="${idPrefix}-headline-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Professional Headline</label>
            <input type="text" id="${idPrefix}-headline-input" name="headline" required value="${isEdit ? escapeAttr(existing.headline) : ''}" placeholder="e.g. Master Electrician & Real Estate Investor"
              class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
          </div>
          <div>
            <label for="${idPrefix}-bio-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Expert Bio</label>
            <textarea id="${idPrefix}-bio-input" name="bio" required rows="3" placeholder="Tell prospective mentees about your journey..."
              class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500 resize-none">${isEdit ? escapeHtml(existing.bio) : ''}</textarea>
          </div>
          <div>
            <label for="${idPrefix}-skills-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Core Skills <span class="font-normal text-gray-400">(comma-separated)</span></label>
            <input type="text" id="${idPrefix}-skills-input" name="skills" required value="${isEdit ? escapeAttr(existing.skills) : ''}" placeholder="e.g. Project Management, Plumbing"
              class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
          </div>
        </div>
      </div>

      <div>
        <h4 class="text-xs font-black uppercase tracking-widest text-teal-600 mb-3">Digital Presence</h4>
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label for="${idPrefix}-youtube-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">YouTube Channel / Video <span class="font-normal text-gray-400">(optional)</span></label>
            <input type="url" id="${idPrefix}-youtube-input" name="youtube_url" value="${isEdit ? escapeAttr(existing.youtubeUrl) : ''}" placeholder="https://youtube.com/..."
              class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
          </div>
          <div>
            <label for="${idPrefix}-website-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Portfolio/Website <span class="font-normal text-gray-400">(optional)</span></label>
            <input type="url" id="${idPrefix}-website-input" name="website_url" value="${isEdit ? escapeAttr(existing.websiteUrl) : ''}" placeholder="https://yourwebsite.com"
              class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
          </div>
        </div>
      </div>

      <p class="text-[11px] text-gray-400 italic">Mentors are verified for the Gonachi network quality.</p>

      <button type="submit" class="w-full py-3 bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm rounded-xl transition-colors">
        ${isEdit ? 'Save Changes' : 'Register as Mentor'}
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

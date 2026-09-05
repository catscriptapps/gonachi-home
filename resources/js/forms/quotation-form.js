// /resources/js/forms/quotation-form.js
//
// Builds the create/edit quotation form HTML — project location,
// work description, classification (contractor type / skilled trade / unit
// type / conditional house type), timeline, financials & audience scope,
// and optional video/contact fields. Ported from the legacy gonachi/
// platform's quotation-form.js.

export function quotationForm({ mode, lookups, existing }) {
  const idPrefix = mode === 'edit' ? 'quote-edit' : 'quote-add';
  const isEdit = mode === 'edit';

  const opt = (list, valueKey, labelKey, selected) =>
    list.map((row) => `<option value="${row[valueKey]}" ${isEdit && String(selected) === String(row[valueKey]) ? 'selected' : ''}>${row[labelKey]}</option>`).join('');

  const showHouseType = isEdit && Number(existing.unitTypeId) === 5;

  return `
    <form id="${idPrefix}-form" data-encoded-id="${isEdit ? existing.encodedId : ''}" class="space-y-6" novalidate>

      <div>
        <h4 class="text-xs font-black uppercase tracking-widest text-teal-600 mb-3">Project Location</h4>
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
            <input type="text" id="${idPrefix}-city-input" name="city" value="${isEdit ? escapeAttr(existing.city) : ''}" placeholder="e.g. Lagos"
              class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
          </div>
        </div>
      </div>

      <div>
        <h4 class="text-xs font-black uppercase tracking-widest text-teal-600 mb-3">Work Description</h4>
        <div class="space-y-4">
          <div>
            <label for="${idPrefix}-title-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Quotation Title</label>
            <input type="text" id="${idPrefix}-title-input" name="quotation_title" required value="${isEdit ? escapeAttr(existing.title) : ''}" placeholder="e.g. Kitchen Renovation Quote Needed"
              class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
          </div>
          <div>
            <label for="${idPrefix}-description-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Description of Work</label>
            <textarea id="${idPrefix}-description-input" name="description_of_work_to_be_done" required rows="3" placeholder="What needs to be done?"
              class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500 resize-none">${isEdit ? escapeHtml(existing.description) : ''}</textarea>
          </div>
        </div>
      </div>

      <div>
        <h4 class="text-xs font-black uppercase tracking-widest text-teal-600 mb-3">Project Classification</h4>
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label for="${idPrefix}-contractor-type-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Contractor Type</label>
            <select id="${idPrefix}-contractor-type-input" name="contractor_type_id" required class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
              <option value="">Select...</option>
              ${opt(lookups.contractorTypes, 'contractor_type_id', 'contractor_type', existing?.contractorTypeId)}
            </select>
          </div>
          <div>
            <label for="${idPrefix}-skilled-trade-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Skilled Trade</label>
            <select id="${idPrefix}-skilled-trade-input" name="skilled_trade_id" required class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
              <option value="">Select...</option>
              ${opt(lookups.skilledTrades, 'skilled_trade_id', 'skilled_trade', existing?.skilledTradeId)}
            </select>
          </div>
          <div>
            <label for="${idPrefix}-unit-type-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Unit Type</label>
            <select id="${idPrefix}-unit-type-input" name="unit_type_id" required class="unit-type-trigger w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
              <option value="">Select...</option>
              ${opt(lookups.unitTypes, 'unit_type_id', 'unit_type', existing?.unitTypeId)}
            </select>
          </div>
          <div id="${idPrefix}-house-type-wrapper" class="${showHouseType ? '' : 'hidden'}">
            <label for="${idPrefix}-house-type-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">House Style</label>
            <select id="${idPrefix}-house-type-input" name="house_type_id" class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
              <option value="">Select...</option>
              ${opt(lookups.houseTypes, 'house_type_id', 'house_type', existing?.houseTypeId)}
            </select>
          </div>
        </div>
      </div>

      <div>
        <h4 class="text-xs font-black uppercase tracking-widest text-teal-600 mb-3">Timeline & Schedule</h4>
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label for="${idPrefix}-start-date-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Start Date</label>
            <input type="date" id="${idPrefix}-start-date-input" name="start_date" required value="${isEdit ? existing.startDate || '' : ''}" min="${todayIso()}"
              class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
          </div>
          <div>
            <label for="${idPrefix}-finish-date-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Finish Date</label>
            <input type="date" id="${idPrefix}-finish-date-input" name="finish_date" required value="${isEdit ? existing.finishDate || '' : ''}" min="${todayIso()}"
              class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
          </div>
          <div>
            <label for="${idPrefix}-start-time-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Start Time</label>
            <input type="time" id="${idPrefix}-start-time-input" name="start_time" required step="900" value="${isEdit ? existing.startTime || '' : ''}"
              class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
          </div>
          <div>
            <label for="${idPrefix}-finish-time-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Finish Time</label>
            <input type="time" id="${idPrefix}-finish-time-input" name="finish_time" required step="900" value="${isEdit ? existing.finishTime || '' : ''}"
              class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
          </div>
        </div>
      </div>

      <div>
        <h4 class="text-xs font-black uppercase tracking-widest text-teal-600 mb-3">Financials & Type</h4>
        <div class="grid sm:grid-cols-3 gap-4">
          <div>
            <label for="${idPrefix}-quotation-type-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Quotation Type</label>
            <select id="${idPrefix}-quotation-type-input" name="quotation_type_id" required class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
              <option value="">Select...</option>
              ${opt(lookups.quotationTypes, 'quotation_type_id', 'quotation_type', existing?.quotationTypeId)}
            </select>
          </div>
          <div>
            <label for="${idPrefix}-budget-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Budget</label>
            <input type="text" id="${idPrefix}-budget-input" name="quotation_budget" value="${isEdit ? escapeAttr(existing.budget) : ''}" placeholder="e.g. $5,000 - $10,000"
              class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
          </div>
          <div>
            <label for="${idPrefix}-destination-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Visible To</label>
            <select id="${idPrefix}-destination-input" name="quotation_dest_id" required class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
              <option value="">Select...</option>
              ${opt(lookups.destinations, 'quotation_dest_id', 'quotation_dest', existing?.quotationDestId)}
            </select>
          </div>
        </div>
      </div>

      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label for="${idPrefix}-youtube-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">YouTube URL <span class="font-normal text-gray-400">(optional)</span></label>
          <input type="url" id="${idPrefix}-youtube-input" name="youtube_url" value="${isEdit ? escapeAttr(existing.youtubeUrl) : ''}" placeholder="https://youtube.com/..."
            class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
        </div>
        <div>
          <label for="${idPrefix}-phone-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Contact Phone <span class="font-normal text-gray-400">(optional)</span></label>
          <input type="tel" id="${idPrefix}-phone-input" name="contact_phone" value="${isEdit ? escapeAttr(existing.contactPhone) : ''}" placeholder="+1 555 000 0000"
            class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
        </div>
      </div>

      <div id="quote-form-error-slot"></div>

      <button type="submit" class="w-full py-3 bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm rounded-xl transition-colors">
        ${isEdit ? 'Save Changes' : 'Post Request'}
      </button>
    </form>
  `;
}

function todayIso() {
  return new Date().toISOString().slice(0, 10);
}

function escapeHtml(str) {
  return String(str ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

function escapeAttr(str) {
  return escapeHtml(str);
}

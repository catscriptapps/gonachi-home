// /resources/js/forms/listing-form.js
//
// Builds the create/edit listing form HTML — base location, classification
// (category -> category type), property specs & amenities, availability &
// financials, and video/phone. Ported from the legacy gonachi/ platform's
// listing-form.js. Category 2 ("Real Estate Services") and 3 ("Other") hide
// the entire property-specs/amenities/financials block (wired up in
// utils/listings/form-events.js), matching legacy's isService split.

export function listingForm({ mode, lookups, existing }) {
  const idPrefix = mode === 'edit' ? 'listing-edit' : 'listing-add';
  const isEdit = mode === 'edit';

  const opt = (list, valueKey, labelKey, selected) =>
    list.map((row) => `<option value="${row[valueKey]}" ${isEdit && String(selected) === String(row[valueKey]) ? 'selected' : ''}>${row[labelKey]}</option>`).join('');

  const categoryId = isEdit ? Number(existing.categoryId) : 1;
  const isService = categoryId === 2 || categoryId === 3;
  const showHouseType = isEdit && Number(existing.unitTypeId) === 5;

  const categoryTypesForCategory = lookups.listingCategoryTypes.filter((t) => Number(t.category_id) === categoryId);

  const selectedAmenities = (isEdit ? existing.amenities || [] : []).map(String);
  const amenitiesHtml = lookups.amenityGroups
    .map(
      (group) => `
      <div>
        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">${group.name}</p>
        <div class="grid grid-cols-2 gap-1.5">
          ${(group.amenities || [])
            .map(
              (a) => `
            <label class="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300 cursor-pointer">
              <input type="checkbox" name="amenities" value="${a.amenity_id}" ${selectedAmenities.includes(String(a.amenity_id)) ? 'checked' : ''} class="rounded border-gray-300 dark:border-gray-700 text-teal-600 focus:ring-teal-500">
              ${a.name}
            </label>`
            )
            .join('')}
        </div>
      </div>`
    )
    .join('');

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
            <input type="text" id="${idPrefix}-city-input" name="city" value="${isEdit ? escapeAttr(existing.city) : ''}" placeholder="e.g. Toronto"
              class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
          </div>
        </div>
      </div>

      <div>
        <h4 class="text-xs font-black uppercase tracking-widest text-teal-600 mb-3">Classification & Details</h4>
        <div class="grid sm:grid-cols-2 gap-4 mb-4">
          <div>
            <label for="${idPrefix}-category-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Category</label>
            <select id="${idPrefix}-category-input" name="category_id" required class="category-trigger w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
              ${opt(lookups.listingCategories, 'category_id', 'category', existing?.categoryId ?? 1)}
            </select>
          </div>
          <div id="${idPrefix}-category-type-wrapper" class="${categoryId === 3 ? 'hidden' : ''}">
            <label for="${idPrefix}-category-type-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Type</label>
            <select id="${idPrefix}-category-type-input" name="category_type_id" class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
              <option value="">Select...</option>
              ${opt(categoryTypesForCategory, 'category_type_id', 'category_type', existing?.categoryTypeId)}
            </select>
          </div>
        </div>
        <div class="space-y-4">
          <div>
            <label for="${idPrefix}-title-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Listing Title</label>
            <input type="text" id="${idPrefix}-title-input" name="listing_title" required value="${isEdit ? escapeAttr(existing.listingTitle) : ''}" placeholder="e.g. 2-Bedroom Downtown Condo for Rent"
              class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
          </div>
          <div>
            <label for="${idPrefix}-description-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Description</label>
            <textarea id="${idPrefix}-description-input" name="listing_description" rows="3" placeholder="Describe the listing..."
              class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500 resize-none">${isEdit ? escapeHtml(existing.listingDescription) : ''}</textarea>
          </div>
        </div>
      </div>

      <div id="${idPrefix}-property-block" class="${isService ? 'hidden' : ''} space-y-4">
        <h4 class="text-xs font-black uppercase tracking-widest text-teal-600 mb-3">Property Specs & Amenities</h4>

        <div>
          <label for="${idPrefix}-address-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Street Address</label>
          <input type="text" id="${idPrefix}-address-input" name="address" value="${isEdit ? escapeAttr(existing.address) : ''}" placeholder="e.g. 123 Main Street"
            class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
        </div>

        <div class="grid sm:grid-cols-3 gap-4">
          <div>
            <label for="${idPrefix}-unit-type-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Unit Type</label>
            <select id="${idPrefix}-unit-type-input" name="unit_type_id" class="unit-type-trigger w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
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
          <div>
            <label for="${idPrefix}-property-size-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Property Size (sq ft)</label>
            <input type="text" id="${idPrefix}-property-size-input" name="property_size" value="${isEdit ? escapeAttr(existing.propertySize) : ''}" placeholder="e.g. 950"
              class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
          </div>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label for="${idPrefix}-bedroom-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Bedrooms</label>
            <select id="${idPrefix}-bedroom-input" name="bedroom_id" class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
              <option value="">Select...</option>
              ${opt(lookups.bedrooms, 'bedroom_id', 'bedroom', existing?.bedroomId)}
            </select>
          </div>
          <div>
            <label for="${idPrefix}-bathroom-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Bathrooms</label>
            <select id="${idPrefix}-bathroom-input" name="bathroom_id" class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
              <option value="">Select...</option>
              ${opt(lookups.bathrooms, 'bathroom_id', 'bathroom', existing?.bathroomId)}
            </select>
          </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
          ${['is_ac:Air Conditioning', 'is_furnished:Furnished', 'parking:Parking', 'pets_allowed:Pets Allowed']
            .map((pair) => {
              const [field, label] = pair.split(':');
              const checked = isEdit && Number(existing[toCamel(field)]) === 1;
              return `
              <label class="flex items-center gap-2 text-xs font-bold text-gray-700 dark:text-gray-300 cursor-pointer bg-gray-50 dark:bg-gray-800 rounded-xl px-3 py-2.5">
                <input type="checkbox" name="${field}" value="1" ${checked ? 'checked' : ''} class="rounded border-gray-300 dark:border-gray-700 text-teal-600 focus:ring-teal-500">
                ${label}
              </label>`;
            })
            .join('')}
        </div>

        <div>
          <p class="text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">Amenities</p>
          <div class="grid sm:grid-cols-2 gap-4 bg-gray-50 dark:bg-gray-800/60 rounded-xl p-4">
            ${amenitiesHtml}
          </div>
        </div>

        <div class="grid sm:grid-cols-3 gap-4">
          <div>
            <label for="${idPrefix}-price-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Price / Rent</label>
            <input type="text" id="${idPrefix}-price-input" name="price" value="${isEdit ? escapeAttr(existing.price) : ''}" placeholder="e.g. 2,200/month"
              class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
          </div>
          <div>
            <label for="${idPrefix}-agreement-type-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Agreement Type</label>
            <select id="${idPrefix}-agreement-type-input" name="agreement_type_id" class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
              <option value="">Select...</option>
              ${opt(lookups.agreementTypes, 'agreement_type_id', 'agreement_type', existing?.agreementTypeId)}
            </select>
          </div>
          <div>
            <label for="${idPrefix}-move-in-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Move-in Date</label>
            <input type="date" id="${idPrefix}-move-in-input" name="move_in_date" value="${isEdit ? existing.moveInDate || '' : ''}" min="${todayIso()}"
              class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500">
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

      <div id="listing-form-error-slot"></div>

      <button type="submit" class="w-full py-3 bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm rounded-xl transition-colors">
        ${isEdit ? 'Save Changes' : 'Post Listing'}
      </button>
    </form>
  `;
}

function toCamel(field) {
  return field.replace(/_([a-z])/g, (_, c) => c.toUpperCase());
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

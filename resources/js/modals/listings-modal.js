// /resources/js/modals/listings-modal.js
//
// Opens the create/edit listing modal: fetches lookups (countries, plus the
// combined listing-category/category-type/unit-type/house-type/bedroom/
// bathroom/agreement-type/amenity-group bundle) in parallel, builds the form
// (listing-form.js), then wires dynamic region loading + the category ->
// category-type cascade + the category -> service-block visibility toggle +
// the unit-type -> house-type toggle + submit. Ported from the legacy
// gonachi/ platform's listings-modal.js.

import { Modal } from '../factories/modal-factory.js';
import { listingForm } from '../forms/listing-form.js';
import { enableDynamicRegionLoading } from '../components/regions-component.js';
import { handleListingFormSubmission } from '../utils/listings/listing-form-submit.js';

let cache = null;

async function loadLookups() {
  if (cache) return cache;

  const baseUrl = window.APP_CONFIG?.baseUrl || '/';
  const [countriesRes, lookupsRes] = await Promise.all([
    fetch(`${baseUrl}api/countries`).then((r) => r.json()),
    fetch(`${baseUrl}api/listing-lookups`).then((r) => r.json()),
  ]);

  cache = {
    countries: countriesRes.data || [],
    listingCategories: lookupsRes.listingCategories || [],
    listingCategoryTypes: lookupsRes.listingCategoryTypes || [],
    unitTypes: lookupsRes.unitTypes || [],
    houseTypes: lookupsRes.houseTypes || [],
    bedrooms: lookupsRes.bedrooms || [],
    bathrooms: lookupsRes.bathrooms || [],
    agreementTypes: lookupsRes.agreementTypes || [],
    amenityGroups: lookupsRes.amenityGroups || [],
  };

  return cache;
}

function isServiceCategory(categoryId) {
  return categoryId === 2 || categoryId === 3;
}

function toggleRequiredWithin(container, shouldRequire) {
  container.querySelectorAll('input, select, textarea').forEach((field) => {
    if (shouldRequire) {
      if (field.dataset.wasRequired === '1') field.required = true;
    } else {
      field.dataset.wasRequired = field.required ? '1' : '0';
      field.required = false;
    }
  });
}

function initFormFeatures(idPrefix, mode, modalInstance, existing, lookups) {
  const formId = `${idPrefix}-form`;
  enableDynamicRegionLoading(formId);

  const categorySelect = document.getElementById(`${idPrefix}-category-input`);
  const categoryTypeWrapper = document.getElementById(`${idPrefix}-category-type-wrapper`);
  const categoryTypeSelect = document.getElementById(`${idPrefix}-category-type-input`);
  const propertyBlock = document.getElementById(`${idPrefix}-property-block`);

  categorySelect?.addEventListener('change', () => {
    const categoryId = Number(categorySelect.value);

    categoryTypeWrapper.classList.toggle('hidden', categoryId === 3);
    const typesForCategory = lookups.listingCategoryTypes.filter((t) => Number(t.category_id) === categoryId);
    categoryTypeSelect.innerHTML =
      '<option value="">Select...</option>' + typesForCategory.map((t) => `<option value="${t.category_type_id}">${t.category_type}</option>`).join('');

    const service = isServiceCategory(categoryId);
    propertyBlock.classList.toggle('hidden', service);
    toggleRequiredWithin(propertyBlock, !service);
  });

  const unitTypeSelect = document.getElementById(`${idPrefix}-unit-type-input`);
  const houseTypeWrapper = document.getElementById(`${idPrefix}-house-type-wrapper`);
  unitTypeSelect?.addEventListener('change', () => {
    houseTypeWrapper.classList.toggle('hidden', unitTypeSelect.value !== '5');
  });

  if (existing?.countryId) {
    const countrySelect = document.getElementById(`${idPrefix}-country-input`);
    if (countrySelect) {
      countrySelect.value = String(existing.countryId);
      countrySelect.dispatchEvent(new CustomEvent('change', { detail: { preSelectedRegionId: existing.regionId } }));
    }
  }

  const form = document.getElementById(formId);
  if (form) {
    handleListingFormSubmission(form, mode, modalInstance);
  }
}

export async function openAddListingModal() {
  const lookups = await loadLookups();

  const modal = new Modal({
    id: 'add-listing-modal',
    title: 'Post a Listing',
    content: listingForm({ mode: 'add', lookups }),
    size: 'lg',
    showFooter: false,
  });

  modal.open();
  initFormFeatures('listing-add', 'add', modal, null, lookups);
}

export async function openEditListingModal(cardEl) {
  const lookups = await loadLookups();

  const existing = {
    encodedId: cardEl.dataset.encodedId,
    listingTitle: cardEl.dataset.listingTitle,
    listingDescription: cardEl.dataset.listingDescription,
    city: cardEl.dataset.city,
    address: cardEl.dataset.address,
    countryId: cardEl.dataset.countryId,
    regionId: cardEl.dataset.regionId,
    categoryId: cardEl.dataset.categoryId,
    categoryTypeId: cardEl.dataset.categoryTypeId,
    unitTypeId: cardEl.dataset.unitTypeId,
    houseTypeId: cardEl.dataset.houseTypeId,
    bedroomId: cardEl.dataset.bedroomId,
    bathroomId: cardEl.dataset.bathroomId,
    propertySize: cardEl.dataset.propertySize,
    isAc: cardEl.dataset.isAc,
    isFurnished: cardEl.dataset.isFurnished,
    parking: cardEl.dataset.parking,
    petsAllowed: cardEl.dataset.petsAllowed,
    price: cardEl.dataset.price,
    agreementTypeId: cardEl.dataset.agreementTypeId,
    moveInDate: cardEl.dataset.moveInDate,
    amenities: JSON.parse(cardEl.dataset.amenities || '[]'),
    youtubeUrl: cardEl.dataset.youtubeUrl,
    contactPhone: cardEl.dataset.contactPhone,
  };

  const modal = new Modal({
    id: 'edit-listing-modal',
    title: 'Edit Listing',
    content: listingForm({ mode: 'edit', lookups, existing }),
    size: 'lg',
    showFooter: false,
  });

  modal.open();
  initFormFeatures('listing-edit', 'edit', modal, existing, lookups);
}

export function initListingsModalTriggers() {
  if (document._listingsModalTriggersAttached) return;
  document._listingsModalTriggersAttached = true;

  document.addEventListener('click', (e) => {
    if (e.target.closest('.create-listing-trigger')) {
      openAddListingModal();
      return;
    }

    const editBtn = e.target.closest('.edit-listing-btn, #view-listing-edit-btn');
    if (editBtn) {
      const encodedId = editBtn.dataset.encodedId || document.getElementById('view-listing-modal')?.dataset.activeEncodedId;
      const card = document.querySelector(`[data-encoded-id="${encodedId}"].listing-card-wrapper`);
      if (card) {
        document.getElementById('view-listing-modal')?.classList.add('hidden');
        openEditListingModal(card);
      }
    }
    // Capture phase: the card's own edit/delete buttons sit inside a wrapper
    // with onclick="event.stopPropagation()" (so clicking them doesn't also
    // open the view modal underneath) — that stops the click from ever
    // reaching a bubble-phase document listener, so this one has to run on
    // the way down instead.
  }, true);
}

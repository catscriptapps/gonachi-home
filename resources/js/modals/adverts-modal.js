// /resources/js/modals/adverts-modal.js
//
// Opens the create/edit advert modal: fetches countries, user types, CTAs,
// and packages in parallel, builds the form (advert-form.js), then wires
// targeting pickers + submit. Ported from the legacy gonachi/ platform's
// adverts-modal.js.

import { Modal } from '../factories/modal-factory.js';
import { advertForm } from '../forms/advert-form.js';
import { initCountryTargeting } from '../utils/adverts/country-targeting.js';
import { handleAdFormSubmission } from '../utils/adverts/form-submit.js';
import { createPhotoStrip } from '../utils/compose-photo-strip.js';

let cache = null;

// One strip shared between the add and edit modals (never open at the same
// time) — see compose-photo-strip.js's docblock for why this isn't a
// module-level singleton shared across *every* type's compose modal.
const photoStrip = createPhotoStrip({ uploadPath: 'api/advert-photo-upload', altText: 'Advert photo' });

async function loadLookups() {
  if (cache) return cache;

  const baseUrl = window.APP_CONFIG?.baseUrl || '/';
  const [countriesRes, ctasRes, packagesRes] = await Promise.all([
    fetch(`${baseUrl}api/countries`).then((r) => r.json()),
    fetch(`${baseUrl}api/advert-ctas`).then((r) => r.json()),
    fetch(`${baseUrl}api/advert-packages`).then((r) => r.json()),
  ]);

  cache = {
    countries: countriesRes.data || [],
    ctas: ctasRes.ctas || [],
    packages: packagesRes.packages || [],
  };

  return cache;
}

function populateCountrySelector(idPrefix, countries) {
  const selector = document.getElementById(`${idPrefix}-country-selector`);
  if (!selector) return;

  countries.forEach((c) => {
    const opt = document.createElement('option');
    opt.value = String(c.id);
    opt.dataset.name = c.country;
    opt.textContent = c.country;
    selector.appendChild(opt);
  });
}

function initFormFeatures(idPrefix, mode, modalInstance, initialCountries) {
  populateCountrySelector(idPrefix, cache.countries);
  initCountryTargeting({ idPrefix, initialSelection: initialCountries });

  document.querySelectorAll(`#${idPrefix}-form .package-option`).forEach((label) => {
    label.addEventListener('click', () => {
      if (label.querySelector('input[type="radio"]')?.disabled) return;
      document.querySelectorAll(`#${idPrefix}-form .package-option`).forEach((l) => {
        l.classList.remove('border-teal-500', 'bg-teal-50', 'dark:bg-teal-950/30');
        l.classList.add('border-gray-200', 'dark:border-gray-700');
      });
      label.classList.add('border-teal-500', 'bg-teal-50', 'dark:bg-teal-950/30');
      label.classList.remove('border-gray-200', 'dark:border-gray-700');
    });
  });

  photoStrip.wire(idPrefix);

  const form = document.getElementById(`${idPrefix}-form`);
  const gridSelector = document.getElementById('ads-grid') ? '#ads-grid' : null;
  if (form && gridSelector) {
    handleAdFormSubmission(form, mode, modalInstance, gridSelector, photoStrip);
  }
}

async function fetchExistingPhotos(encodedId) {
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';

  try {
    const response = await fetch(`${baseUrl}api/advert-pictures?id=${encodeURIComponent(encodedId)}`);
    const result = await response.json();
    return result.pictures || [];
  } catch (err) {
    console.error('Load existing advert pictures error:', err);
    return [];
  }
}

export async function openAddAdModal() {
  const { ctas, packages } = await loadLookups();
  photoStrip.setPhotos([]);

  const modal = new Modal({
    id: 'add-ad-modal',
    title: 'New Advert',
    content: advertForm({ mode: 'add', ctas, packages }),
    size: 'lg',
    showFooter: false,
    onDismiss: () => photoStrip.discardAbandoned(),
  });

  modal.open();
  initFormFeatures('ad-add', 'add', modal, []);
}

export async function openEditAdModal(cardEl) {
  const { ctas, packages } = await loadLookups();

  const existing = {
    encodedId: cardEl.dataset.encodedId,
    title: cardEl.dataset.title,
    description: cardEl.dataset.description,
    keywords: cardEl.dataset.keywords,
    landingPageUrl: cardEl.dataset.landingPageUrl,
    callToActionId: parseInt(cardEl.dataset.callToActionId, 10) || null,
    package: parseInt(cardEl.dataset.advertPackage, 10) || 1,
    countries: JSON.parse(cardEl.dataset.selectedCountries || '["ALL"]'),
  };

  photoStrip.setPhotos(await fetchExistingPhotos(existing.encodedId));

  const modal = new Modal({
    id: 'edit-ad-modal',
    title: 'Edit Advert',
    content: advertForm({ mode: 'edit', ctas, packages, existing }),
    size: 'lg',
    showFooter: false,
    onDismiss: () => photoStrip.discardAbandoned(),
  });

  modal.open();
  const initialCountries = existing.countries.includes('ALL') ? [] : existing.countries;
  initFormFeatures('ad-edit', 'edit', modal, initialCountries);
}

export function initAdvertsModalTriggers() {
  if (document._advertsModalTriggersAttached) return;
  document._advertsModalTriggersAttached = true;

  document.addEventListener('click', (e) => {
    if (e.target.closest('.create-ad-trigger')) {
      openAddAdModal();
      return;
    }

    const editBtn = e.target.closest('.edit-ad-btn, #view-ad-edit-btn');
    if (editBtn) {
      const encodedId = editBtn.dataset.encodedId || document.getElementById('view-ad-modal')?.dataset.activeEncodedId;
      const card = document.querySelector(`[data-encoded-id="${encodedId}"]`);
      if (card) {
        document.getElementById('view-ad-modal')?.classList.add('hidden');
        openEditAdModal(card);
      }
    }
    // Capture phase: the card's own edit/delete buttons sit inside a
    // wrapper with onclick="event.stopPropagation()" (so clicking them
    // doesn't also open the view modal underneath) — that stops the click
    // from ever reaching a bubble-phase document listener, so this one has
    // to run on the way down instead.
  }, true);
}

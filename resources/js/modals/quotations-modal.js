// /resources/js/modals/quotations-modal.js
//
// Opens the create/edit quotation modal: fetches lookups (countries, plus
// the combined contractor-type/skilled-trade/unit-type/house-type/
// quotation-type/destination bundle) in parallel, builds the form
// (quotation-form.js), then wires dynamic region loading + the
// unit-type -> house-type toggle + submit. Ported from the legacy
// gonachi/ platform's quotations-modal.js.

import { Modal } from '../factories/modal-factory.js';
import { quotationForm } from '../forms/quotation-form.js';
import { enableDynamicRegionLoading } from '../components/regions-component.js';
import { handleQuoteFormSubmission } from '../utils/quotations/form-submit.js';

let cache = null;

async function loadLookups() {
  if (cache) return cache;

  const baseUrl = window.APP_CONFIG?.baseUrl || '/';
  const [countriesRes, lookupsRes] = await Promise.all([
    fetch(`${baseUrl}api/countries`).then((r) => r.json()),
    fetch(`${baseUrl}api/quotation-lookups`).then((r) => r.json()),
  ]);

  cache = {
    countries: countriesRes.data || [],
    contractorTypes: lookupsRes.contractorTypes || [],
    skilledTrades: lookupsRes.skilledTrades || [],
    unitTypes: lookupsRes.unitTypes || [],
    houseTypes: lookupsRes.houseTypes || [],
    quotationTypes: lookupsRes.quotationTypes || [],
    destinations: lookupsRes.destinations || [],
  };

  return cache;
}

function initFormFeatures(idPrefix, mode, modalInstance, existing) {
  const formId = `${idPrefix}-form`;
  enableDynamicRegionLoading(formId);

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
    handleQuoteFormSubmission(form, mode, modalInstance);
  }
}

export async function openAddQuoteModal() {
  const lookups = await loadLookups();

  const modal = new Modal({
    id: 'add-quote-modal',
    title: 'New Quotation',
    content: quotationForm({ mode: 'add', lookups }),
    size: 'lg',
    showFooter: false,
  });

  modal.open();
  initFormFeatures('quote-add', 'add', modal, null);
}

export async function openEditQuoteModal(cardEl) {
  const lookups = await loadLookups();

  const existing = {
    encodedId: cardEl.dataset.encodedId,
    title: cardEl.dataset.title,
    description: cardEl.dataset.description,
    city: cardEl.dataset.city,
    countryId: cardEl.dataset.countryId,
    regionId: cardEl.dataset.regionId,
    contractorTypeId: cardEl.dataset.contractorTypeId,
    skilledTradeId: cardEl.dataset.skilledTradeId,
    unitTypeId: cardEl.dataset.unitTypeId,
    houseTypeId: cardEl.dataset.houseTypeId,
    quotationTypeId: cardEl.dataset.quotationTypeId,
    quotationDestId: cardEl.dataset.quotationDestId,
    budget: cardEl.dataset.budget,
    startDate: cardEl.dataset.startDate,
    finishDate: cardEl.dataset.finishDate,
    startTime: cardEl.dataset.startTime,
    finishTime: cardEl.dataset.finishTime,
    youtubeUrl: cardEl.dataset.youtubeUrl,
    contactPhone: cardEl.dataset.contactPhone,
  };

  const modal = new Modal({
    id: 'edit-quote-modal',
    title: 'Edit Quotation',
    content: quotationForm({ mode: 'edit', lookups, existing }),
    size: 'lg',
    showFooter: false,
  });

  modal.open();
  initFormFeatures('quote-edit', 'edit', modal, existing);
}

export function initQuotationsModalTriggers() {
  if (document._quotationsModalTriggersAttached) return;
  document._quotationsModalTriggersAttached = true;

  document.addEventListener('click', (e) => {
    if (e.target.closest('.create-quote-trigger')) {
      openAddQuoteModal();
      return;
    }

    const editBtn = e.target.closest('.edit-quote-btn, #view-quote-edit-btn');
    if (editBtn) {
      const encodedId = editBtn.dataset.encodedId || document.getElementById('view-quote-modal')?.dataset.activeEncodedId;
      const card = document.querySelector(`[data-encoded-id="${encodedId}"].quote-card-wrapper`);
      if (card) {
        document.getElementById('view-quote-modal')?.classList.add('hidden');
        openEditQuoteModal(card);
      }
    }
    // Capture phase: the card's on-card edit/delete buttons sit inside a
    // wrapper with onclick="event.stopPropagation()" (so clicking them
    // doesn't also open the view modal underneath) — that stops the click
    // from ever reaching a bubble-phase document listener, so this one
    // has to run on the way down instead.
  }, true);
}

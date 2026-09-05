// /resources/js/modals/mentors-modal.js
//
// Opens the create/edit mentor profile modal: fetches lookups (countries,
// user types filtered to exclude Admin) in parallel, builds the form
// (mentor-form.js), then wires dynamic region loading + submit. Ported
// from the legacy gonachi/ platform's mentors-modal.js.

import { Modal } from '../factories/modal-factory.js';
import { mentorForm } from '../forms/mentor-form.js';
import { enableDynamicRegionLoading } from '../components/regions-component.js';
import { handleMentorFormSubmission } from '../utils/mentors/form-submit.js';

let cache = null;

async function loadLookups() {
  if (cache) return cache;

  const baseUrl = window.APP_CONFIG?.baseUrl || '/';
  const [countriesRes, userTypesRes] = await Promise.all([
    fetch(`${baseUrl}api/countries`).then((r) => r.json()),
    fetch(`${baseUrl}api/user-types`).then((r) => r.json()),
  ]);

  const allTypes = userTypesRes.data || [];

  cache = {
    countries: countriesRes.data || [],
    mentorTypes: allTypes.filter((t) => String(t.user_type).toLowerCase() !== 'admin'),
  };

  return cache;
}

function initFormFeatures(idPrefix, mode, modalInstance, existing) {
  const formId = `${idPrefix}-form`;
  enableDynamicRegionLoading(formId);

  if (existing?.countryId) {
    const countrySelect = document.getElementById(`${idPrefix}-country-input`);
    if (countrySelect) {
      countrySelect.value = String(existing.countryId);
      countrySelect.dispatchEvent(new CustomEvent('change', { detail: { preSelectedRegionId: existing.regionId } }));
    }
  }

  const form = document.getElementById(formId);
  if (form) {
    handleMentorFormSubmission(form, mode, modalInstance);
  }
}

export async function openAddMentorModal() {
  const lookups = await loadLookups();

  const modal = new Modal({
    id: 'add-mentor-modal',
    title: 'Become a Mentor',
    content: mentorForm({ mode: 'add', lookups }),
    size: 'lg',
    showFooter: false,
  });

  modal.open();
  initFormFeatures('mentor-add', 'add', modal, null);
}

export async function openEditMentorModal(cardEl) {
  const lookups = await loadLookups();

  const existing = {
    encodedId: cardEl.dataset.encodedId,
    headline: cardEl.dataset.headline,
    bio: cardEl.dataset.bio,
    city: cardEl.dataset.city,
    countryId: cardEl.dataset.countryId,
    regionId: cardEl.dataset.regionId,
    targetTypeId: cardEl.dataset.targetTypeId,
    experienceYears: cardEl.dataset.experienceYears,
    youtubeUrl: cardEl.dataset.youtubeUrl,
    websiteUrl: cardEl.dataset.websiteUrl,
    skills: (JSON.parse(cardEl.dataset.skillsJson || '[]') || []).join(', '),
  };

  const modal = new Modal({
    id: 'edit-mentor-modal',
    title: 'Edit Mentor Profile',
    content: mentorForm({ mode: 'edit', lookups, existing }),
    size: 'lg',
    showFooter: false,
  });

  modal.open();
  initFormFeatures('mentor-edit', 'edit', modal, existing);
}

export function initMentorsModalTriggers() {
  if (document._mentorsModalTriggersAttached) return;
  document._mentorsModalTriggersAttached = true;

  document.addEventListener('click', (e) => {
    if (e.target.closest('.register-mentor-trigger')) {
      openAddMentorModal();
      return;
    }

    const editBtn = e.target.closest('.edit-mentor-btn, #view-mentor-edit-btn');
    if (editBtn) {
      const encodedId = editBtn.dataset.encodedId || document.getElementById('view-mentor-modal')?.dataset.activeEncodedId;
      const card = document.querySelector(`[data-encoded-id="${encodedId}"].mentor-card-wrapper`);
      if (card) {
        document.getElementById('view-mentor-modal')?.classList.add('hidden');
        openEditMentorModal(card);
      }
    }
    // Capture phase: the card's own edit/delete buttons sit inside a
    // wrapper with onclick="event.stopPropagation()" (so clicking them
    // doesn't also open the view modal underneath) — that stops the click
    // from ever reaching a bubble-phase document listener, so this one
    // has to run on the way down instead.
  }, true);
}

// /resources/js/utils/mentors/form-submit.js
//
// Handles create/edit mentor profile form submission. Adapted from the
// legacy gonachi/ platform's mentor form-submit.js (same payload shape).

import { FormValidator } from '../form-validator.js';
import { AnimationEngine } from '../animations.js';

function getPayload(form) {
  const formData = new FormData(form);
  const data = Object.fromEntries(formData.entries());

  return {
    encoded_id: form.dataset.encodedId || null,
    headline: data.headline?.trim(),
    bio: data.bio?.trim(),
    skills: data.skills?.trim(),
    years_experience: parseInt(data.years_experience || 0, 10),
    target_stakeholder_type_id: parseInt(data.target_stakeholder_type_id || 0, 10),
    country_id: parseInt(data.countryId || 0, 10),
    region_id: parseInt(data.regionId || 0, 10),
    city: data.city?.trim(),
    youtube_url: data.youtube_url?.trim(),
    website_url: data.website_url?.trim(),
  };
}

export function handleMentorFormSubmission(form, mode, modalInstance) {
  if (form._mentorFormListenerAttached) return;
  form._mentorFormListenerAttached = true;

  const validator = new FormValidator(form);
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalLabel = submitBtn.innerHTML;

  let apiMsg = form.querySelector('.api-message');
  if (!apiMsg) {
    apiMsg = document.createElement('div');
    apiMsg.className = 'api-message mt-4';
    form.appendChild(apiMsg);
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    apiMsg.innerHTML = '';

    if (!validator.validateForEmptyFields(e)) return;

    submitBtn.disabled = true;
    submitBtn.textContent = mode === 'edit' ? 'Saving...' : 'Registering...';

    try {
      const payload = getPayload(form);
      const baseUrl = window.APP_CONFIG?.baseUrl || '/';
      const response = await fetch(`${baseUrl}api/mentors`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      const result = await response.json();

      if (result.success) {
        const grid = document.getElementById('mentors-grid');

        if (grid) {
          grid.classList.remove('hidden');
          document.getElementById('empty-mentors-state')?.classList.add('hidden');

          if (mode === 'edit') {
            const existingCard = grid.querySelector(`.mentor-card-wrapper[data-encoded-id="${payload.encoded_id}"]`);
            if (existingCard) existingCard.outerHTML = result.cardHtml;
          } else {
            grid.insertAdjacentHTML('afterbegin', result.cardHtml);
          }

          AnimationEngine.refresh();
        }

        apiMsg.innerHTML = `<div class="bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/40 text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-xl font-bold text-sm mt-2 text-center">
          ${mode === 'edit' ? 'Profile updated.' : 'You are now a mentor!'}
        </div>`;

        setTimeout(() => modalInstance?.close?.(), 1200);
      } else {
        apiMsg.innerHTML = (result.messages || ['Error saving mentor profile.'])
          .map((msg) => `<div class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/40 text-red-700 dark:text-red-400 px-4 py-2 rounded-xl font-bold text-sm mt-2">${msg}</div>`)
          .join('');
      }
    } catch (err) {
      console.error('Mentor profile submission error:', err);
      apiMsg.innerHTML = `<div class="bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-400 px-4 py-2 rounded-xl font-bold text-sm mt-2">Server communication failed.</div>`;
    } finally {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalLabel;
    }
  });
}

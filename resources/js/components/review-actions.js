// /resources/js/components/review-actions.js
//
// "Report Review" (landlord_and_tenant_validation.pdf §9) and "Respond"
// (§10) actions on landlords/detail.php and tenants/detail.php — delegated
// on document (like review-queue.js) so it works across SPA partial-load
// navigations without re-binding per page. Both open a small lazily-built
// Modal (resources/js/factories/modal-factory.js), same convention as
// reset-modal.js.

import { Modal } from '../factories/modal-factory.js';
import { showToast } from '../ui/toast.js';

const REPORT_REASONS = [
  ['no_tenancy_relationship', 'I did not have a tenancy relationship with this person.'],
  ['false_information', 'Contains false factual information.'],
  ['discriminatory_hateful', 'Contains discriminatory or hateful content.'],
  ['threats_harassment', 'Contains threats or harassment.'],
  ['private_personal_information', 'Contains private/personal information.'],
  ['irrelevant_information', 'Contains irrelevant information.'],
  ['conflict_of_interest', 'Conflict of interest.'],
  ['other', 'Other.'],
];

export function initReviewActions() {
  if (document._reviewActionsAttached) return;
  document._reviewActionsAttached = true;

  const baseUrl = window.APP_CONFIG?.baseUrl || '/';

  document.addEventListener('click', (e) => {
    const reportBtn = e.target.closest('[data-report-review]');
    if (reportBtn) {
      openReportModal(Number(reportBtn.dataset.reportReview), baseUrl);
      return;
    }

    const respondBtn = e.target.closest('[data-respond-review]');
    if (respondBtn) {
      openRespondModal(Number(respondBtn.dataset.respondReview), baseUrl);
    }
  });
}

function openReportModal(reviewId, baseUrl) {
  const content = `
    <form id="review-report-form" class="space-y-4">
      <div>
        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Reason</label>
        <select name="reason" required class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-900 dark:text-white">
          ${REPORT_REASONS.map(([key, label]) => `<option value="${key}">${label}</option>`).join('')}
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Details <span class="normal-case font-medium text-gray-400">(optional)</span></label>
        <textarea name="details" rows="3" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-900 dark:text-white resize-none"></textarea>
      </div>
      <div id="review-report-message"></div>
      <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold text-sm rounded-lg transition-colors shadow-sm">Submit Report</button>
    </form>
  `;

  const modal = new Modal({ id: 'review-action-modal', title: 'Report Review', content, size: 'sm', showFooter: false });
  modal.open();

  const form = document.getElementById('review-report-form');
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(form);
    const messageBox = document.getElementById('review-report-message');
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;

    try {
      const response = await fetch(`${baseUrl}api/review-report`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          review_id: reviewId,
          reason: formData.get('reason'),
          details: formData.get('details'),
        }),
      });
      const result = await response.json();

      if (result.success) {
        showToast(result.messages?.[0] || 'Report submitted.', 'success');
        modal.close();
      } else {
        messageBox.innerHTML = `<p class="text-xs text-red-600 dark:text-red-400">${(result.messages || ['Please try again.']).join(' ')}</p>`;
        submitBtn.disabled = false;
      }
    } catch (err) {
      console.error('Report review failed:', err);
      messageBox.innerHTML = `<p class="text-xs text-red-600 dark:text-red-400">Unexpected error. Please try again.</p>`;
      submitBtn.disabled = false;
    }
  });
}

function openRespondModal(reviewId, baseUrl) {
  const content = `
    <form id="review-respond-form" class="space-y-4">
      <div>
        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Your Response</label>
        <textarea name="response" required rows="4" placeholder="A professional response to this review — you only get one, so make it count." class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-900 dark:text-white resize-none"></textarea>
      </div>
      <div id="review-respond-message"></div>
      <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm rounded-lg transition-colors shadow-sm">Post Response</button>
    </form>
  `;

  const modal = new Modal({ id: 'review-action-modal', title: 'Respond to Review', content, size: 'sm', showFooter: false });
  modal.open();

  const form = document.getElementById('review-respond-form');
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(form);
    const messageBox = document.getElementById('review-respond-message');
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;

    try {
      const response = await fetch(`${baseUrl}api/review-response`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ review_id: reviewId, response: formData.get('response') }),
      });
      const result = await response.json();

      if (result.success) {
        showToast(result.messages?.[0] || 'Response posted.', 'success');
        modal.close();
        const currentUrl = window.location.pathname + window.location.search;
        if (window.loadPartial) window.loadPartial(currentUrl, false);
      } else {
        messageBox.innerHTML = `<p class="text-xs text-red-600 dark:text-red-400">${(result.messages || ['Please try again.']).join(' ')}</p>`;
        submitBtn.disabled = false;
      }
    } catch (err) {
      console.error('Respond to review failed:', err);
      messageBox.innerHTML = `<p class="text-xs text-red-600 dark:text-red-400">Unexpected error. Please try again.</p>`;
      submitBtn.disabled = false;
    }
  });
}

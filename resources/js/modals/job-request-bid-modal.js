// /resources/js/modals/job-request-bid-modal.js
//
// Opens the "Submit A Quote" bid modal for a job request. Mirrors the Real
// Estate World Quotations module's quotation-response-modal.js —
// pre-flight self-bid check client-side, posts to
// api/job-request-responses, auto-closes on success.

import { Modal } from '../factories/modal-factory.js';
import { jobRequestBidForm } from '../forms/job-request-bid-form.js';
import { showToast } from '../ui/toast.js';

export function openJobRequestBidModal({ jobId, ownerId, jobTitle }) {
  const currentUserId = window.sessionUserId ? Number(window.sessionUserId) : null;
  if (currentUserId !== null && Number(ownerId) === currentUserId) {
    showToast('You cannot submit a quote on your own job request.', 'error');
    return;
  }

  const modal = new Modal({
    id: 'job-request-bid-modal',
    title: 'Submit A Quote',
    content: jobRequestBidForm({ jobId, jobTitle }),
    size: 'md',
    showFooter: false,
  });

  modal.open();

  const form = document.getElementById('job-request-bid-form');
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalLabel = submitBtn.innerHTML;
  const messageSlot = document.getElementById('job-request-bid-message-slot');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    messageSlot.innerHTML = '';

    const message = form.querySelector('[name="message"]').value.trim();
    if (!message) {
      messageSlot.innerHTML = `<p class="text-xs text-red-600">A message is required.</p>`;
      return;
    }

    submitBtn.disabled = true;
    submitBtn.textContent = 'Sending...';

    try {
      const baseUrl = window.APP_CONFIG?.baseUrl || '/';
      const quoteAmount = form.querySelector('[name="quote_amount"]').value.trim();

      const response = await fetch(`${baseUrl}api/job-request-responses`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ job_request_id: jobId, quote_amount: quoteAmount, message }),
      });
      const result = await response.json();

      if (result.success) {
        messageSlot.innerHTML = `<div class="bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/40 text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-xl font-bold text-sm text-center">Quote sent!</div>`;
        setTimeout(() => modal.close(), 1200);
      } else {
        messageSlot.innerHTML = `<p class="text-xs text-red-600">${result.message || 'Could not send quote.'}</p>`;
      }
    } catch (err) {
      console.error('Send job request bid error:', err);
      messageSlot.innerHTML = `<p class="text-xs text-red-600">Unexpected error.</p>`;
    } finally {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalLabel;
    }
  });
}

export function initJobRequestBidTriggers() {
  if (document._jobRequestBidTriggersAttached) return;
  document._jobRequestBidTriggersAttached = true;

  document.addEventListener('click', (e) => {
    const trigger = e.target.closest('.submit-quote-trigger');
    if (!trigger) return;

    e.preventDefault();

    const { jobId, ownerId, jobTitle } = trigger.dataset;
    if (!jobId || !ownerId) return;

    openJobRequestBidModal({ jobId, ownerId, jobTitle });
  });
}

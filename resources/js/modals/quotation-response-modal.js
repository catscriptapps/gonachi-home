// /resources/js/modals/quotation-response-modal.js
//
// Opens the "Connect with Owner" / bid modal. Ported from the legacy
// gonachi/ platform's quotations-connect-modal.js — pre-flight self-bid
// check client-side, posts to api/quotation-responses, auto-closes on success.

import { Modal } from '../factories/modal-factory.js';
import { quotationResponseForm } from '../forms/quotation-response-form.js';
import { showToast } from '../ui/toast.js';

export function openQuotationResponseModal({ encodedId, ownerId, title }) {
  const currentUserId = window.sessionUserId ? Number(window.sessionUserId) : null;
  if (currentUserId !== null && Number(ownerId) === currentUserId) {
    showToast('You cannot bid on your own quotation.', 'error');
    return;
  }

  const modal = new Modal({
    id: 'quote-response-modal',
    title: 'Connect with Owner',
    content: quotationResponseForm({ encodedId, ownerId, title }),
    size: 'md',
    showFooter: false,
  });

  modal.open();

  const form = document.getElementById('quote-response-form');
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalLabel = submitBtn.innerHTML;
  const messageSlot = document.getElementById('quote-response-message-slot');

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
      const response = await fetch(`${baseUrl}api/quotation-responses`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ quotation_id: encodedId, receiver_id: ownerId, message }),
      });
      const result = await response.json();

      if (result.success) {
        messageSlot.innerHTML = `<div class="bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/40 text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-xl font-bold text-sm text-center">Proposal sent!</div>`;
        setTimeout(() => modal.close(), 1200);
      } else {
        messageSlot.innerHTML = `<p class="text-xs text-red-600">${result.message || 'Could not send proposal.'}</p>`;
      }
    } catch (err) {
      console.error('Send quotation response error:', err);
      messageSlot.innerHTML = `<p class="text-xs text-red-600">Unexpected error.</p>`;
    } finally {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalLabel;
    }
  });
}

export function initQuotationResponseTriggers() {
  if (document._quoteResponseTriggersAttached) return;
  document._quoteResponseTriggersAttached = true;

  // Capture phase: the card's own Connect button sits inside a wrapper with
  // onclick="event.stopPropagation()" (so clicking it doesn't also open the
  // view modal underneath) — that stops the click from ever reaching a
  // bubble-phase document listener, so this one has to run on the way down.
  document.addEventListener('click', (e) => {
    const trigger = e.target.closest('.connect-quote-trigger, #view-quote-primary-btn.is-connect');
    if (!trigger) return;

    const encodedId = trigger.dataset.encodedId || document.getElementById('view-quote-modal')?.dataset.activeEncodedId;
    const ownerId = trigger.dataset.ownerId || document.getElementById('view-quote-modal')?.dataset.ownerId;
    const title = trigger.dataset.title || document.getElementById('view-quote-title')?.textContent || '';

    if (!encodedId || !ownerId) return;

    document.getElementById('view-quote-modal')?.classList.add('hidden');
    openQuotationResponseModal({ encodedId, ownerId, title });
  }, true);
}

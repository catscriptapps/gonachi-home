// /resources/js/modals/listing-response-modal.js
//
// Opens the "Contact Owner" / inquiry modal. Ported from the legacy
// gonachi/ platform's listings-connect-modal.js — pre-flight self-inquiry
// check client-side, posts to api/listing-responses, auto-closes on success.

import { Modal } from '../factories/modal-factory.js';
import { listingResponseForm } from '../forms/listing-response-form.js';
import { showToast } from '../ui/toast.js';

export function openListingResponseModal({ encodedId, ownerId, title }) {
  const currentUserId = window.sessionUserId ? Number(window.sessionUserId) : null;
  if (currentUserId !== null && Number(ownerId) === currentUserId) {
    showToast('This is your own listing.', 'error');
    return;
  }

  const modal = new Modal({
    id: 'listing-response-modal',
    title: 'Contact Owner',
    content: listingResponseForm({ encodedId, ownerId, title }),
    size: 'md',
    showFooter: false,
  });

  modal.open();

  const form = document.getElementById('listing-response-form');
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalLabel = submitBtn.innerHTML;
  const messageSlot = document.getElementById('listing-response-message-slot');

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
      const response = await fetch(`${baseUrl}api/listing-responses`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ listing_id: encodedId, receiver_id: ownerId, message }),
      });
      const result = await response.json();

      if (result.success) {
        messageSlot.innerHTML = `<div class="bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/40 text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-xl font-bold text-sm text-center">Message sent!</div>`;
        setTimeout(() => modal.close(), 1200);
      } else {
        messageSlot.innerHTML = `<p class="text-xs text-red-600">${result.message || 'Could not send message.'}</p>`;
      }
    } catch (err) {
      console.error('Send listing response error:', err);
      messageSlot.innerHTML = `<p class="text-xs text-red-600">Unexpected error.</p>`;
    } finally {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalLabel;
    }
  });
}

export function initListingResponseTriggers() {
  if (document._listingResponseTriggersAttached) return;
  document._listingResponseTriggersAttached = true;

  // Capture phase: the card's own Contact Owner button sits inside a
  // wrapper with onclick="event.stopPropagation()" (so clicking it doesn't
  // also open the view modal underneath) — that stops the click from ever
  // reaching a bubble-phase document listener, so this one has to run on
  // the way down.
  document.addEventListener('click', (e) => {
    const trigger = e.target.closest('.connect-listing-trigger, #view-listing-primary-btn.is-connect');
    if (!trigger) return;

    const encodedId = trigger.dataset.encodedId || document.getElementById('view-listing-modal')?.dataset.activeEncodedId;
    const ownerId = trigger.dataset.ownerId || document.getElementById('view-listing-modal')?.dataset.ownerId;
    const title = trigger.dataset.listingTitle || document.getElementById('view-listing-title')?.textContent || '';

    if (!encodedId || !ownerId) return;

    document.getElementById('view-listing-modal')?.classList.add('hidden');
    openListingResponseModal({ encodedId, ownerId, title });
  }, true);
}

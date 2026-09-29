// /resources/js/modals/swap-listing-response-modal.js
//
// Opens the "Connect with Owner" modal for a Swap Marketplace listing.
// Mirrors Real Estate World's quotation-response-modal.js — pre-flight
// self-message check client-side, posts to api/swap-listing-responses,
// auto-closes on success.

import { Modal } from '../factories/modal-factory.js';
import { swapListingResponseForm } from '../forms/swap-listing-response-form.js';
import { showToast } from '../ui/toast.js';
import { openAuthGateModal } from './auth-gate-modal.js';

export function openSwapListingResponseModal({ encodedId, ownerId, title }) {
  const currentUserId = window.sessionUserId ? Number(window.sessionUserId) : null;
  if (currentUserId !== null && Number(ownerId) === currentUserId) {
    showToast('You cannot respond to your own listing.', 'error');
    return;
  }

  const modal = new Modal({
    id: 'swap-response-modal',
    title: 'Connect with Owner',
    content: swapListingResponseForm({ encodedId, ownerId, title }),
    size: 'md',
    showFooter: false,
  });

  modal.open();

  const form = document.getElementById('swap-response-form');
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalLabel = submitBtn.innerHTML;
  const messageSlot = document.getElementById('swap-response-message-slot');

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
      const response = await fetch(`${baseUrl}api/swap-listing-responses`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ listing_id: encodedId, receiver_id: ownerId, message }),
      });
      const result = await response.json();

      if (result.success) {
        // Flip the card behind the modal to its "Message Sent" state.
        if (result.cardHtml) {
          document.querySelectorAll(`[data-listing-wrapper][data-encoded-id="${encodedId}"]`).forEach((el) => {
            el.outerHTML = result.cardHtml;
          });
        }

        messageSlot.innerHTML = `<div class="bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/40 text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-xl font-bold text-sm text-center">Message sent!</div>`;
        setTimeout(() => modal.close(), 1200);
      } else {
        messageSlot.innerHTML = `<p class="text-xs text-red-600">${result.message || 'Could not send message.'}</p>`;
      }
    } catch (err) {
      console.error('Send Swap Marketplace response error:', err);
      messageSlot.innerHTML = `<p class="text-xs text-red-600">Unexpected error.</p>`;
    } finally {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalLabel;
    }
  });
}

export function initSwapListingResponseTriggers() {
  if (document._swapResponseTriggersAttached) return;
  document._swapResponseTriggersAttached = true;

  // Capture phase: the card's own Connect button sits inside a wrapper with
  // onclick="event.stopPropagation()" (so clicking it doesn't also open the
  // view modal underneath) — that stops the click from ever reaching a
  // bubble-phase document listener, so this one has to run on the way down.
  document.addEventListener('click', (e) => {
    const trigger = e.target.closest('.connect-swap-trigger, #view-swap-primary-btn.is-connect');
    if (!trigger) return;

    // Guests get the sign-in gate. Handled here (capture phase) rather than
    // via .auth-gate-btn, whose bubble-phase handler never sees clicks that
    // the card's stopPropagation wrapper swallows.
    if (!window.sessionUserId) {
      e.preventDefault();
      e.stopImmediatePropagation();
      openAuthGateModal();
      return;
    }

    const viewModal = document.getElementById('view-swap-modal');
    const encodedId = trigger.dataset.encodedId || viewModal?.dataset.activeEncodedId;
    const ownerId = trigger.dataset.ownerId || viewModal?.dataset.ownerId;
    const title = trigger.dataset.title || document.getElementById('view-swap-title')?.textContent || '';

    if (!encodedId || !ownerId) return;

    viewModal?.classList.add('hidden');
    openSwapListingResponseModal({ encodedId, ownerId, title });
  }, true);
}

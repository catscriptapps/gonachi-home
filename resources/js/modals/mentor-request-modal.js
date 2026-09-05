// /resources/js/modals/mentor-request-modal.js
//
// Opens the "Connect with Mentor" / request modal. Ported from the legacy
// gonachi/ platform's mentors-connect-modal.js — pre-flight self-request
// check client-side, posts to api/mentor-requests, auto-closes on success.

import { Modal } from '../factories/modal-factory.js';
import { mentorRequestForm } from '../forms/mentor-request-form.js';
import { showToast } from '../ui/toast.js';

export function openMentorRequestModal({ encodedId, mentorId, ownerId, ownerName, targetUserType }) {
  const currentUserId = window.sessionUserId ? Number(window.sessionUserId) : null;
  if (currentUserId !== null && Number(ownerId) === currentUserId) {
    showToast('You cannot connect with yourself.', 'error');
    return;
  }

  const modal = new Modal({
    id: 'mentor-request-modal',
    title: 'Connect with Mentor',
    content: mentorRequestForm({ encodedId, mentorId, ownerId, ownerName, targetUserType }),
    size: 'md',
    showFooter: false,
  });

  modal.open();

  const form = document.getElementById('mentor-request-form');
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalLabel = submitBtn.innerHTML;
  const messageSlot = document.getElementById('mentor-request-message-slot');

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
      const response = await fetch(`${baseUrl}api/mentor-requests`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ mentor_id: mentorId, receiver_id: ownerId, message }),
      });
      const result = await response.json();

      if (result.success) {
        messageSlot.innerHTML = `<div class="bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/40 text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-xl font-bold text-sm text-center">Request sent!</div>`;
        setTimeout(() => modal.close(), 1200);
      } else {
        messageSlot.innerHTML = `<p class="text-xs text-red-600">${result.message || 'Could not send request.'}</p>`;
      }
    } catch (err) {
      console.error('Send mentor request error:', err);
      messageSlot.innerHTML = `<p class="text-xs text-red-600">Unexpected error.</p>`;
    } finally {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalLabel;
    }
  });
}

export function initMentorRequestTriggers() {
  if (document._mentorRequestTriggersAttached) return;
  document._mentorRequestTriggersAttached = true;

  // Capture phase: the card's own Connect button sits inside a wrapper with
  // onclick="event.stopPropagation()" (so clicking it doesn't also open the
  // view modal underneath) — that stops the click from ever reaching a
  // bubble-phase document listener, so this one has to run on the way down.
  document.addEventListener('click', (e) => {
    const trigger = e.target.closest('.connect-mentor-trigger');
    if (!trigger) return;

    const encodedId = trigger.dataset.encodedId || document.getElementById('view-mentor-modal')?.dataset.activeEncodedId;
    const mentorId = trigger.dataset.id || document.getElementById('view-mentor-modal')?.dataset.mentorId;
    const ownerId = trigger.dataset.ownerId || document.getElementById('view-mentor-modal')?.dataset.ownerId;
    const ownerName = trigger.dataset.ownerName || document.getElementById('view-mentor-name')?.textContent || '';
    const targetUserType = trigger.dataset.targetUserType || document.getElementById('view-mentor-type-badge')?.textContent || 'Expert';

    if (!encodedId || !mentorId || !ownerId) return;

    document.getElementById('view-mentor-modal')?.classList.add('hidden');
    openMentorRequestModal({ encodedId, mentorId, ownerId, ownerName, targetUserType });
  }, true);
}

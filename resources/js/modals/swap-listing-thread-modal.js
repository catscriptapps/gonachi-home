// /resources/js/modals/swap-listing-thread-modal.js
//
// The caller's read-only conversation with a Swap Marketplace listing's
// owner: every message they sent, with the owner's decision (accepted /
// declined / still waiting) beneath each. Nothing here is editable — the
// only action offered is "Send another message", and only when the last one
// was declined (the same rule the server enforces in send()).

import { Modal } from '../factories/modal-factory.js';
import { showToast } from '../ui/toast.js';
import { openSwapListingResponseModal } from './swap-listing-response-modal.js';

function escapeHtml(str) {
  return String(str ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

function ownerEventHtml(m, ownerName) {
  const owner = escapeHtml(ownerName);

  if (m.status === 'accepted') {
    return `<div class="flex justify-start"><div class="max-w-[85%] rounded-2xl rounded-bl-sm px-3.5 py-2.5 bg-green-50 dark:bg-green-900/20 border border-green-100 dark:border-green-800/30">
      <p class="text-xs font-bold text-green-700 dark:text-green-400">${owner} accepted your message</p>
      <p class="text-[10px] text-green-600/80 dark:text-green-400/70 mt-0.5">${escapeHtml(m.decided_at || '')}</p>
    </div></div>`;
  }

  if (m.status === 'declined') {
    return `<div class="flex justify-start"><div class="max-w-[85%] rounded-2xl rounded-bl-sm px-3.5 py-2.5 bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-800/30">
      <p class="text-xs font-bold text-red-600 dark:text-red-400">${owner} declined your message</p>
      <p class="text-[10px] text-red-500/80 dark:text-red-400/70 mt-0.5">${escapeHtml(m.decided_at || '')}</p>
    </div></div>`;
  }

  return `<div class="flex justify-start"><div class="max-w-[85%] rounded-2xl rounded-bl-sm px-3.5 py-2.5 bg-gray-100 dark:bg-gray-800">
    <p class="text-xs text-gray-500 dark:text-gray-400 italic">Waiting for ${owner} to respond&hellip;</p>
  </div></div>`;
}

function threadHtml(result) {
  const { listing, messages } = result;

  const items = messages.map((m) => `
    <div class="space-y-2">
      <div class="flex justify-end">
        <div class="max-w-[85%] rounded-2xl rounded-br-sm px-3.5 py-2.5 bg-purple-600 text-white">
          <p class="text-sm whitespace-pre-line break-words">${escapeHtml(m.message)}</p>
          <p class="text-[10px] text-purple-100/80 mt-1 text-right">You &middot; ${escapeHtml(m.sent_at || '')}</p>
        </div>
      </div>
      ${ownerEventHtml(m, listing.owner_name)}
    </div>
  `).join('');

  const again = result.can_send_again
    ? `<button type="button" id="swap-thread-send-again" class="w-full py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-xl transition-colors">Send Another Message</button>`
    : '';

  return `
    <div class="space-y-4">
      <div class="rounded-2xl bg-purple-100 dark:bg-purple-950/50 border-2 border-purple-300 dark:border-purple-800/60 px-5 py-4">
        <p class="text-2xl sm:text-3xl font-black leading-tight text-gray-900 dark:text-white">
          Your conversation with <span class="text-purple-600 dark:text-purple-400">${escapeHtml(listing.owner_name)}</span>
        </p>
        <p class="text-base font-bold text-gray-600 dark:text-gray-300 mt-2">
          about <span class="text-gray-900 dark:text-white">&ldquo;${escapeHtml(listing.title)}&rdquo;</span>
        </p>
      </div>
      <div class="space-y-4 max-h-[50vh] overflow-y-auto custom-scrollbar pr-1">
        ${items || '<p class="text-xs text-gray-400 text-center py-6">You haven\'t sent any messages on this listing yet.</p>'}
      </div>
      <p class="text-[10px] text-gray-400 text-center">This is a read-only view of your conversation.</p>
      ${again}
    </div>
  `;
}

export function initSwapListingThreadTriggers() {
  if (document._swapThreadTriggersAttached) return;
  document._swapThreadTriggersAttached = true;

  // Capture phase for the same reason as the Connect trigger: the card's
  // action wrapper stops click propagation.
  document.addEventListener('click', (e) => {
    const trigger = e.target.closest('.view-swap-thread-trigger, #view-swap-primary-btn.is-thread');
    if (!trigger) return;

    const viewModal = document.getElementById('view-swap-modal');
    const encodedId = trigger.dataset.encodedId || viewModal?.dataset.activeEncodedId;
    const ownerId = trigger.dataset.ownerId || viewModal?.dataset.ownerId;
    const title = trigger.dataset.title || document.getElementById('view-swap-title')?.textContent || '';

    if (!encodedId) return;

    viewModal?.classList.add('hidden');
    openSwapListingThreadModal({ encodedId, ownerId, title });
  }, true);
}

export async function openSwapListingThreadModal({ encodedId, ownerId, title }) {
  const modal = new Modal({
    id: 'swap-thread-modal',
    title: 'Your Conversation',
    content: `<div id="swap-thread-body" class="min-h-[120px] flex justify-center items-center py-8"><div class="animate-spin rounded-full h-6 w-6 border-2 border-purple-500 border-t-transparent"></div></div>`,
    size: 'md',
    showFooter: false,
  });
  modal.open();

  const body = document.getElementById('swap-thread-body');
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';

  try {
    const response = await fetch(`${baseUrl}api/swap-listing-responses?id=${encodeURIComponent(encodedId)}&mine=1`);
    const result = await response.json();

    if (!result.success) {
      body.className = '';
      body.innerHTML = `<p class="text-sm text-gray-400 text-center py-8">${escapeHtml(result.message || 'Could not load this conversation.')}</p>`;
      return;
    }

    body.className = '';
    body.innerHTML = threadHtml(result);

    document.getElementById('swap-thread-send-again')?.addEventListener('click', () => {
      modal.close();
      openSwapListingResponseModal({ encodedId, ownerId, title: title || result.listing.title });
    });
  } catch (err) {
    console.error('Load Swap Marketplace conversation error:', err);
    showToast('Could not load this conversation.', 'error');
    modal.close();
  }
}

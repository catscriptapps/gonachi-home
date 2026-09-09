// /resources/js/utils/contractor/job-request-bids.js
//
// The job request owner's inline "View Bids" list — Contractor Discovery
// has no per-card detail modal (unlike Real Estate World's Quotations, which
// hosts its Responses list inside the view-quotation modal), so this is
// rendered directly inside the card instead. Mirrors
// utils/quotations/view-quotation-modal.js's Responses section: lazy-loads
// on first expand, Accept/Decline inline, no notification system needed.

import { showToast } from '../../ui/toast.js';
import { confirmDialog } from '../../ui/confirm.js';

export function initJobRequestBids() {
  if (document._jobRequestBidsAttached) return;
  document._jobRequestBidsAttached = true;

  document.addEventListener('click', (e) => {
    const toggle = e.target.closest('.view-bids-toggle');
    if (toggle) {
      toggleBidsList(toggle);
      return;
    }

    const actionBtn = e.target.closest('[data-bid-action]');
    if (actionBtn) {
      handleBidAction(actionBtn);
    }
  });
}

function toggleBidsList(toggleBtn) {
  const jobId = toggleBtn.dataset.jobId;
  const list = document.querySelector(`.job-request-bids-list[data-job-id="${jobId}"]`);
  if (!list) return;

  const isHidden = list.classList.contains('hidden');
  list.classList.toggle('hidden', !isHidden);

  if (isHidden && !list.dataset.loaded) {
    loadBids(jobId, list, toggleBtn);
  }
}

async function loadBids(jobId, list, toggleBtn) {
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';

  list.innerHTML = `<div class="flex justify-center py-3"><div class="animate-spin rounded-full h-5 w-5 border-2 border-secondary-500 border-t-transparent"></div></div>`;

  try {
    const response = await fetch(`${baseUrl}api/job-request-responses?job_request_id=${encodeURIComponent(jobId)}`);
    const result = await response.json();
    const bids = result.bids || [];

    list.dataset.loaded = 'true';
    list.innerHTML = bids.map((b) => renderBidRow(b, jobId)).join('') || '<p class="text-xs text-gray-400">No quotes yet.</p>';
    updateToggleBadge(toggleBtn, bids.filter((b) => b.status === 'pending').length);
  } catch (err) {
    console.error('Load job request bids error:', err);
    list.innerHTML = '<p class="text-xs text-gray-400">Couldn\'t load quotes.</p>';
  }
}

function renderBidRow(b, jobId) {
  const statusMap = {
    pending: 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400 border-yellow-100 dark:border-yellow-800/30',
    accepted: 'bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 border-green-100 dark:border-green-800/30',
    declined: 'bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 border-red-100 dark:border-red-800/30',
  };
  const badge = `<span class="inline-flex items-center rounded-full px-2 py-0.5 text-[9px] font-black uppercase tracking-wider border ${statusMap[b.status] || statusMap.pending}">${b.status}</span>`;

  const actions = b.status === 'pending'
    ? `<div class="flex items-center gap-2 mt-2">
        <button type="button" data-bid-action="accept" data-bid-id="${b.id}" data-job-id="${jobId}" class="text-[11px] font-bold text-emerald-600 hover:text-emerald-700">Accept</button>
        <button type="button" data-bid-action="decline" data-bid-id="${b.id}" data-job-id="${jobId}" class="text-[11px] font-bold text-red-500 hover:text-red-600">Decline</button>
      </div>`
    : '';

  return `
    <div class="p-3 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30">
      <div class="flex items-center justify-between gap-2 mb-1">
        <span class="text-xs font-bold text-gray-800 dark:text-gray-200">${escapeHtml(b.sender_name)}</span>
        ${badge}
      </div>
      ${b.quote_amount ? `<p class="text-xs font-bold text-secondary-600 dark:text-secondary-400">&#8358;${b.quote_amount}</p>` : ''}
      <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">${escapeHtml(b.message || '')}</p>
      <p class="text-[10px] text-gray-400 mt-1">${b.created_at || ''}</p>
      ${actions}
    </div>`;
}

async function handleBidAction(btn) {
  const action = btn.dataset.bidAction;
  const bidId = btn.dataset.bidId;
  const jobId = btn.dataset.jobId;
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';

  const confirmed = action === 'accept'
    ? await confirmDialog('Accept this quote?', 'Accept', 'Cancel', 'bg-emerald-600 hover:bg-emerald-700')
    : await confirmDialog('Decline this quote?', 'Decline', 'Cancel', 'bg-red-600 hover:bg-red-700');

  if (!confirmed) return;

  try {
    const response = await fetch(`${baseUrl}api/job-request-responses`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action, bid_id: bidId }),
    });
    const result = await response.json();

    if (result.success) {
      showToast(`Quote ${action}ed.`, 'success');
      const list = document.querySelector(`.job-request-bids-list[data-job-id="${jobId}"]`);
      const toggle = document.querySelector(`.view-bids-toggle[data-job-id="${jobId}"]`);
      if (list) {
        list.dataset.loaded = '';
        await loadBids(jobId, list, toggle);
      }
    } else {
      showToast(result.message || 'Could not update quote.', 'error');
    }
  } catch (err) {
    console.error('Job request bid action error:', err);
    showToast('Unexpected error.', 'error');
  }
}

function updateToggleBadge(toggleBtn, pendingCount) {
  if (!toggleBtn) return;

  let badge = toggleBtn.querySelector('span');
  if (pendingCount > 0) {
    if (!badge) {
      badge = document.createElement('span');
      badge.className = 'inline-flex items-center justify-center min-w-[1rem] h-4 px-1 rounded-full bg-secondary-600 text-white text-[10px]';
      toggleBtn.appendChild(badge);
    }
    badge.textContent = pendingCount;
  } else if (badge) {
    badge.remove();
  }
}

function escapeHtml(str) {
  return String(str ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

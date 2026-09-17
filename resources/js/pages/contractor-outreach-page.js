// /resources/js/pages/contractor-outreach-page.js
//
// Admin-only Contractor Outreach queue: per-row "Send SMS"/"Send Email"
// (one contractor, template pre-filled with their own business name) and
// page-level "Send to All (This Page)" bulk actions (many contractors, one
// shared editable template using {business_name}/{claim_url} tokens the
// server substitutes per recipient — see
// Src\Service\ContractorOutreachService::personalize()). Both paths POST to
// the same server/api/contractor-outreach-send.php endpoint.

import { Modal } from '../factories/modal-factory.js';
import { confirmDialog } from '../ui/confirm.js';
import { showToast } from '../ui/toast.js';

let outreachModal = null;

function claimUrlFor(contractorId) {
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';
  return `${window.location.origin}${baseUrl}contractor/${contractorId}`;
}

function defaultSmsTemplate(businessName, claimUrl) {
  return `Hi! We found "${businessName}" listed on Gonachi Contractor Discovery. Claim your free profile to get discovered by more customers and receive job requests directly: ${claimUrl}`;
}

function defaultEmailSubject(businessName) {
  return `Is "${businessName}" your business? Claim your free profile`;
}

function defaultEmailBody(businessName, claimUrl) {
  return `Hello,\n\nThis looks like your business — "${businessName}" — on Gonachi Contractor Discovery.\n\nClaiming your profile is free and only takes a minute. Once verified, you'll be able to:\n  - Complete your profile with photos and certifications\n  - Get found by customers actively searching for your services\n  - Receive job requests directly\n\nClaim your business profile here: ${claimUrl}\n\n— The Gonachi Team`;
}

function getModal() {
  if (outreachModal) return outreachModal;

  outreachModal = new Modal({
    id: 'contractor-outreach-modal',
    title: 'Send Outreach',
    content: '<div id="outreach-modal-body"></div>',
    size: 'md',
    showFooter: false,
  });

  return outreachModal;
}

function openSmsModal({ contractorIds, message, isBulk }) {
  const modal = getModal();

  document.querySelector('#contractor-outreach-modal .modal-body').innerHTML = `
    <form id="outreach-sms-form" class="space-y-3" novalidate>
      ${isBulk ? '<p class="text-xs text-gray-500 dark:text-gray-400">Use <code class="font-mono">{business_name}</code> and <code class="font-mono">{claim_url}</code> — each recipient gets their own values substituted in.</p>' : ''}
      <textarea id="outreach-sms-message" rows="6" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-secondary-500 focus:border-secondary-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100 text-sm">${message}</textarea>
      <button type="submit" class="w-full px-4 py-2 bg-secondary-600 hover:bg-secondary-700 text-white font-bold text-sm rounded-md transition-colors">
        Send SMS to ${contractorIds.length} Contractor${contractorIds.length === 1 ? '' : 's'}
      </button>
    </form>
  `;

  modal.open();

  document.getElementById('outreach-sms-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const finalMessage = document.getElementById('outreach-sms-message').value.trim();
    if (!finalMessage) return;

    if (isBulk) {
      const confirmed = await confirmDialog(
        `Send this SMS to ${contractorIds.length} contractors?`,
        'Send',
        'Cancel',
        'bg-secondary-600 hover:bg-secondary-700'
      );
      if (!confirmed) return;
    }

    modal.close();
    await sendOutreach({ channel: 'sms', contractorIds, message: finalMessage });
  });
}

function openEmailModal({ contractorIds, subject, message, isBulk }) {
  const modal = getModal();

  document.querySelector('#contractor-outreach-modal .modal-body').innerHTML = `
    <form id="outreach-email-form" class="space-y-3" novalidate>
      ${isBulk ? '<p class="text-xs text-gray-500 dark:text-gray-400">Use <code class="font-mono">{business_name}</code> and <code class="font-mono">{claim_url}</code> — each recipient gets their own values substituted in.</p>' : ''}
      <input type="text" id="outreach-email-subject" value="${subject.replace(/"/g, '&quot;')}" placeholder="Subject"
        class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-secondary-500 focus:border-secondary-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100 text-sm" />
      <textarea id="outreach-email-message" rows="8" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-secondary-500 focus:border-secondary-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-100 text-sm">${message}</textarea>
      <button type="submit" class="w-full px-4 py-2 bg-gray-900 hover:bg-gray-800 dark:bg-secondary-600 dark:hover:bg-secondary-500 text-white font-bold text-sm rounded-md transition-colors">
        Send Email to ${contractorIds.length} Contractor${contractorIds.length === 1 ? '' : 's'}
      </button>
    </form>
  `;

  modal.open();

  document.getElementById('outreach-email-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const finalSubject = document.getElementById('outreach-email-subject').value.trim();
    const finalMessage = document.getElementById('outreach-email-message').value.trim();
    if (!finalSubject || !finalMessage) return;

    if (isBulk) {
      const confirmed = await confirmDialog(
        `Send this email to ${contractorIds.length} contractors?`,
        'Send',
        'Cancel',
        'bg-secondary-600 hover:bg-secondary-700'
      );
      if (!confirmed) return;
    }

    modal.close();
    await sendOutreach({ channel: 'email', contractorIds, subject: finalSubject, message: finalMessage });
  });
}

async function sendOutreach({ channel, contractorIds, message, subject }) {
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';

  try {
    const response = await fetch(`${baseUrl}api/contractor-outreach-send`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ channel, contractor_ids: contractorIds, message, subject }),
    });
    const result = await response.json();

    showToast(result.messages?.[0] || (result.success ? 'Sent.' : 'Failed to send.'), result.success ? 'success' : 'error');

    if (result.sent > 0 && window.loadPartial) {
      const currentUrl = window.location.pathname + window.location.search;
      window.loadPartial(currentUrl, false);
    }
  } catch (err) {
    console.error('Contractor outreach send error:', err);
    showToast('Unexpected error. Please try again.', 'error');
  }
}

function rowIdsWithContact(rows, field) {
  return rows
    .filter((row) => row.dataset[field])
    .map((row) => Number(row.dataset.contractorId));
}

export function init() {
  const list = document.getElementById('outreach-list');
  if (!list) return;

  list.addEventListener('click', (e) => {
    const smsBtn = e.target.closest('.send-sms-btn');
    const emailBtn = e.target.closest('.send-email-btn');

    if (smsBtn) {
      const row = smsBtn.closest('.outreach-row');
      const contractorId = Number(row.dataset.contractorId);
      const businessName = row.dataset.businessName;
      const claimUrl = claimUrlFor(contractorId);

      openSmsModal({
        contractorIds: [contractorId],
        message: defaultSmsTemplate(businessName, claimUrl),
        isBulk: false,
      });
    }

    if (emailBtn) {
      const row = emailBtn.closest('.outreach-row');
      const contractorId = Number(row.dataset.contractorId);
      const businessName = row.dataset.businessName;
      const claimUrl = claimUrlFor(contractorId);

      openEmailModal({
        contractorIds: [contractorId],
        subject: defaultEmailSubject(businessName),
        message: defaultEmailBody(businessName, claimUrl),
        isBulk: false,
      });
    }
  });

  const bulkSmsBtn = document.getElementById('bulk-send-sms-btn');
  if (bulkSmsBtn) {
    bulkSmsBtn.addEventListener('click', () => {
      const rows = Array.from(list.querySelectorAll('.outreach-row'));
      const ids = rowIdsWithContact(rows, 'phone');

      if (ids.length === 0) {
        showToast('No contractors on this page have a phone on file.', 'error');
        return;
      }

      openSmsModal({
        contractorIds: ids,
        message: defaultSmsTemplate('{business_name}', '{claim_url}'),
        isBulk: true,
      });
    });
  }

  const bulkEmailBtn = document.getElementById('bulk-send-email-btn');
  if (bulkEmailBtn) {
    bulkEmailBtn.addEventListener('click', () => {
      const rows = Array.from(list.querySelectorAll('.outreach-row'));
      const ids = rowIdsWithContact(rows, 'email');

      if (ids.length === 0) {
        showToast('No contractors on this page have an email on file.', 'error');
        return;
      }

      openEmailModal({
        contractorIds: ids,
        subject: defaultEmailSubject('{business_name}'),
        message: defaultEmailBody('{business_name}', '{claim_url}'),
        isBulk: true,
      });
    });
  }
}

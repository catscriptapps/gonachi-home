// /resources/js/forms/quotation-response-form.js
//
// The "Connect with Owner" / bid form — a single message field, submitted
// as a new QuotationResponse. Ported from the legacy gonachi/ platform's
// quotation-connect-form.js.

export function quotationResponseForm({ encodedId, ownerId, title }) {
  return `
    <form id="quote-response-form" class="space-y-4" novalidate>
      <input type="hidden" name="quotation_id" value="${encodedId}">
      <input type="hidden" name="receiver_id" value="${ownerId}">

      <p class="text-xs text-gray-500 dark:text-gray-400">Send a proposal for "<span class="font-bold text-gray-700 dark:text-gray-300">${escapeHtml(title)}</span>".</p>

      <div>
        <label for="quote-response-message-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Proposal Details</label>
        <textarea id="quote-response-message-input" name="message" required rows="6" placeholder="Introduce yourself and describe your offer..."
          class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500 resize-none"></textarea>
      </div>

      <div id="quote-response-message-slot"></div>

      <button type="submit" class="w-full py-3 bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm rounded-xl transition-colors">
        Send Proposal
      </button>
    </form>
  `;
}

function escapeHtml(str) {
  return String(str ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

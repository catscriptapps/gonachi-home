// /resources/js/forms/job-request-bid-form.js
//
// The "Submit A Quote" bid form — quote amount (optional) + message,
// submitted as a new JobRequestBid. Mirrors the Real Estate World
// Quotations module's quotation-response-form.js.

export function jobRequestBidForm({ jobId, jobTitle }) {
  return `
    <form id="job-request-bid-form" class="space-y-4" novalidate>
      <input type="hidden" name="job_request_id" value="${jobId}">

      <p class="text-xs text-gray-500 dark:text-gray-400">Send a quote for "<span class="font-bold text-gray-700 dark:text-gray-300">${escapeHtml(jobTitle)}</span>".</p>

      <div>
        <label for="job-request-bid-amount-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Your Quote <span class="font-normal text-gray-400">(optional, &#8358;)</span></label>
        <input type="number" id="job-request-bid-amount-input" name="quote_amount" min="0" step="1000" placeholder="e.g. 150000"
          class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-secondary-500 focus:ring-secondary-500">
      </div>

      <div>
        <label for="job-request-bid-message-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Message</label>
        <textarea id="job-request-bid-message-input" name="message" required rows="6" placeholder="Introduce yourself and describe your offer..."
          class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-secondary-500 focus:ring-secondary-500 resize-none"></textarea>
      </div>

      <div id="job-request-bid-message-slot"></div>

      <button type="submit" class="w-full py-3 bg-secondary-600 hover:bg-secondary-700 text-white font-bold text-sm rounded-xl transition-colors">
        Submit Quote
      </button>
    </form>
  `;
}

function escapeHtml(str) {
  return String(str ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

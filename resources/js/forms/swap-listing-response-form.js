// /resources/js/forms/swap-listing-response-form.js
//
// The "Connect with Owner" form for a Swap Marketplace listing — a single
// message field, submitted as a new SwapListingResponse. Mirrors Real Estate
// World's quotation-response-form.js.

export function swapListingResponseForm({ encodedId, ownerId, title }) {
  return `
    <form id="swap-response-form" class="space-y-4" novalidate>
      <input type="hidden" name="listing_id" value="${encodedId}">
      <input type="hidden" name="receiver_id" value="${ownerId}">

      <p class="text-xs text-gray-500 dark:text-gray-400">Send a message about "<span class="font-bold text-gray-700 dark:text-gray-300">${escapeHtml(title)}</span>".</p>

      <div>
        <label for="swap-response-message-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Your Message</label>
        <textarea id="swap-response-message-input" name="message" required rows="6" maxlength="2000" placeholder="Say hello and tell the owner what you have in mind — an offer, a swap idea, a question..."
          class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-purple-500 focus:ring-purple-500 resize-none"></textarea>
      </div>

      <div id="swap-response-message-slot"></div>

      <button type="submit" class="w-full py-3 bg-purple-600 hover:bg-purple-700 text-white font-bold text-sm rounded-xl transition-colors">
        Send Message
      </button>
    </form>
  `;
}

function escapeHtml(str) {
  return String(str ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

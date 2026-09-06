// /resources/js/forms/listing-response-form.js
//
// The "Contact Owner" / inquiry form — a single message field, submitted as
// a new ListingResponse. Ported from the legacy gonachi/ platform's
// listing-connect-form.js.

export function listingResponseForm({ encodedId, ownerId, title }) {
  return `
    <form id="listing-response-form" class="space-y-4" novalidate>
      <input type="hidden" name="listing_id" value="${encodedId}">
      <input type="hidden" name="receiver_id" value="${ownerId}">

      <p class="text-xs text-gray-500 dark:text-gray-400">Send a message about "<span class="font-bold text-gray-700 dark:text-gray-300">${escapeHtml(title)}</span>".</p>

      <div>
        <label for="listing-response-message-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Message</label>
        <textarea id="listing-response-message-input" name="message" required rows="6" placeholder="Introduce yourself and ask your question..."
          class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500 resize-none"></textarea>
      </div>

      <div id="listing-response-message-slot"></div>

      <button type="submit" class="w-full py-3 bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm rounded-xl transition-colors">
        Send Message
      </button>
    </form>
  `;
}

function escapeHtml(str) {
  return String(str ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

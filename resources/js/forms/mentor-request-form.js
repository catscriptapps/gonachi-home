// /resources/js/forms/mentor-request-form.js
//
// The "Connect with Mentor" / request form — a single message field,
// submitted as a new MentorRequest. Ported from the legacy gonachi/
// platform's mentor-connect-form.js.

export function mentorRequestForm({ encodedId, mentorId, ownerId, ownerName, targetUserType }) {
  return `
    <form id="mentor-request-form" class="space-y-4" novalidate>
      <input type="hidden" name="mentor_id" value="${mentorId}">
      <input type="hidden" name="receiver_id" value="${ownerId}">

      <p class="text-xs text-gray-500 dark:text-gray-400">
        Introduce yourself and your goals to <span class="font-bold text-gray-700 dark:text-gray-300">${escapeHtml(ownerName)}</span>
        <span class="ml-1 px-1.5 py-0.5 rounded bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 text-[9px] font-black uppercase tracking-wide">${escapeHtml(targetUserType)}</span>.
        Once they accept your request, a direct line will open up.
      </p>

      <div>
        <label for="mentor-request-message-input" class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Your Initial Pitch</label>
        <textarea id="mentor-request-message-input" name="message" required rows="6" placeholder="Hi, I'm looking for some guidance on..."
          class="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2.5 px-4 text-sm text-gray-900 dark:text-white focus:border-teal-500 focus:ring-teal-500 resize-none"></textarea>
      </div>

      <div id="mentor-request-message-slot"></div>

      <button type="submit" class="w-full py-3 bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm rounded-xl transition-colors">
        Send Connection Request
      </button>
    </form>
  `;
}

function escapeHtml(str) {
  return String(str ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

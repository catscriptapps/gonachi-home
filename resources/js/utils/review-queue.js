// /resources/js/utils/review-queue.js
//
// Shared AJAX approve/reject handler for the admin moderation queues —
// lead-review.php, landlord-report-review.php, contractor-claims-review.php
// — which all share the identical shape: two POST forms per card
// (action=approve|reject, id, page) hitting a per-project review endpoint.
// Previously these were plain native form submissions (full page reload);
// this intercepts the submit, posts via fetch, and refreshes the current
// view through the SPA router instead.
//
// Delegated on document (like utils/contractor-claim.js), so it only needs
// to be wired once globally — it keeps working across SPA partial-load
// navigations without re-binding per page.

import { showToast } from '../ui/toast.js';

export function wireReviewQueue() {
  if (document._reviewQueueAttached) return;
  document._reviewQueueAttached = true;

  document.addEventListener('submit', async (e) => {
    const form = e.target.closest('form[data-review-form]');
    if (!form) return;

    e.preventDefault();

    const submitButtons = form.parentElement.querySelectorAll('button[type="submit"]');
    submitButtons.forEach((btn) => (btn.disabled = true));

    try {
      // form.action (not getAttribute) would return the <input name="action">
      // element itself here, not the URL string — a well-known DOM footgun:
      // a form control literally named "action" shadows HTMLFormElement's
      // own .action property. getAttribute() reads the raw HTML attribute
      // and isn't affected by the naming collision.
      const response = await fetch(form.getAttribute('action'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(Object.fromEntries(new FormData(form).entries())),
      });
      const result = await response.json();

      if (result.success) {
        showToast(result.messages?.[0] || 'Done.', 'success');
        const currentUrl = window.location.pathname + window.location.search;
        if (window.loadPartial) {
          window.loadPartial(currentUrl, false);
        }
      } else {
        showToast(result.messages?.[0] || 'Action failed.', 'error');
        submitButtons.forEach((btn) => (btn.disabled = false));
      }
    } catch (err) {
      console.error('Review queue action failed:', err);
      showToast('Unexpected error. Please try again.', 'error');
      submitButtons.forEach((btn) => (btn.disabled = false));
    }
  });
}

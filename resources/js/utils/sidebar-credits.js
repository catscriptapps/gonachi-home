// /resources/js/utils/sidebar-credits.js
//
// Keeps the sidebar's "Available Credits" badge (layout-sidebar.php's
// #sidebar-credit-balance, Real Estate Leads' own wallet — the only project
// that currently surfaces a live balance in its sidebar) in sync with
// whatever a credit-spending or credit-purchasing action just reported,
// without needing a full page reload (F5). Listens for a single shared
// 'credits-updated' event so any current or future action can trigger a
// refresh the same way — either:
//   - directly, e.g. window.dispatchEvent(new CustomEvent('credits-updated', { detail: { balance } }))
//     right after an AJAX call whose response already carries the new
//     balance (see resources/js/pages/transactions-page.js's Paystack
//     purchase flow), or
//   - indirectly, via resources/js/utils/spa-router.js's loadPartial(),
//     which looks for a hidden [data-credit-balance] element in whatever
//     partial HTML just got swapped into #main-content and dispatches this
//     same event — for actions that spend a credit purely as a side effect
//     of rendering a page server-side (see resources/views/pages/leads/
//     detail.php's CreditService::unlockLead() call), where there's no
//     client-side fetch response to hook into directly.

export function updateSidebarCredits(balance) {
  const el = document.getElementById('sidebar-credit-balance');
  if (!el || balance === undefined || balance === null) return;
  el.textContent = String(balance);
}

export function initSidebarCreditsSync() {
  if (window._sidebarCreditsSyncAttached) return;
  window._sidebarCreditsSyncAttached = true;

  window.addEventListener('credits-updated', (e) => {
    updateSidebarCredits(e.detail?.balance);
  });
}

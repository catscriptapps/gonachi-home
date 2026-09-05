// /resources/js/pages/quotations-page.js
//
// Public "Browse Quotations" feed page logic: search, and the shared
// view-quotation modal. The feed includes the viewer's own quotations
// alongside everyone else's, so the modal's owner-only Edit button (and the
// on-card edit/delete overlay) need to work here too, not just on
// /my-quotations.

import { AnimationEngine } from '../utils/animations.js';
import { initViewQuotationModal } from '../utils/quotations/view-quotation-modal.js';
import { initQuotationsModalTriggers } from '../modals/quotations-modal.js';
import { initQuotationResponseTriggers } from '../modals/quotation-response-modal.js';
import { initQuotationSearch } from '../utils/quotations/quote-search.js';

export function init() {
  AnimationEngine.refresh();

  if (!document.getElementById('quotes-grid')) return;

  initViewQuotationModal();
  initQuotationsModalTriggers();
  initQuotationResponseTriggers();
  initQuotationSearch({ endpointParam: 'all=1' });
}

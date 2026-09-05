// /resources/js/pages/quotations-page.js
//
// Public "Browse Quotations" feed page logic: search, and the shared
// view-quotation modal (create/respond happen from /my-quotations).

import { AnimationEngine } from '../utils/animations.js';
import { initViewQuotationModal } from '../utils/quotations/view-quotation-modal.js';
import { initQuotationResponseTriggers } from '../modals/quotation-response-modal.js';
import { initQuotationSearch } from '../utils/quotations/quote-search.js';

export function init() {
  AnimationEngine.refresh();

  if (!document.getElementById('quotes-grid')) return;

  initViewQuotationModal();
  initQuotationResponseTriggers();
  initQuotationSearch({ endpointParam: 'all=1' });
}

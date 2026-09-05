// /resources/js/pages/my-quotations-page.js
//
// "My Quotations" page logic: create/edit/delete a quotation, respond to
// bids, search, and the shared view-quotation modal.

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
  initQuotationSearch({ endpointParam: '' });
}

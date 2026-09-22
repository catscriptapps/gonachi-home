// /resources/js/pages/swap-page.js

import { initSwapListingsModalTriggers } from '../modals/swap-listings-modal.js';
import { initSwapListingActions } from '../utils/swap-listings/swap-listing-actions.js';
import { initViewSwapListingModal } from '../utils/swap-listings/view-swap-listing-modal.js';

export function init() {
  initSwapListingsModalTriggers();
  initSwapListingActions();
  initViewSwapListingModal();
}

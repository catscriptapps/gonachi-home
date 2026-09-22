// /resources/js/pages/swap-page.js

import { initSwapListingsModalTriggers } from '../modals/swap-listings-modal.js';
import { initSwapListingActions } from '../utils/swap-listings/swap-listing-actions.js';

export function init() {
  initSwapListingsModalTriggers();
  initSwapListingActions();
}

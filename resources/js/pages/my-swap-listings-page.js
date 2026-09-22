// /resources/js/pages/my-swap-listings-page.js

import { initSwapListingsModalTriggers } from '../modals/swap-listings-modal.js';
import { initSwapListingActions } from '../utils/swap-listings/swap-listing-actions.js';

export function init() {
  initSwapListingsModalTriggers();
  initSwapListingActions();
}

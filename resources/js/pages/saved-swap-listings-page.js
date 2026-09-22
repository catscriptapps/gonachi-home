// /resources/js/pages/saved-swap-listings-page.js

import { initSwapListingActions } from '../utils/swap-listings/swap-listing-actions.js';

export function init() {
  // No add/edit modal on this page — just the save/unsave toggle (an
  // unsave here removes the card entirely, see handleSaveToggle()'s
  // saved-swap-listings-page-marker check).
  initSwapListingActions();
}

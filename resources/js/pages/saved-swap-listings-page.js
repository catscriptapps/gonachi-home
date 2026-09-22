// /resources/js/pages/saved-swap-listings-page.js

import { initSwapListingsModalTriggers } from '../modals/swap-listings-modal.js';
import { initSwapListingActions } from '../utils/swap-listings/swap-listing-actions.js';
import { initViewSwapListingModal } from '../utils/swap-listings/view-swap-listing-modal.js';

export function init() {
  // Edit is reachable here too — a viewer can bookmark their own listing
  // (see data-card.php), so an owned card can still show up on this page.
  initSwapListingsModalTriggers();
  // Save/unsave toggle (an unsave here removes the card entirely, see
  // handleSaveToggle()'s saved-swap-listings-page-marker check).
  initSwapListingActions();
  initViewSwapListingModal();
}

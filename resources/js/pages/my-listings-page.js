// /resources/js/pages/my-listings-page.js
//
// "My Listings" page logic: create/edit/delete a listing, respond to
// inquiries, search, infinite scroll, and the shared view-listing modal.
// Also consumes the sessionStorage handoff flag set by the /listings page's
// "Post a Listing" button to auto-open the Add modal right after landing
// here, matching legacy's cross-page handoff exactly.

import { AnimationEngine } from '../utils/animations.js';
import { initViewListingModal } from '../utils/listings/view-listing-modal.js';
import { initListingsModalTriggers, openAddListingModal } from '../modals/listings-modal.js';
import { initListingResponseTriggers } from '../modals/listing-response-modal.js';
import { initListingSearch } from '../utils/listings/listing-search.js';
import { initListingInfiniteScroll } from '../utils/listings/listing-infinite-scroll.js';

export function init() {
  AnimationEngine.refresh();

  if (!document.getElementById('listings-grid')) return;

  initViewListingModal();
  initListingsModalTriggers();
  initListingResponseTriggers();
  initListingSearch({ endpointParam: '' });
  initListingInfiniteScroll({ endpointParam: '' });

  if (sessionStorage.getItem('trigger_add_listing_modal') === 'true') {
    sessionStorage.removeItem('trigger_add_listing_modal');
    setTimeout(() => openAddListingModal(), 100);
  }
}

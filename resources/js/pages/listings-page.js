// /resources/js/pages/listings-page.js
//
// Public "Browse Listings" feed page logic: search, infinite scroll, and the
// shared view-listing modal. The feed includes the viewer's own listings
// alongside everyone else's, so the modal's owner-only Edit button (and the
// on-card edit/delete overlay) need to work here too, not just on
// /my-listings. The "Post a Listing" button hands off to /my-listings via a
// sessionStorage flag (consumed there to auto-open the Add modal), matching
// legacy's cross-page handoff exactly.

import { AnimationEngine } from '../utils/animations.js';
import { initViewListingModal } from '../utils/listings/view-listing-modal.js';
import { initListingsModalTriggers } from '../modals/listings-modal.js';
import { initListingResponseTriggers } from '../modals/listing-response-modal.js';
import { initListingSearch } from '../utils/listings/listing-search.js';
import { initListingInfiniteScroll } from '../utils/listings/listing-infinite-scroll.js';
import { loadPartial } from '../utils/spa-router.js';

export function init() {
  AnimationEngine.refresh();

  if (!document.getElementById('listings-grid')) return;

  initViewListingModal();
  initListingsModalTriggers();
  initListingResponseTriggers();
  initListingSearch({ endpointParam: 'all=1' });
  initListingInfiniteScroll({ endpointParam: 'all=1' });

  const postNewBtn = document.getElementById('post-new-listing-btn');
  if (postNewBtn && !postNewBtn.dataset.handoffAttached) {
    postNewBtn.dataset.handoffAttached = 'true';
    postNewBtn.addEventListener('click', () => {
      sessionStorage.setItem('trigger_add_listing_modal', 'true');
      const baseUrl = window.APP_CONFIG?.baseUrl || '/';
      loadPartial(`${baseUrl}my-listings`);
    });
  }
}

// /resources/js/pages/my-adverts-page.js
//
// "My Adverts" page logic: create/edit/delete an advert, search, and the
// shared view-advert modal.

import { AnimationEngine } from '../utils/animations.js';
import { initViewAdvertModal } from '../utils/adverts/view-advert-modal.js';
import { initAdvertsModalTriggers } from '../modals/adverts-modal.js';
import { initAdvertSearch } from '../utils/adverts/search.js';

export function init() {
  AnimationEngine.refresh();

  if (!document.getElementById('ads-grid')) return;

  initViewAdvertModal();
  initAdvertsModalTriggers();
  initAdvertSearch({ endpointParam: '' });
}

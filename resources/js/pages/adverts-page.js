// /resources/js/pages/adverts-page.js
//
// Public "Browse Adverts" feed page logic: search, and the shared
// view-advert modal. (No composer here — that lives on /my-adverts.)

import { AnimationEngine } from '../utils/animations.js';
import { initViewAdvertModal } from '../utils/adverts/view-advert-modal.js';
import { initAdvertSearch } from '../utils/adverts/search.js';

export function init() {
  AnimationEngine.refresh();

  if (!document.getElementById('ads-grid')) return;

  initViewAdvertModal();
  initAdvertSearch({ endpointParam: 'all=1' });
}

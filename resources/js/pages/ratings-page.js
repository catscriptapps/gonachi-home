// /resources/js/pages/ratings-page.js
//
// Ratings page logic: "Rate a User" modal trigger, the "Look Up Ratings"
// search + "Ratings I've Given" list.

import { AnimationEngine } from '../utils/animations.js';
import { initRateUserTriggers } from '../modals/rate-user-modal.js';
import { initRatingsBrowse } from '../utils/ratings/browse.js';

export function init() {
  AnimationEngine.refresh();

  initRateUserTriggers();

  if (document.getElementById('ratings-lookup-input')) {
    initRatingsBrowse();
  }
}

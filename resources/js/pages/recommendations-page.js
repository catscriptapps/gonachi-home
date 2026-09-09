// /resources/js/pages/recommendations-page.js
//
// Recommendations page logic: "Recommend a User" modal trigger, the "Look
// Up Recommendations" search + "Recommendations I've Given" list.

import { AnimationEngine } from '../utils/animations.js';
import { initRecommendUserTriggers } from '../modals/recommend-user-modal.js';
import { initRecommendationsBrowse } from '../utils/recommendations/browse.js';

export function init() {
  AnimationEngine.refresh();

  initRecommendUserTriggers();

  if (document.getElementById('recommendations-lookup-input')) {
    initRecommendationsBrowse();
  }
}

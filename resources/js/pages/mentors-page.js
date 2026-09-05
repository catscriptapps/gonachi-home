// /resources/js/pages/mentors-page.js
//
// The shared /mentors directory page logic: register/edit/delete a mentor
// profile, connect/request, search + filter, and the shared view-mentor
// modal. Single page — no separate "my mentors" page, matching legacy.

import { AnimationEngine } from '../utils/animations.js';
import { initViewMentorModal } from '../utils/mentors/view-mentor-modal.js';
import { initMentorsModalTriggers } from '../modals/mentors-modal.js';
import { initMentorRequestTriggers } from '../modals/mentor-request-modal.js';
import { initMentorSearch } from '../utils/mentors/mentor-search.js';

export function init() {
  AnimationEngine.refresh();

  if (!document.getElementById('mentors-grid')) return;

  initViewMentorModal();
  initMentorsModalTriggers();
  initMentorRequestTriggers();
  initMentorSearch();
}

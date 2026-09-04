// /resources/js/pages/social-feed-page.js

/**
 * Social Feed page logic (Real Estate World):
 *  - Composer (create post, with optional single photo/video attachment)
 *  - Feed actions (like, delete post, options menu)
 *  - View-post modal (comments, add/delete comment)
 *  - Follow graph (stats, suggestions, search, follow/unfollow)
 *
 * Guests never reach this — the page renders guest-landing.php instead,
 * which has none of the elements these modules look for, so each init()
 * below simply no-ops when its target elements aren't present.
 *
 * Exported `init()` is called by app.js on full load and after partial-load
 * navigation (see spa-router.js).
 */

import { AnimationEngine } from '../utils/animations.js';
import { initComposer } from '../utils/social-feed/compose-modal.js';
import { initFeedActions } from '../utils/social-feed/feed-actions.js';
import { initViewPost } from '../utils/social-feed/view-post.js';
import { initFollowUi } from '../utils/social-feed/follow.js';

export function init() {
  AnimationEngine.refresh();

  if (!document.getElementById('social-feed-container')) return;

  initComposer();
  initFeedActions();
  initViewPost();
  initFollowUi();
}

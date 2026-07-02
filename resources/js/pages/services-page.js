// /resources/js/pages/services-page.js

import { AnimationEngine } from '../utils/animations';
import { initSubscribeService } from '../utils/services/subscribe-service.js';

/**
 * Initialize the Services page events
 */
export function init() {
    AnimationEngine.refresh();
    initSubscribeService();
}
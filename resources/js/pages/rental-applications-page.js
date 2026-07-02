// /resources/js/pages/rental-applications-page.js

import { AnimationEngine } from '../utils/animations';
import { initRegisterNewLandlord } from "../utils/home/register-new-landlord.js";

/**
 * Initialize the About page events
 */
export function init() {
    AnimationEngine.refresh();
    initRegisterNewLandlord();
}
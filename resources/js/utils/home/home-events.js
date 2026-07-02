// /resources/js/utils/home/home-events.js

import { AnimationEngine } from "../animations.js";
import { initRegisterNewLandlord } from "./register-new-landlord.js";
import { initTenantTokenGateway } from "./tenant-token-gateway.js";

export function initHomeEvents() {
    AnimationEngine.refresh();
    initRegisterNewLandlord();
    initTenantTokenGateway();
}
// /resources/js/pages/contractor-discovery-page.js
//
// Contractor Discovery directory page logic: wires "Claim This Profile"
// buttons on the card grid. Filtering/search is a plain GET form (no JS
// needed), same convention as job-requests.php's filter bar.
//
// Exported `init()` is called by app.js on full load and after partial-load
// navigation (see spa-router.js).

import { wireContractorClaimButtons } from '../utils/contractor-claim.js';

export function init() {
  wireContractorClaimButtons();
}

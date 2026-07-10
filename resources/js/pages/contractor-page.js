// /resources/js/pages/contractor-page.js
//
// Contractor profile detail page (/contractor/{id}) logic: wires "Claim
// This Profile". Matched via app.js's segment-reversed page-manifest lookup
// (the last URL segment is the numeric id, so the "contractor" segment is
// what resolves to this module).
//
// Exported `init()` is called by app.js on full load and after partial-load
// navigation (see spa-router.js).

import { wireContractorClaimButtons } from '../utils/contractor-claim.js';

export function init() {
  wireContractorClaimButtons();
}

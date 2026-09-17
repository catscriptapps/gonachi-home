// /resources/js/pages/contractor-claims-review-page.js
//
// Contractor Claim Review Queue: wires the shared global image previewer
// (resources/js/utils/globals/preview.js) onto each pending claim's
// business-document thumbnail (data-img-src, see contractor-claims-review.php)
// so an admin can zoom in to verify it against the contractor's business
// name without leaving the page.

import { registerImagePreview } from '../utils/globals/preview.js';

export function init() {
  registerImagePreview();
}

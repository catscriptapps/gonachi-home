// /resources/js/pages/landlord-report-review-page.js
//
// Landlord Report Review Queue: wires the shared global image previewer
// (resources/js/utils/globals/preview.js) onto each pending report's
// building-picture thumbnails (data-img-src, see landlord-report-review.php)
// so an admin can zoom in without leaving the page. Supporting Evidence
// (PDF-only) renders as plain file links instead — never through this.

import { registerImagePreview } from '../utils/globals/preview.js';

export function init() {
  registerImagePreview();
}

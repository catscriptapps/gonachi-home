// /resources/js/pages/report-landlord-page.js

/**
 * Report A Landlord form validation. Submission stays a plain HTML POST
 * (multipart/form-data) to server/api/report-landlord.php — this only wires
 * up FormValidator for required-field validation (Property Address,
 * Landlord Name, Issue Type) before the native submission proceeds.
 * Exported `init()` is called by app.js on full load and after
 * partial-load navigation (see spa-router.js).
 */

import { FormValidator } from '../utils/form-validator.js';

export function init() {
  const form = document.getElementById('report-landlord-form');
  if (!form || form.dataset.initialized) return;
  form.dataset.initialized = 'true';

  const validator = new FormValidator(form);

  form.addEventListener('submit', (e) => {
    if (!validator.validateForEmptyFields(e)) return;
    // Valid — let the native form submission proceed (multipart POST to /api/report-landlord).
  });
}

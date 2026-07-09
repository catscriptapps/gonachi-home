// /resources/js/pages/signup-page.js

/**
 * Signup page form logic (validation only — submission stays a plain HTML
 * POST to server/api/register.php, matching this app's dependency-free
 * pattern for one-shot forms; AuthController::register() handles the
 * server-side result and redirects). Exported `init()` is called by app.js
 * on full load and after partial-load navigation (see spa-router.js).
 */

import { FormValidator } from '../utils/form-validator.js';

export function init() {
  const form = document.getElementById('signup-form');
  if (!form || form.dataset.initialized) return;
  form.dataset.initialized = 'true';

  const validator = new FormValidator(form);
  const password = form.querySelector('#signup-password');
  const passwordConfirmation = form.querySelector('#signup-password-confirmation');

  form.addEventListener('submit', (e) => {
    // 1. Required-field validation (styled inline errors, matches app convention)
    if (!validator.validateForEmptyFields(e)) return;

    // 2. Password length
    if (password.value.length < 8) {
      e.preventDefault();
      validator.showError(password, 'Password must be at least 8 characters.');
      return;
    }

    // 3. Password match
    if (password.value !== passwordConfirmation.value) {
      e.preventDefault();
      validator.showError(passwordConfirmation, 'Passwords do not match.');
      return;
    }

    // Valid — let the native form submission proceed (plain POST to /api/register).
  });
}

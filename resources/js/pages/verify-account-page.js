// /resources/js/pages/verify-account-page.js
//
// Consumes the activation link's ?token=&email= query string, verifies it
// server-side (which also logs the account straight in — see
// Src\Controller\VerificationController::verify()), then hard-navigates to
// wherever the user was before they had to sign up (or /dashboard as a
// fallback) — this is a real page load from an email client, not an SPA
// partial, so this always uses window.location, never loadPartial().
//
// Mirrors resources/js/utils/login/update-password.js's shape (the closest
// existing "consume a mailed token" precedent) but this page has no form —
// verification just needs the query string, no user input.

import { resendActivationLink } from '../utils/login/resend-activation.js';

export function init() {
  const root = document.getElementById('verification-page');
  if (!root || root.dataset.initialized) return;
  root.dataset.initialized = 'true';

  const baseUrl = window.APP_CONFIG?.baseUrl || '/';
  const params = new URLSearchParams(window.location.search);
  const token = params.get('token') || '';
  const email = params.get('email') || '';

  const loader = document.getElementById('verifying-loader');
  const successBlock = document.getElementById('verification-success');
  const errorBlock = document.getElementById('verification-error');
  const errorMessage = document.getElementById('error-message');
  const continueBtn = document.getElementById('verification-continue-btn');
  const resendBtn = document.getElementById('verification-resend-btn');
  const resendMessage = document.getElementById('verification-resend-message');

  resendBtn?.addEventListener('click', async () => {
    resendBtn.disabled = true;
    resendBtn.textContent = 'Sending...';
    // Renders the real success/failure text from the API (e.g. "already
    // verified — you can sign in") into resendMessage — passing null here
    // would silently drop that and always leave the button's own generic
    // label as the only feedback, indistinguishable from a real failure.
    await resendActivationLink(email, resendMessage);
    resendBtn.disabled = false;
    resendBtn.textContent = 'Resend Activation Link';
  });

  if (!token || !email) {
    showError('This verification link is missing information. Please use the link from your email, or request a new one.');
    return;
  }

  verify();

  async function verify() {
    try {
      const response = await fetch(`${baseUrl}api/verify-account`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ token, email }),
      });
      const result = await response.json();

      if (result.success) {
        showSuccess(result.redirect_url);
      } else {
        showError((result.messages || ['This verification link is invalid or has expired.'])[0]);
      }
    } catch (err) {
      console.error('Account verification error:', err);
      showError('Unexpected error while verifying your account. Please try again.');
    }
  }

  function showSuccess(redirectUrl) {
    loader.classList.add('hidden');
    successBlock.classList.remove('hidden');

    // A bare '/dashboard' is AuthService::loginAsUser()'s generic fallback
    // (no APP_BASE_PATH awareness) — anything else is a real captured
    // window.location.href from signup time (see
    // resources/js/utils/users/form-submit.js's getPayload()), already a
    // full, correctly-based URL safe to navigate to directly.
    const target = redirectUrl && redirectUrl.startsWith('http') ? redirectUrl : `${baseUrl}dashboard`;

    if (continueBtn) {
      continueBtn.removeAttribute('data-partial');
      continueBtn.href = target;
    }

    // Auto-continue shortly after, same idiom as update-password.js's
    // post-reset redirect — the Continue button above still works
    // immediately for anyone who doesn't want to wait.
    setTimeout(() => {
      window.location.href = target;
    }, 2500);
  }

  function showError(message) {
    loader.classList.add('hidden');
    errorBlock.classList.remove('hidden');
    if (errorMessage) errorMessage.textContent = message;
  }
}

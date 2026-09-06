// /resources/js/modals/auth-gate-modal.js
//
// Guest content gates (e.g. Real Estate Leads "View Full Details", claiming
// a contractor profile) used to jump a guest straight into the Sign In
// modal, with no visible path to Create Account at all. This is the fix:
// a small choice modal offering both — "Already have an account? Sign In"
// and "Don't have an account yet? Create Account" — each button reusing
// the app's existing global triggers (a[data-login-button] for
// LoginModal, .register-btn for the Create Account modal), so this module
// owns no auth logic itself, just the presentation choice.

import { Modal } from '../factories/modal-factory.js';

let gateModal = null;

function authGateContent() {
  return `
    <div class="space-y-4 text-center">
      <div class="p-5 rounded-2xl border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/40">
        <h4 class="text-sm font-bold text-gray-900 dark:text-white">Already have an account?</h4>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mb-4">Sign in to continue.</p>
        <a href="javascript:void(0)" data-login-button class="inline-flex items-center justify-center w-full px-5 py-2.5 bg-gray-900 hover:bg-gray-800 dark:bg-primary-600 dark:hover:bg-primary-500 text-white font-bold text-sm rounded-lg transition-colors shadow-sm">
          Sign In
        </a>
      </div>

      <div class="flex items-center gap-3">
        <span class="flex-1 h-px bg-gray-200 dark:bg-gray-800"></span>
        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Or</span>
        <span class="flex-1 h-px bg-gray-200 dark:bg-gray-800"></span>
      </div>

      <div class="p-5 rounded-2xl border border-gray-200 dark:border-gray-800">
        <h4 class="text-sm font-bold text-gray-900 dark:text-white">Don't have an account yet?</h4>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 mb-4">Create one — it only takes a minute.</p>
        <button type="button" class="register-btn inline-flex items-center justify-center w-full px-5 py-2.5 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-bold text-sm rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/60 transition-colors">
          Create Account
        </button>
      </div>
    </div>
  `;
}

export function openAuthGateModal() {
  if (gateModal) gateModal.destroy();

  gateModal = new Modal({
    id: 'auth-gate-modal',
    title: 'Sign In Required',
    content: authGateContent(),
    size: 'sm',
    showFooter: false,
  });

  gateModal.open();

  // Bubble-phase listener on the modal itself fires before the click
  // reaches document.body/document, where LoginModal and
  // register-new-user.js's own delegated listeners live — so this closes
  // the gate first, then lets the click carry on to actually open the
  // real modal underneath.
  document.getElementById('auth-gate-modal')?.addEventListener('click', (e) => {
    if (e.target.closest('a[data-login-button], .register-btn')) {
      gateModal?.close();
    }
  });
}

export function initAuthGateTriggers() {
  if (document._authGateTriggersAttached) return;
  document._authGateTriggersAttached = true;

  document.addEventListener('click', (e) => {
    const trigger = e.target.closest('.auth-gate-btn');
    if (!trigger) return;

    e.preventDefault();
    e.stopImmediatePropagation();

    openAuthGateModal();
  });
}

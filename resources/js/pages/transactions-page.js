// /resources/js/pages/transactions-page.js
//
// Billing & Credits page: wires each pack's "Buy Credits" button to
// Paystack's inline popup checkout, then verifies + credits server-side the
// moment it reports success (server/api/credit-purchase-initialize.php /
// credit-purchase-verify.php -> Src\Service\CreditService / PaystackService).
// No webhook involved — verification happens synchronously right after the
// popup closes, keyed off the reference the server minted at initialize time.

import { showToast } from '../ui/toast.js';

const PAYSTACK_SCRIPT_URL = 'https://js.paystack.co/v1/inline.js';
let paystackScriptPromise = null;

export function init() {
  const container = document.getElementById('credit-packs');
  if (!container || container.dataset.purchasingEnabled !== '1') return;

  container.querySelectorAll('.buy-credits-btn').forEach((btn) => {
    btn.addEventListener('click', () => handleBuyClick(btn, container));
  });
}

async function handleBuyClick(btn, container) {
  const packId = parseInt(btn.dataset.packId, 10);
  const email = container.dataset.userEmail;

  if (!email) {
    showToast('Your account has no email on file — cannot start checkout.', 'error');
    return;
  }

  setBusy(btn, true);

  try {
    const initRes = await fetch(`${window.APP_CONFIG.baseUrl}api/credit-purchase-initialize`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      body: JSON.stringify({ pack_id: packId }),
    });
    const initData = await initRes.json();

    if (!initData.success) {
      showToast(initData.message || 'Could not start checkout.', 'error');
      setBusy(btn, false);
      return;
    }

    await loadPaystackScript();

    const handler = window.PaystackPop.setup({
      key: initData.public_key,
      email,
      amount: initData.amount_kobo,
      currency: 'NGN',
      ref: initData.reference,
      callback: (response) => verifyPurchase(response.reference, btn),
      onClose: () => setBusy(btn, false),
    });

    handler.openIframe();
  } catch (err) {
    showToast('Could not start checkout.', 'error');
    setBusy(btn, false);
  }
}

async function verifyPurchase(reference, btn) {
  try {
    const res = await fetch(`${window.APP_CONFIG.baseUrl}api/credit-purchase-verify`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      body: JSON.stringify({ reference }),
    });
    const data = await res.json();

    if (data.success) {
      showToast(`Success! New balance: ${data.balance} credits.`, 'success');
      // Full partial reload so the balance badge + transaction history both
      // reflect the purchase, same as any other server-truth refresh here.
      if (window.loadPartial) {
        window.loadPartial(window.location.href, false);
      }
    } else {
      showToast(data.message || 'Payment could not be verified.', 'error');
    }
  } catch (err) {
    showToast('Payment could not be verified.', 'error');
  } finally {
    setBusy(btn, false);
  }
}

function setBusy(btn, busy) {
  btn.disabled = busy;
  btn.textContent = busy ? 'Please wait…' : 'Buy Credits';
}

function loadPaystackScript() {
  if (window.PaystackPop) return Promise.resolve();
  if (paystackScriptPromise) return paystackScriptPromise;

  paystackScriptPromise = new Promise((resolve, reject) => {
    const script = document.createElement('script');
    script.src = PAYSTACK_SCRIPT_URL;
    script.onload = () => resolve();
    script.onerror = () => {
      paystackScriptPromise = null;
      reject(new Error('Failed to load Paystack script'));
    };
    document.head.appendChild(script);
  });

  return paystackScriptPromise;
}

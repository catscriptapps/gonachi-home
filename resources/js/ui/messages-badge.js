// /resources/js/ui/messages-badge.js
//
// Live unread-count badge for the admin "Messages" header icon — mirrors
// live-chat-badge.js exactly (same poll cadence, same visibility-aware
// scheduling, same stop-on-403 behavior for a non-admin session).

const POLL_MS = 3000;
let timer = null;
let stopped = false;

function updateBadge(count) {
  const badge = document.getElementById('messages-nav-badge');
  if (!badge) return;

  if (count > 0) {
    badge.textContent = count > 99 ? '99+' : String(count);
    badge.classList.remove('hidden');
  } else {
    badge.classList.add('hidden');
  }
}

async function poll() {
  try {
    const res = await fetch(`${window.APP_CONFIG?.baseUrl || '/'}api/messages-unread-count`, { cache: 'no-store' });
    if (res.status === 403) {
      stopped = true;
      return;
    }
    const data = await res.json();
    if (data.success) updateBadge(data.count);
  } catch (err) {
    console.error('Messages badge poll failed:', err);
  }
}

function schedule() {
  clearTimeout(timer);
  if (stopped) return;
  timer = setTimeout(async () => {
    if (document.visibilityState === 'visible') await poll();
    schedule();
  }, POLL_MS);
}

export function initMessagesBadge() {
  poll();
  schedule();
}

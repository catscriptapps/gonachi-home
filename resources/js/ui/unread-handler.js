// /resources/js/ui/unread-handler.js

/**
 * One request to rule them all. 
 * Updates every badge in the header in a single heartbeat.
 */
export function initUnreadPolling() {
  const badges = {
    messages: document.getElementById('messages-badge'),
    chats: document.getElementById('chats-badge'),
    notifications: document.getElementById('notifications-badge')
  };

  async function updateAllBadges() {
    try {
      const res = await fetch(`${window.APP_CONFIG.baseUrl}api/global-unread`, { cache: 'no-store' });
      const data = await res.json();

      if (data.success) {
        Object.keys(badges).forEach(key => {
          const el = badges[key];
          const count = data.counts[key];

          if (el && count > 0) {
            // Only update text for numeric badges (not the red dot notification)
            if (key !== 'notifications') {
                el.textContent = count > 99 ? '99+' : count;
            }
            
            el.classList.remove('hidden');
          } else if (el) {
            el.classList.add('hidden');
            // Clear text if it's a numeric badge being hidden
            if (key !== 'notifications') el.textContent = '';
          }
        });
      }
    } catch (err) {
      console.error('Heartbeat failed:', err);
    }
  }

  updateAllBadges();
  setInterval(updateAllBadges, 30000);
}
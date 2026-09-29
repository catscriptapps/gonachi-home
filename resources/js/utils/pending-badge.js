// /resources/js/utils/pending-badge.js
//
// Keeps an owner's card badge (components/ui/pending-badge.php) in step with
// the live count of pending messages, after the owner's detail modal loads
// or acts on them. The badge is always in the card's markup (hidden at
// zero), so this works in both directions — it can also reveal it.

export function setPendingBadge(cardEl, count) {
  const badge = cardEl?.querySelector('[data-pending-badge]');
  if (!badge) return;

  const noun = badge.dataset.noun || 'Message';

  badge.querySelector('[data-pending-count]').textContent = count;
  badge.querySelector('[data-pending-label]').textContent = `Pending ${noun}${count === 1 ? '' : 's'}`;
  badge.classList.toggle('hidden', count <= 0);
  badge.classList.toggle('inline-flex', count > 0);
}

// /resources/js/utils/swap-listings/swap-listing-actions.js
//
// Delete, status toggle (Mark As Completed / Reactivate), and save/unsave
// for Swap listing cards — capture-phase document click delegation, same
// pattern as Real Estate World's view-listing-modal.js delete/toggle
// handlers (minus the "view modal" step Swap doesn't have).

import { confirmDialog } from '../../ui/confirm.js';
import { showToast } from '../../ui/toast.js';

export function initSwapListingActions() {
  if (document._swapListingActionsAttached) return;
  document._swapListingActionsAttached = true;

  document.addEventListener('click', async (e) => {
    const deleteBtn = e.target.closest('.delete-swap-listing-btn');
    if (deleteBtn) {
      await handleDelete(deleteBtn);
      return;
    }

    const toggleBtn = e.target.closest('.complete-swap-listing-trigger, .reactivate-swap-listing-trigger');
    if (toggleBtn) {
      await handleStatusToggle(toggleBtn);
      return;
    }

    const saveBtn = e.target.closest('.save-swap-listing-btn');
    if (saveBtn) {
      await handleSaveToggle(saveBtn);
    }
  }, true);
}

async function handleDelete(btn) {
  const confirmed = await confirmDialog(
    'Delete this listing? This also removes its photos. This cannot be undone.',
    'Delete',
    'Cancel',
    'bg-red-600 hover:bg-red-700'
  );
  if (!confirmed) return;

  const baseUrl = window.APP_CONFIG?.baseUrl || '/';
  const encodedId = btn.dataset.encodedId;

  try {
    const response = await fetch(`${baseUrl}api/swap-listings`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ _method: 'DELETE', encoded_id: encodedId }),
    });
    const result = await response.json();

    if (result.success) {
      document.querySelectorAll(`[data-listing-wrapper][data-encoded-id="${encodedId}"]`).forEach((el) => el.remove());
      showToast('Listing deleted.', 'success');
    } else {
      showToast(result.message || 'Could not delete listing.', 'error');
    }
  } catch (err) {
    console.error('Swap listing delete error:', err);
    showToast('Unexpected error. Please try again.', 'error');
  }
}

async function handleStatusToggle(btn) {
  const isReactivate = btn.classList.contains('reactivate-swap-listing-trigger');
  const confirmed = await confirmDialog(
    isReactivate ? 'Reactivate this listing?' : 'Mark this listing as completed?',
    'Confirm',
    'Cancel',
    'bg-purple-600 hover:bg-purple-700'
  );
  if (!confirmed) return;

  const baseUrl = window.APP_CONFIG?.baseUrl || '/';
  const encodedId = btn.dataset.encodedId;

  try {
    const response = await fetch(`${baseUrl}api/swap-listings`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ encoded_id: encodedId, intent: isReactivate ? 'posted' : 'completed' }),
    });
    const result = await response.json();

    if (result.success) {
      document.querySelectorAll(`[data-listing-wrapper][data-encoded-id="${encodedId}"]`).forEach((el) => {
        el.outerHTML = result.cardHtml;
      });
      showToast(isReactivate ? 'Listing reactivated.' : 'Listing marked as completed.', 'success');
    } else {
      showToast(result.message || 'Could not update listing.', 'error');
    }
  } catch (err) {
    console.error('Swap listing status toggle error:', err);
    showToast('Unexpected error. Please try again.', 'error');
  }
}

async function handleSaveToggle(btn) {
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';
  const encodedId = btn.dataset.encodedId;

  btn.disabled = true;

  try {
    const response = await fetch(`${baseUrl}api/swap-listing-save`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ encoded_id: encodedId }),
    });
    const result = await response.json();

    if (result.success) {
      const svg = btn.querySelector('svg');
      // Keeps data-is-saved in sync on the card wrapper itself — without
      // this, reopening the detail modal (no full page reload in between)
      // would re-populate its primary button from the now-stale attribute
      // and show the wrong Save/Unsave label.
      const card = btn.closest('[data-listing-wrapper]');
      if (card) card.dataset.isSaved = result.saved ? '1' : '0';

      if (result.saved) {
        btn.classList.remove('text-gray-400', 'hover:text-amber-500');
        btn.classList.add('text-amber-500');
        svg.setAttribute('fill', 'currentColor');
        btn.title = 'Remove from Saved';
        showToast('Saved.', 'success');
      } else {
        btn.classList.remove('text-amber-500');
        btn.classList.add('text-gray-400', 'hover:text-amber-500');
        svg.setAttribute('fill', 'none');
        btn.title = 'Save this listing';
        showToast('Removed from saved.', 'success');

        // On the Saved page itself, an unsave should drop the card entirely.
        if (document.getElementById('saved-swap-listings-page-marker')) {
          document.querySelectorAll(`[data-listing-wrapper][data-encoded-id="${encodedId}"]`).forEach((el) => el.remove());
        }
      }
    } else {
      showToast(result.message || 'Could not update saved listings.', 'error');
    }
  } catch (err) {
    console.error('Swap listing save-toggle error:', err);
    showToast('Unexpected error. Please try again.', 'error');
  } finally {
    btn.disabled = false;
  }
}

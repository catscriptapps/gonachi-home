// /resources/js/utils/compose-photo-strip.js
//
// Shared "photos" state + upload/remove/reorder wiring for compose (add/
// edit) forms that let the whole picture set be built up while the rest of
// the form is filled out — the pattern Swap Marketplace's compose modal
// established (resources/js/modals/swap-listings-modal.js), now reused by
// Real Estate World's advert/listing/quotation compose modals. The photos
// are uploaded immediately (to a dedicated pre-upload endpoint, no parent
// record required yet) and only *attached* to the advert/listing/quotation
// on submit, via the encoded_id-less `photo_urls` the save() endpoints now
// accept and reconcile (see e.g. AdvertsPicturesController::replacePhotos()).
//
// Orphan guarantee: every photo uploaded through this strip during THIS
// compose session (i.e. NOT one already-attached and loaded in via
// setPhotos()'s edit prefill) is tracked in `freshUrls`. The instant one is
// removed from the strip, or the whole compose modal is dismissed without
// ever submitting, this fires a discard request for it
// (api/discard-photo-upload) so it never sits on disk unattached — see
// Src\Service\PendingUploadTracker on the server side for the full
// lifecycle, including the periodic sweep that catches anything a crashed
// tab/dropped connection stopped this from reaching.
//
// createPhotoStrip() returns one independent instance — each *-modal.js
// creates its own (module-level, shared between that type's add/edit
// modals, which are never open at the same time) rather than a shared
// singleton, so two different compose modals can't stomp on each other's
// state.

import { uploadModal, createUploadHandler } from '../modals/upload-modal.js';
import { showToast } from '../ui/toast.js';
import { reorderButtonHtml, wirePicReorder } from './pic-reorder.js';

const MAX_PHOTOS = 12; // matches server/helpers.php's getMediaLimit()

function discardUpload(url) {
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';

  // Fire-and-forget: neither removing a tile nor closing a modal should
  // wait on this network round-trip, and there's nothing meaningful to do
  // client-side if it fails — the server-side sweep is the backstop.
  fetch(`${baseUrl}api/discard-photo-upload`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ url }),
  }).catch((err) => console.error('Discard photo upload error:', err));
}

export function createPhotoStrip({ uploadPath, altText = 'Photo' }) {
  let photos = []; // { url }
  let freshUrls = new Set(); // urls uploaded THIS session, not yet attached

  function setPhotos(list) {
    photos = (list || []).map((p) => ({ url: p.url }));
    freshUrls = new Set();
  }

  function getUrls() {
    return photos.map((p) => p.url);
  }

  // Called right after a successful save — those freshly-uploaded photos
  // are now genuinely attached, so they must stop being treated as
  // "abandon on dismiss" candidates (the server independently stops
  // tracking them as pending too, via replacePhotos()'s own untrackAttached()
  // call, so this is belt-and-braces, not the only safeguard).
  function markSaved() {
    freshUrls = new Set();
  }

  // Called when the compose modal is dismissed (X, overlay, Escape, or a
  // cancelled close) without ever submitting — anything still uploaded-this-
  // session and not yet saved has to go now, or it's an orphan.
  function discardAbandoned() {
    freshUrls.forEach((url) => discardUpload(url));
    freshUrls = new Set();
  }

  function render(previewEl) {
    if (!previewEl) return;

    previewEl.innerHTML = photos.map((file, i) => `
      <div data-pic-tile data-pic-id="${i}" class="relative rounded-lg overflow-hidden border border-gray-200 dark:border-gray-800 h-20">
        <img src="${file.url}" class="w-full h-full object-cover" alt="${altText}" />
        ${photos.length > 1 ? reorderButtonHtml() : ''}
        <button type="button" data-remove-photo="${i}" title="Remove" class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs shadow">&times;</button>
      </div>
    `).join('');
  }

  function wire(idPrefix) {
    const addBtn = document.getElementById(`${idPrefix}-add-photos-btn`);
    const preview = document.getElementById(`${idPrefix}-photos-preview`);
    if (!addBtn || !preview || addBtn.dataset.photoStripWired) return;
    addBtn.dataset.photoStripWired = 'true';

    render(preview);

    preview.addEventListener('click', (e) => {
      const btn = e.target.closest('[data-remove-photo]');
      if (!btn) return;

      const [removed] = photos.splice(Number(btn.dataset.removePhoto), 1);
      render(preview);

      if (removed && freshUrls.has(removed.url)) {
        freshUrls.delete(removed.url);
        discardUpload(removed.url);
      }
    });

    wirePicReorder(preview, (ids) => {
      photos = ids.map((i) => photos[Number(i)]);
      render(preview);
    });

    addBtn.addEventListener('click', () => {
      if (photos.length >= MAX_PHOTOS) {
        showToast(`You can attach up to ${MAX_PHOTOS} photos.`, 'error');
        return;
      }

      const baseUrl = window.APP_CONFIG?.baseUrl || '/';

      uploadModal.open();
      setTimeout(() => {
        createUploadHandler(
          `${baseUrl}${uploadPath}`,
          `${idPrefix}-photos`,
          (files) => {
            files.forEach((f) => freshUrls.add(f.url));
            photos.push(...files.map((f) => ({ url: f.url })));
            render(preview);
          },
          6,
          true,
          { maxFiles: MAX_PHOTOS - photos.length }
        );
      }, 50);
    });
  }

  return { setPhotos, getUrls, markSaved, discardAbandoned, wire };
}

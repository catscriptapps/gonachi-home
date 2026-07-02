// /resources/js/utils/properties/view-media.js

import { updateMediaCountLabel, photosEmptyStatePlaceholder } from "../helpers.js";
import { registerImagePreview } from "../globals/preview.js";
import { deleteMedia } from "../media-manager.js";

/**
 * Fetches and renders gallery images for a specific property
 * @param {number|string} propertyId
 * @param {boolean|null} canManage If null, pulls from persisted DOM state.
 */
export async function viewMedia(propertyId, canManage = null) {
    const grid = document.getElementById('property-pics-wrapper');
    if (!grid) return;

    if (canManage !== null) {
        grid.dataset.canManage = String(canManage);
    } else {
        canManage = grid.dataset.canManage === 'true';
    }

    if (!grid._deleteAttached) {
        grid.addEventListener('click', async (e) => {
            const btn = e.target.closest('[data-action="delete-picture"]');
            if (btn) {
                const picId = btn.getAttribute('data-id');
                await deleteMedia({
                    type: 'property',
                    picId: picId,
                    parentId: propertyId
                });
            }
        });
        grid._deleteAttached = true;
    }

    try {
        const response = await fetch(`${window.APP_CONFIG.baseUrl}api/property-pictures?id=${propertyId}`);
        const result = await response.json();

        if (result.success) {
            updateMediaCountLabel('property-pics-count', result.pictures.length);

            grid.querySelectorAll('.relative.group, .empty-state-placeholder').forEach(el => el.remove());

            if (result.pictures.length === 0) {
                photosEmptyStatePlaceholder(grid);
                return;
            }

            result.pictures.forEach(pic => {
                const imgSrc = `${window.APP_CONFIG.baseUrl}images/uploads/properties/${pic.pic_name}`;

                const deleteBtnHtml = canManage ? `
                    <button type="button" data-action="delete-picture" data-id="${pic.entry_id}" class="p-2.5 bg-red-500/80 hover:bg-red-500 rounded-full text-white transition-all transform hover:scale-110" title="Delete Photo">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>` : '';

                const html = `
                    <div class="relative group aspect-square rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50 shadow-sm">
                        <img src="${imgSrc}"
                            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                            alt="Property Photo">

                        <div class="absolute inset-0 bg-secondary-900/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center gap-3">
                            <button type="button" data-action="view-picture" data-img-src="${imgSrc}" class="p-2.5 bg-white/20 hover:bg-white/40 rounded-full backdrop-blur-md text-white transition-all transform hover:scale-110">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                            ${deleteBtnHtml}
                        </div>
                    </div>
                `;
                grid.insertAdjacentHTML('beforeend', html);
            });

            registerImagePreview();
        }
    } catch (error) {
        console.error('Failed to refresh property pictures:', error);
    }
}

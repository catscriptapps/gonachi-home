// /resources/js/utils/properties/view-property.js

import { ViewContentMapper } from './view-content-mapper.js';
import { viewMedia } from './view-media.js';
import { modalDetailOwner } from '../../ui/modal-detail-owner.js';

export function initViewProperty() {
    if (window._viewPropertyListenerAttached) return;

    ViewContentMapper.initMediaListeners();

    // Open modal on card click — guard against edit/delete buttons inside the trigger
    document.addEventListener('click', (e) => {
        if (e.target.closest('.edit-property-btn, .delete-property-btn')) return;

        const trigger = e.target.closest('.view-property-trigger');
        if (!trigger) return;

        e.preventDefault();
        e.stopPropagation();
        openPropertyModal(trigger.dataset);
    });

    ViewContentMapper.initUIBehaviors();
    window._viewPropertyListenerAttached = true;
}

function openPropertyModal(data) {
    const modal = document.getElementById('view-property-modal');
    if (!modal) return;

    // 1-6. Map header, location, service links, metadata, and the edit button
    ViewContentMapper.mapAll(data);

    // 7. Owner section via shared component
    modalDetailOwner('property', {
        ownerName: data.landlordName || 'Unknown Landlord',
        ownerAvatar: data.ownerAvatar || '',
        ownerRegion: data.regionName || 'N/A',
        ownerCountry: data.countryName || 'N/A',
        ownerInitial: (data.landlordName || 'L').charAt(0).toUpperCase(),
        userTypes: data.userTypesJson || '["Landlord"]',
    });

    // 8. Store ID on modal for sub-systems (pics, access tokens)
    const finalId = data.encodedId;
    modal.dataset.propertyId = finalId;
    modal.dataset.propertyName = data.propertyName || '';
    modal.dataset.activeTokensByService = data.activeTokensByService || '{}';

    // 9. Reveal
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    if (finalId) {
        setTimeout(() => { viewMedia(finalId, true); }, 50);
    }
}

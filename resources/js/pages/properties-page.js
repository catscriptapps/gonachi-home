// /resources/js/pages/properties-page.js

import { initPropertiesModal } from '../modals/properties-modal.js';
import { updateCount } from '../components/table-pagination-count.js';
import { initDeleteProperty } from '../utils/properties/delete-property.js';
import { enableTableSearch } from '../components/table-search.js';
import { initViewProperty } from '../utils/properties/view-property.js';
//import { initPropertyInfiniteScroll } from '../utils/properties/infinite-scroll-properties.js';

/**
 * Initialize the Properties page JS.
 */
export function init() {
    // 1. Initialize the Create/Edit modal logic (scoped contextually to the active landlord session)
    initPropertiesModal();

    // 2. Enable the pro AJAX search
    enableTableSearch({
        searchInputId: 'properties-search',
        tbodyId: 'properties-tbody',
        countId: 'properties-count',
        endpoint: `${window.APP_CONFIG?.baseUrl}api/properties`,
        resourceLabel: 'properties',
        addButtonId: 'add-property-btn'
    });

    // 3. Initialize the delete asset functionality
    initDeleteProperty();

    // 4. Initial count check
    updateCount('property', '#properties-tbody', '#properties-count');

    // 5. Initialize the detailed view/profile modal for assets
    initViewProperty();

    // 6. Initialize Infinite Scroll for performance scaling across large portfolios
    //initPropertyInfiniteScroll();
}
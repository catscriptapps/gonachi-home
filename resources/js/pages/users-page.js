// /resources/js/pages/users-page.js

import { initUsersModal } from '../modals/users-modal.js';
import { updateCount } from '../components/table-pagination-count.js';
import { initDeleteUser } from '../utils/users/delete-user.js';
import { enableTableSearch } from '../components/table-search.js';
import { initViewUser } from '../utils/users/view-user.js';
import { initUserInfiniteScroll } from '../utils/users/infinite-scroll-users.js';

/**
 * Initialize the Users page JS.
 */
export function init() {
    // 1. Initialize the Create/Edit modal logic
    initUsersModal();
    
    // 2. Enable the pro AJAX search
    enableTableSearch({
        searchInputId: 'users-search',
        tbodyId: 'users-tbody',
        countId: 'users-count',
        endpoint: `${window.APP_CONFIG?.baseUrl}api/users`,
        resourceLabel: 'user',
        addButtonId: 'add-user-btn'
    });

    // 3. Initialize the delete functionality
    initDeleteUser();

    // 4. Initial count check
    updateCount('user', '#users-tbody', '#users-count');
    
    // 5. Initialize the detailed view/profile modal
    initViewUser();

    // 6. Initialize Infinite Scroll for those 7,000+ users
    initUserInfiniteScroll(); 
}
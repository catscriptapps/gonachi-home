// /resources/js/pages/users-page.js

import { initUsersModal } from '../modals/users-modal.js';
import { updateCount } from '../components/table-pagination-count.js';
import { initDeleteUser } from '../utils/users/delete-user.js';
import { enableTableSearch } from '../components/table-search.js';
import { initViewUser } from '../utils/users/view-user.js';
import { initUserInfiniteScroll } from '../utils/users/infinite-scroll-users.js';
import { initUserTableControls } from '../utils/users/table-controls.js';

/**
 * Initialize the Users page JS.
 */
export function init() {
    // 1. Initialize the Create/Edit modal logic
    initUsersModal();

    // 2. Infinite scroll and search both need to know the CURRENT column
    // sort/filter state on every request — tableControls is declared below,
    // but these closures only read it once actually invoked (on scroll/input),
    // by which point it's assigned.
    const scroll = initUserInfiniteScroll(() => tableControls.getParams());

    const search = enableTableSearch({
        searchInputId: 'users-search',
        tbodyId: 'users-tbody',
        countId: 'users-count',
        endpoint: `${window.APP_CONFIG?.baseUrl}api/users`,
        resourceLabel: 'user',
        addButtonId: 'add-user-btn',
        getExtraParams: () => tableControls.getParams(),
    });

    // 3. Column sort headers + per-column filter inputs. Any change replaces
    // the table with a fresh page 1 and resets infinite scroll's pager.
    const tableControls = initUserTableControls({
        onStateChange: () => {
            scroll.resetPage();
            search.refresh();
        }
    });

    // 4. Initialize the delete functionality
    initDeleteUser();

    // 5. Initial count check
    updateCount('user', '#users-tbody', '#users-count');

    // 6. Initialize the detailed view/profile modal
    initViewUser();
}

// /resources/js/utils/users/table-controls.js

/**
 * Wires the Users table's column sort headers (click to sort, click again to
 * flip direction) and per-column header text filters. Owns the current
 * sort/filter state and hands it to the caller (users-page.js) via
 * getParams(), which is passed straight through to table-search.js's
 * getExtraParams and infinite-scroll-users.js's getExtraParams so every
 * request — first page, filtered search, or scrolled page 2+ — stays
 * consistent.
 */
export function initUserTableControls({ onStateChange } = {}) {
    const state = {
        sort: '',
        dir: 'asc',
        filter_name: '',
        filter_location: '',
        filter_roles: '',
        filter_status: '',
    };

    const sortButtons = document.querySelectorAll('.sort-header-btn');
    const filterInputs = document.querySelectorAll('[data-filter-key]');

    function updateArrows() {
        sortButtons.forEach((btn) => {
            const arrow = btn.querySelector('.sort-arrow');
            if (!arrow) return;

            if (btn.dataset.sortKey === state.sort) {
                arrow.classList.remove('opacity-0');
                arrow.classList.toggle('rotate-180', state.dir === 'desc');
            } else {
                arrow.classList.add('opacity-0');
                arrow.classList.remove('rotate-180');
            }
        });
    }

    sortButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            const key = btn.dataset.sortKey;
            if (state.sort === key) {
                state.dir = state.dir === 'asc' ? 'desc' : 'asc';
            } else {
                state.sort = key;
                state.dir = 'asc';
            }
            updateArrows();
            onStateChange?.();
        });
    });

    let debounceTimer = null;
    filterInputs.forEach((input) => {
        input.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                state[`filter_${input.dataset.filterKey}`] = input.value.trim();
                onStateChange?.();
            }, 350);
        });
    });

    return {
        getParams: () => ({ ...state }),
    };
}

// /resources/js/utils/users/infinite-scroll-users.js

/**
 * @param {() => Object} [getExtraParams] Optional — returns the current
 *   sort/filter state (see table-controls.js) so subsequent pages keep the
 *   same sort/filter applied as page 1.
 */
export function initUserInfiniteScroll(getExtraParams) {
    const tableBody = document.getElementById('users-tbody');
    if (!tableBody) return { resetPage: () => {} };

    let currentPage = 1;
    let isLoading = false;
    let hasMore = true;
    let throttleTimeout = null;

    const loadMoreUsers = async () => {
        if (isLoading || !hasMore) return;

        isLoading = true;
        currentPage++;

        const searchInput = document.getElementById('users-search');
        const query = searchInput ? searchInput.value : '';

        const params = new URLSearchParams({ page: currentPage, q: query });
        if (typeof getExtraParams === 'function') {
            const extra = getExtraParams() || {};
            Object.entries(extra).forEach(([key, value]) => {
                if (value !== '' && value !== null && typeof value !== 'undefined') {
                    params.set(key, value);
                }
            });
        }

        try {
            // Using your existing API endpoint
            const response = await fetch(`${window.APP_CONFIG?.baseUrl}api/users?${params.toString()}`);
            const result = await response.json();

            if (result.success && result.data && result.data.length > 0) {
                const rowsHtml = result.data.map(item => item.rowHtml).join('');

                // Append the rows smoothly
                tableBody.insertAdjacentHTML('beforeend', rowsHtml);

                // Update the count label logic (using your existing component)
                const countEl = document.getElementById('users-count');
                if (countEl && result.meta) {
                    // Update to show "Showing X of Y"
                    const currentCount = tableBody.querySelectorAll('tr').length;
                    countEl.textContent = `Showing ${currentCount} of ${result.meta.total} users`;
                }

                hasMore = result.meta.hasMore;
            } else {
                hasMore = false;
            }
        } catch (error) {
            console.error("Infinite scroll error:", error);
            currentPage--; // Reset page on failure to allow retry
        } finally {
            isLoading = false;
        }
    };

    const handleScroll = () => {
        if (throttleTimeout) return;

        throttleTimeout = setTimeout(() => {
            throttleTimeout = null;

            // Trigger load when 400px from the bottom
            const scrollBottom = window.innerHeight + window.scrollY;
            const threshold = document.documentElement.scrollHeight - 400;

            if (scrollBottom >= threshold) {
                loadMoreUsers();
            }
        }, 200); // 200ms Throttle
    };

    window.addEventListener('scroll', handleScroll);

    return {
        // Called by table-controls.js whenever sort/filter state changes —
        // performSearch() already replaced the tbody with a fresh page 1, so
        // the next scroll-triggered load must resume from page 2 of that
        // new result set, not silently continue the old one.
        resetPage: () => {
            currentPage = 1;
            hasMore = true;
        }
    };
}

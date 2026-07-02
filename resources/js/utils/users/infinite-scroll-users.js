// /resources/js/utils/users/infinite-scroll-users.js

export function initUserInfiniteScroll() {
    const tableBody = document.getElementById('users-tbody');
    if (!tableBody) return;

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
        
        try {
            // Using your existing API endpoint
            const response = await fetch(`${window.APP_CONFIG?.baseUrl}api/users?page=${currentPage}&q=${encodeURIComponent(query)}`);
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
}
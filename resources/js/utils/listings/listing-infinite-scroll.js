// /resources/js/utils/listings/listing-infinite-scroll.js
//
// Infinite scroll for /listings and /my-listings — the one Real Estate
// World module here that genuinely paginates (Adverts/Quotations/Mentors
// render everything in one go), matching legacy's actual UX. Uses an
// IntersectionObserver on #listings-load-more-sentinel; state resets to
// page 1 whenever listing-search.js reports a new query via the
// listings-search-updated window event.

import { AnimationEngine } from '../animations.js';

export function initListingInfiniteScroll({ endpointParam = '' } = {}) {
  const sentinel = document.getElementById('listings-load-more-sentinel');
  const grid = document.getElementById('listings-grid');
  if (!sentinel || !grid || sentinel.dataset.infiniteScrollInitialized) return;
  sentinel.dataset.infiniteScrollInitialized = 'true';

  let page = parseInt(sentinel.dataset.page || '1', 10);
  let hasMore = sentinel.dataset.hasMore === '1';
  let isLoading = false;
  let currentQuery = '';
  let currentEndpointParam = endpointParam;
  let isResetting = false;

  window.addEventListener('listings-search-updated', (e) => {
    currentQuery = e.detail?.query || '';
    currentEndpointParam = e.detail?.endpointParam ?? endpointParam;
    page = 1;
    hasMore = sentinel.dataset.hasMore === '1';
    isResetting = true;
    setTimeout(() => { isResetting = false; }, 500);
  });

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting && hasMore && !isLoading && !isResetting) {
          loadNextPage();
        }
      });
    },
    { rootMargin: '250px', threshold: 0.1 }
  );

  observer.observe(sentinel);

  async function loadNextPage() {
    isLoading = true;
    page += 1;

    const baseUrl = window.APP_CONFIG?.baseUrl || '/';
    const params = new URLSearchParams();
    if (currentEndpointParam) {
      currentEndpointParam.split('&').forEach((pair) => {
        const [k, v] = pair.split('=');
        params.set(k, v);
      });
    }
    if (currentQuery) params.set('q', currentQuery);
    params.set('page', String(page));

    try {
      const response = await fetch(`${baseUrl}api/listings?${params.toString()}`);
      const result = await response.json();

      if (result.success && result.html) {
        grid.insertAdjacentHTML('beforeend', result.html);
        AnimationEngine.refresh();
        window.dispatchEvent(new CustomEvent('listing:updated'));
      }

      hasMore = !!result.hasMore;
      if (!hasMore) observer.unobserve(sentinel);
    } catch (err) {
      console.error('Listing infinite scroll error:', err);
      page -= 1;
    } finally {
      isLoading = false;
    }
  }
}

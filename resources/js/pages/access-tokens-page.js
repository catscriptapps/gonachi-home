// /resources/js/pages/access-tokens-page.js

import { enableTableSearch } from '../components/table-search.js';
import { updateCount } from '../components/table-pagination-count.js';
import { initRevokeAccessToken } from '../utils/access-tokens/revoke-access-token.js';

/**
 * Initialize the Access Tokens page JS.
 */
export function init() {
    const baseUrl = window.APP_CONFIG?.baseUrl;

    enableTableSearch({
        searchInputId: 'access-tokens-search',
        tbodyId: 'access-tokens-tbody',
        countId: 'access-tokens-count',
        endpoint: `${baseUrl}api/access-tokens`,
        resourceLabel: 'token'
    });

    initRevokeAccessToken();

    updateCount('token', '#access-tokens-tbody', '#access-tokens-count');
}

// /resources/js/utils/full-reload-links.js
//
// A handful of links intentionally skip the SPA router (data-partial) because
// they cross between layouts entirely — switching projects, or returning to
// the portal hub — each of which has its own sidebar/header shell a partial
// swap can't replace. That means no loadPartial() ever runs for them, so the
// full-page loading overlay (resources/js/ui/spinner.js) never shows and the
// visitor sees a blank gap between the old page unloading and the new one
// painting. This shows that same overlay immediately on click, purely as a
// "something is happening" cue for the native navigation already underway —
// it's never explicitly hidden, since the current page (and this listener)
// is about to be torn down anyway once the browser navigates.

import { showSpinner } from '../ui/spinner.js';

export function wireFullReloadLinks() {
    document.addEventListener('click', (e) => {
        const link = e.target.closest('a[data-full-reload]');
        if (!link) return;

        // A modifier click (new tab/window) or an explicit target="_blank"
        // leaves the current page in place — showing the overlay here would
        // never get hidden since no navigation actually carries this page away.
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) return;
        if (link.target === '_blank') return;

        showSpinner();
    });
}

// /resources/js/ui/spinner.js

/**
 * Manages the global loading spinner UI element.
 */

const spinner = document.createElement('div');

export function setupSpinner() {
    // Create and append the spinner element only once.
    // `fixed` (not `absolute`) is required here: appended directly to <body>
    // with no positioned ancestor, an `absolute inset-0` box resolves against
    // the initial containing block — anchored to the top of the document at
    // just one viewport's height — so on any page taller than the screen (or
    // once scrolled) it only ever covers the top portion, never the bottom.
    // `fixed inset-0` always spans the actual visible viewport regardless of
    // document height or scroll position, so the spinner sits dead-centre
    // over the whole screen every time.
    spinner.className = 'fixed inset-0 flex items-center justify-center bg-white dark:bg-gray-900 bg-opacity-80 z-50 hidden';
    spinner.innerHTML = `<div class="w-12 h-12 border-4 border-orange-500 border-dashed rounded-full animate-spin"></div>`;
    document.body.appendChild(spinner);
}

export function showSpinner() {
    spinner.classList.remove('hidden');
}

export function hideSpinner() {
    spinner.classList.add('hidden');
}
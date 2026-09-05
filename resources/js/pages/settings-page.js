// /resources/js/pages/settings-page.js
import { AnimationEngine } from '../utils/animations';
import { showToast } from '../ui/toast.js';

export function init() {
    AnimationEngine.refresh();

    // Toggle logic for Settings (decorative mockup toggles elsewhere on
    // this page — not the real, API-backed Scraping Controls toggles below).
    const toggles = document.querySelectorAll('button[class*="rounded-full relative"]');
    toggles.forEach(toggle => {
        toggle.addEventListener('click', () => {
            const dot = toggle.querySelector('div');
            toggle.classList.toggle('bg-primary-500');
            toggle.classList.toggle('bg-gray-300');
            dot.classList.toggle('right-1');
            dot.classList.toggle('left-1');
        });
    });

    // Sidebar navigation simulation
    const navBtns = document.querySelectorAll('aside button');
    navBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            navBtns.forEach(b => b.className = 'w-full flex items-center gap-4 px-6 py-4 rounded-2xl transition-all text-gray-500 hover:bg-white dark:hover:bg-white/5');
            btn.className = 'w-full flex items-center gap-4 px-6 py-4 rounded-2xl transition-all bg-secondary-900 text-white shadow-xl shadow-secondary-900/20';
        });
    });

    initScrapingControls();
}

// -------------------------------
// Scraping Controls (admin-only, real API-backed toggles)
// -------------------------------

function initScrapingControls() {
    const container = document.getElementById('scraping-controls');
    if (!container) return;

    const baseUrl = window.APP_CONFIG?.baseUrl || '/';

    const applyState = (state) => {
        container.querySelectorAll('[data-scraping-toggle]').forEach((btn) => {
            const key = btn.dataset.scrapingToggle;
            const enabled = !!state[key];
            paintToggle(btn, enabled);
            btn.disabled = false;

            const label = container.querySelector(`[data-status-label="${key}"]`);
            if (label) label.textContent = enabled ? 'Running on schedule.' : 'Paused — no new items will be scraped.';
        });
    };

    fetch(`${baseUrl}api/system-settings`)
        .then((r) => r.json())
        .then((result) => {
            if (result.success) applyState(result);
        })
        .catch((err) => console.error('Load system settings error:', err));

    container.querySelectorAll('[data-scraping-toggle]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const key = btn.dataset.scrapingToggle;
            const nextEnabled = !toggleIsOn(btn);

            btn.disabled = true;
            paintToggle(btn, nextEnabled);

            try {
                const response = await fetch(`${baseUrl}api/system-settings`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ [key]: nextEnabled }),
                });
                const result = await response.json();

                if (result.success) {
                    applyState(result);
                    showToast(nextEnabled ? 'Scraping resumed.' : 'Scraping paused.', 'success');
                } else {
                    paintToggle(btn, !nextEnabled);
                    showToast((result.messages || ['Could not update setting.'])[0], 'error');
                }
            } catch (err) {
                console.error('Update system settings error:', err);
                paintToggle(btn, !nextEnabled);
                showToast('Unexpected error.', 'error');
            } finally {
                btn.disabled = false;
            }
        });
    });
}

function toggleIsOn(btn) {
    return btn.classList.contains('bg-teal-500');
}

function paintToggle(btn, enabled) {
    const dot = btn.querySelector('div');
    btn.classList.toggle('bg-teal-500', enabled);
    btn.classList.toggle('bg-gray-300', !enabled);
    btn.classList.toggle('dark:bg-gray-700', !enabled);
    dot.classList.toggle('right-1', enabled);
    dot.classList.toggle('left-1', !enabled);
}
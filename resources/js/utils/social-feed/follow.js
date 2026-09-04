// /resources/js/utils/social-feed/follow.js
//
// The follow graph UI: sidebar mini-profile stats, "Who to follow"
// suggestions, the people-search box + dropdown, and the follow/unfollow
// toggle button (delegated — works for both suggestion rows and search
// results, both rendered server-side via components/users/social-search-item.php).

let searchDebounce = null;

export function initFollowUi() {
  loadStats();
  loadSuggestions();
  wireSearch();
  wireFollowToggle();
}

async function loadStats() {
  const followingEl = document.getElementById('following-count');
  const followersEl = document.getElementById('followers-count');
  if (!followingEl || !followersEl) return;

  const baseUrl = window.APP_CONFIG?.baseUrl || '/';

  try {
    const response = await fetch(`${baseUrl}api/social-relations?action=get-stats`);
    const result = await response.json();
    if (result.success) {
      followingEl.textContent = result.following;
      followersEl.textContent = result.followers;
    }
  } catch (err) {
    console.error('Load follow stats error:', err);
  }
}

async function loadSuggestions() {
  const box = document.getElementById('suggested-follows');
  if (!box) return;

  const baseUrl = window.APP_CONFIG?.baseUrl || '/';

  try {
    const response = await fetch(`${baseUrl}api/social-relations`);
    const result = await response.json();

    if (!result.success || !result.users?.length) {
      box.innerHTML = `<p class="text-xs text-gray-400 text-center py-2">No suggestions right now.</p>`;
      return;
    }

    box.innerHTML = result.html;
  } catch (err) {
    console.error('Load suggestions error:', err);
    box.innerHTML = `<p class="text-xs text-gray-400 text-center py-2">Couldn't load suggestions.</p>`;
  }
}

function wireSearch() {
  const input = document.getElementById('user-search-input');
  const dropdown = document.getElementById('search-results-dropdown');
  const content = document.getElementById('search-results-content');
  if (!input || !dropdown || !content) return;

  input.addEventListener('input', () => {
    clearTimeout(searchDebounce);
    const query = input.value.trim();

    if (query === '') {
      dropdown.classList.add('hidden');
      return;
    }

    searchDebounce = setTimeout(() => runSearch(query, dropdown, content), 300);
  });

  document.addEventListener('click', (e) => {
    if (!dropdown.contains(e.target) && e.target !== input) {
      dropdown.classList.add('hidden');
    }
  });
}

async function runSearch(query, dropdown, content) {
  const baseUrl = window.APP_CONFIG?.baseUrl || '/';
  content.innerHTML = `<div class="flex justify-center py-4"><div class="animate-spin rounded-full h-4 w-4 border-2 border-secondary-500 border-t-transparent"></div></div>`;
  dropdown.classList.remove('hidden');

  try {
    const response = await fetch(`${baseUrl}api/social-relations?action=search&q=${encodeURIComponent(query)}`);
    const result = await response.json();

    if (!result.success || !result.users?.length) {
      content.innerHTML = `<p class="text-xs text-gray-400 text-center py-4">No people found.</p>`;
      return;
    }

    content.innerHTML = result.html;
  } catch (err) {
    console.error('User search error:', err);
    content.innerHTML = `<p class="text-xs text-gray-400 text-center py-4">Search failed.</p>`;
  }
}

function wireFollowToggle() {
  if (document._followToggleAttached) return;
  document._followToggleAttached = true;

  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-follow-toggle]');
    if (!btn) return;

    const targetId = btn.dataset.followToggle;
    const baseUrl = window.APP_CONFIG?.baseUrl || '/';

    btn.disabled = true;

    try {
      const response = await fetch(`${baseUrl}api/social-relations`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ following_id: targetId }),
      });
      const result = await response.json();

      if (result.success) {
        const nowFollowing = result.status === 'followed';
        btn.dataset.following = nowFollowing ? '1' : '0';
        btn.textContent = nowFollowing ? 'Following' : 'Follow';
        btn.className = `follow-toggle-btn flex-shrink-0 px-3 py-1.5 text-[9px] font-black uppercase tracking-widest rounded-lg transition-all active:scale-95 ${nowFollowing ? 'bg-gray-200 dark:bg-gray-800 text-gray-500 dark:text-gray-400' : 'bg-secondary-500 hover:bg-primary-500 text-white'}`;
        loadStats();
      } else {
        showFollowError(result.message);
      }
    } catch (err) {
      console.error('Follow toggle error:', err);
    } finally {
      btn.disabled = false;
    }
  });
}

function showFollowError(message) {
  import('../../ui/toast.js').then(({ showToast }) => showToast(message || 'Could not update follow status.', 'error'));
}

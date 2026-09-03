<?php
// /resources/views/pages/saved-searches.php
//
// Saved Alerts — a user saves a (search, region) pair (the exact filter
// vocabulary already live on /real-estate-leads, see
// LeadsController::browse()) and comes back to see how many active leads
// match it, with a "N new since you last checked" badge. Create/delete are
// pure AJAX (resources/js/pages/saved-searches-page.js) — no page reload.
//
// @var bool $isLoggedIn
// @var string $baseUrl

declare(strict_types=1);

use Src\Controller\LeadsController;
use Src\Controller\SavedSearchController;
use Src\Service\AuthService;

$currentUserId = $isLoggedIn ? AuthService::userId() : null;
?>
<div class="space-y-6">
    <?php
    $breadcrumbs = [
        ['label' => 'Real Estate Leads', 'href' => $baseUrl . 'real-estate-leads'],
        ['label' => 'Saved Alerts'],
    ];
    include __DIR__ . '/../components/breadcrumbs.php';
    ?>

    <div>
        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Saved Alerts</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Get notified the moment a new lead matches a search you care about.</p>
    </div>

    <?php if (!$currentUserId): ?>
        <div class="max-w-lg mx-auto text-center py-20">
            <svg class="h-10 w-10 text-gray-300 dark:text-gray-700 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L7 21V5z"/></svg>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Sign In To Save Alerts</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Saved alerts and match notifications live on your account.</p>
            <a href="<?= $baseUrl ?>login" data-login-button class="inline-flex items-center mt-6 px-5 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-bold rounded-lg transition-all shadow-sm">
                Sign In
            </a>
        </div>
    <?php else: ?>
        <?php
        $regions = LeadsController::regions();
        $regionNameBySlug = $regions->pluck('name', 'slug');

        $savedSearches = SavedSearchController::forUser($currentUserId);
        $savedSearchData = $savedSearches->map(function ($s) use ($regionNameBySlug) {
            return [
                'id' => $s->id,
                'search_query' => $s->search_query,
                'region_slug' => $s->region_slug,
                'region_name' => $s->region_slug ? ($regionNameBySlug[$s->region_slug] ?? ucfirst($s->region_slug)) : null,
                'total' => LeadsController::countMatching($s->search_query, $s->region_slug),
                'new_count' => LeadsController::countNewMatching($s->search_query, $s->region_slug, $s->last_viewed_at),
            ];
        });

        // Only after this visit's new-match counts are computed above —
        // otherwise every alert would show 0 new on the very visit meant to reveal them.
        SavedSearchController::markAllViewed($currentUserId);
        ?>

        <!-- Create Alert -->
        <form id="saved-search-form" class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col md:flex-row gap-4 items-center">
            <div class="w-full md:flex-1 relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" id="saved-search-q" placeholder="Keyword to watch for (e.g., 'Lagos House', 'Land')..." class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none text-gray-900 dark:text-white" />
            </div>
            <div class="w-full md:w-48">
                <select id="saved-search-region" class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none text-gray-700 dark:text-gray-300">
                    <option value="">Any Region</option>
                    <?php foreach ($regions as $r): ?>
                        <option value="<?= htmlspecialchars($r->slug) ?>"><?= htmlspecialchars($r->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" id="saved-search-submit" class="w-full md:w-auto px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-bold text-sm rounded-lg transition-colors shadow-sm whitespace-nowrap">
                Save Alert
            </button>
        </form>

        <!-- Saved Alerts List -->
        <div id="saved-search-list" class="space-y-3">
            <?php if ($savedSearchData->isEmpty()): ?>
                <div id="saved-search-empty" class="bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-800 rounded-xl p-10 text-center">
                    <svg class="h-8 w-8 text-gray-300 dark:text-gray-700 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L7 21V5z"/></svg>
                    <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300">No Saved Alerts Yet</h4>
                    <p class="text-xs text-gray-400 dark:text-gray-500 max-w-sm mx-auto mt-1">
                        Save a keyword or region above and new matching leads will show up here.
                    </p>
                </div>
            <?php else: ?>
                <?php foreach ($savedSearchData as $s): ?>
                    <?php
                    $label = $s['search_query'] && $s['region_name']
                        ? "\"{$s['search_query']}\" in {$s['region_name']}"
                        : ($s['search_query'] ? "\"{$s['search_query']}\"" : $s['region_name']);
                    $matchUrl = $baseUrl . 'real-estate-leads?' . http_build_query(array_filter([
                        'q' => $s['search_query'],
                        'region' => $s['region_slug'],
                    ]));
                    ?>
                    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 flex items-center justify-between gap-4" data-saved-search-id="<?= $s['id'] ?>">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white truncate"><?= htmlspecialchars($label) ?></h4>
                                <?php if ($s['new_count'] > 0): ?>
                                    <span class="flex-shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-400">
                                        <?= $s['new_count'] ?> new
                                    </span>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1"><?= $s['total'] ?> active lead<?= $s['total'] === 1 ? '' : 's' ?> match right now</p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <a href="<?= htmlspecialchars($matchUrl) ?>" data-partial class="inline-flex items-center px-3 py-2 bg-gray-900 hover:bg-gray-800 dark:bg-primary-600 dark:hover:bg-primary-500 text-white font-bold text-xs rounded-lg transition-colors shadow-sm">
                                View Matches
                            </a>
                            <button type="button" data-delete-saved-search="<?= $s['id'] ?>" aria-label="Delete alert" class="p-2 rounded-lg text-gray-400 hover:bg-red-50 dark:hover:bg-red-950/40 hover:text-red-600 dark:hover:text-red-400 transition-colors">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

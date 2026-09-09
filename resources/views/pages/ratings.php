<?php
// /resources/views/pages/ratings.php

declare(strict_types=1);

/**
 * Real Estate World - Ratings
 *
 * Ported from the legacy gonachi-old platform's standalone /ratings/ app: a
 * user rates ANOTHER user (not a listing/property) in the context of a
 * role and location, leaving a free-text review plus optional 1-5 star
 * scores against a criteria set that depends on the chosen role. Guests
 * see marketing copy only, matching every other Real Estate World module
 * here (legacy itself allowed guest browsing, but gonachi-home
 * consistently gates every module's actual content behind login).
 *
 * @var bool $isLoggedIn
 * @var string $baseUrl
 * @var string $assetBase
 */

use Src\Controller\RatingsController;

$breadcrumbs = [['label' => 'Ratings']];
$breadcrumbAccent = 'teal';

$totalRatings = RatingsController::totalCount();
?>
<div class="space-y-6">
    <?php include __DIR__ . '/../components/breadcrumbs.php'; ?>

    <section class="relative overflow-hidden rounded-3xl shadow-sm bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-6 p-6 sm:p-10">
            <div>
                <span class="inline-block text-xs font-semibold tracking-[0.2em] text-teal-600 dark:text-teal-400 uppercase mb-2">Ratings</span>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Rate Your Experience</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 max-w-md">Rate landlords, tenants, contractors, agents, and more — help the community make informed decisions.</p>

                <div class="flex flex-wrap items-center gap-3 mt-5">
                    <?php if ($isLoggedIn): ?>
                        <button type="button" class="rate-user-trigger inline-flex items-center px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm rounded-lg transition-colors shadow-sm">
                            Rate a User
                        </button>
                    <?php else: ?>
                        <button type="button" class="auth-gate-btn inline-flex items-center px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm rounded-lg transition-colors shadow-sm">
                            Rate a User
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex items-center space-x-4 bg-gray-50 dark:bg-gray-800/60 p-2 rounded-xl border border-gray-200 dark:border-gray-800 flex-shrink-0">
                <div class="px-4 py-2 text-center">
                    <span class="block text-2xl font-bold text-teal-600 dark:text-teal-400"><?= $totalRatings ?></span>
                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Ratings Posted</span>
                </div>
            </div>
        </div>
    </section>

    <?php if (!$isLoggedIn): ?>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <?php
            $tiles = [
                ['Rate Anyone', 'Landlords, tenants, contractors, agents — rate anyone on the platform.'],
                ['Role-Specific', 'Star criteria adapt to the role you\'re rating them as.'],
                ['Look Anyone Up', 'Search a user to see every rating they\'ve received.'],
            ];
            foreach ($tiles as $tile): ?>
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5">
                    <div class="w-10 h-10 rounded-xl bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 flex items-center justify-center mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.54 1.118l-3.976-2.888a1 1 0 00-1.175 0l-3.976 2.888c-.784.57-1.838-.196-1.539-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>
                    </div>
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-1"><?= $tile[0] ?></h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400"><?= $tile[1] ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-3">Look Up Ratings</h3>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" id="ratings-lookup-input" placeholder="Search a user by name or email..." autocomplete="off"
                        class="w-full pl-9 pr-3 py-2.5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none text-gray-900 dark:text-white" />
                </div>
                <div id="ratings-lookup-results" class="space-y-2 mt-3"></div>

                <div id="ratings-lookup-list" class="space-y-3 mt-4 hidden"></div>
                <p id="ratings-lookup-empty" class="text-xs text-gray-400 text-center py-6">Search for a user above to see their ratings.</p>
            </div>

            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-3">Ratings I've Given</h3>
                <div id="my-ratings-given-list" class="space-y-3"></div>
                <p id="my-ratings-given-empty" class="hidden text-xs text-gray-400 text-center py-6">You haven't rated anyone yet.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

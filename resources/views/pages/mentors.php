<?php
// /resources/views/pages/mentors.php

declare(strict_types=1);

/**
 * Real Estate World - Mentors ("Expert Network")
 *
 * Ported from the legacy gonachi/ platform. A single shared directory —
 * unlike Adverts/Quotations there's no separate "my X" page here, matching
 * legacy exactly: any logged-in user can register as a mentor instantly
 * (no approval step), and everyone's active mentor profiles — including
 * your own, with edit/delete overlaid — show in the same feed. Guests see
 * marketing copy only, same as the other Real Estate World modules.
 *
 * @var bool $isLoggedIn
 * @var string $baseUrl
 * @var string $assetBase
 */

use App\Models\StakeholderType;
use Src\Controller\MentorsController;
use Src\Service\AuthService;

$breadcrumbs = [['label' => 'Mentors']];
$breadcrumbAccent = 'teal';

$viewerId = $isLoggedIn ? AuthService::userId() : null;
$feedHtml = '';
$totalActive = MentorsController::totalActiveCount();

// Mentor-category filter options — Real Estate World's own stakeholder
// list (Landlord, Tenant, Contractor, etc.), not the account-level user types.
$filterTypes = StakeholderType::orderBy('id')->get();

if ($isLoggedIn) {
    $result = MentorsController::browse(null, null, $viewerId);
    $feedHtml = $result['html'];
}
?>
<div class="space-y-6">
    <?php include __DIR__ . '/../components/breadcrumbs.php'; ?>

    <section class="relative overflow-hidden rounded-3xl shadow-sm bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-6 p-6 sm:p-10">
            <div>
                <span class="inline-block text-xs font-semibold tracking-[0.2em] text-teal-600 dark:text-teal-400 uppercase mb-2">Mentors</span>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Bridge the Knowledge Gap</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 max-w-md">Connect with seasoned experts. Search by skill or specialty.</p>

                <div class="flex flex-wrap items-center gap-3 mt-5">
                    <?php if ($isLoggedIn): ?>
                        <button type="button" class="register-mentor-trigger inline-flex items-center px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm rounded-lg transition-colors shadow-sm">
                            Become a Mentor
                        </button>
                    <?php else: ?>
                        <a href="<?= $baseUrl ?>login" data-login-button class="inline-flex items-center px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm rounded-lg transition-colors shadow-sm">
                            View All Mentors
                        </a>
                        <a href="<?= $baseUrl ?>signup?redirect=mentors" data-partial class="inline-flex items-center px-6 py-2.5 border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300 font-bold text-sm rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/60 transition-colors">
                            Join as a Mentor
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex items-center space-x-4 bg-gray-50 dark:bg-gray-800/60 p-2 rounded-xl border border-gray-200 dark:border-gray-800 flex-shrink-0">
                <div class="px-4 py-2 text-center">
                    <span class="block text-2xl font-bold text-teal-600 dark:text-teal-400"><?= $totalActive ?></span>
                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Active Mentors</span>
                </div>
                <div class="px-4 py-2 border-l border-gray-200 dark:border-gray-800 text-center">
                    <span class="block text-2xl font-bold text-primary-600 dark:text-primary-400">24/7</span>
                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Global Reach</span>
                </div>
            </div>
        </div>
    </section>

    <?php if (!$isLoggedIn): ?>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <?php
            $tiles = [
                ['Verified Experts', 'Every mentor is manually vetted for quality guidance.'],
                ['1-on-1 Sessions', 'Direct messaging to help you overcome specific blockers.'],
                ['Global Network', 'Connect with leaders across the real estate world.'],
            ];
            foreach ($tiles as $tile): ?>
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5">
                    <div class="w-10 h-10 rounded-xl bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 flex items-center justify-center mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    </div>
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-1"><?= $tile[0] ?></h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400"><?= $tile[1] ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Expert Directory</h3>
            <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                <div class="relative w-full sm:w-64">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" id="mentor-search-input" placeholder="Find a mentor..." class="w-full pl-9 pr-3 py-2 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none text-gray-900 dark:text-white" />
                </div>
                <select id="mentor-type-filter" class="w-full sm:w-48 py-2 px-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none text-gray-900 dark:text-white">
                    <option value="0">All Mentor Types</option>
                    <?php foreach ($filterTypes as $type): ?>
                        <option value="<?= $type->id ?>"><?= htmlspecialchars($type->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div id="mentors-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 <?= $feedHtml === '' ? 'hidden' : '' ?>">
            <?= $feedHtml ?>
        </div>

        <div id="empty-mentors-state" class="<?= $feedHtml !== '' ? 'hidden' : '' ?> bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-800 rounded-2xl p-10 text-center">
            <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300">No mentors joined yet</h4>
            <p class="text-xs text-gray-400 dark:text-gray-500 max-w-sm mx-auto mt-1 mb-4">Our expert network is just getting started. Be the first to share your expertise!</p>
            <button type="button" class="register-mentor-trigger inline-flex items-center px-5 py-2 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs rounded-lg transition-colors">
                Become a Founding Mentor
            </button>
        </div>

        <div id="no-mentors-found" class="hidden bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-800 rounded-2xl p-10 text-center">
            <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300">No matches found</h4>
            <p class="text-xs text-gray-400 dark:text-gray-500 max-w-sm mx-auto mt-1 mb-4">We couldn't find any experts matching those criteria.</p>
            <button type="button" id="clear-mentor-filters" class="text-xs font-bold text-teal-600 hover:text-teal-700 uppercase tracking-widest">Reset Filters</button>
        </div>

        <?php include __DIR__ . '/../components/mentors/view-mentor-modal.php'; ?>
    <?php endif; ?>
</div>

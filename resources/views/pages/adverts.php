<?php
// /resources/views/pages/adverts.php

declare(strict_types=1);

/**
 * Real Estate World - Adverts (public "Browse Adverts" feed)
 *
 * Ported from the legacy gonachi/ platform. Guests see marketing copy only
 * (they never see the ad grid — matches legacy exactly); logged-in users
 * see active ads filtered by targeting (their country + user type) against
 * each ad's audience selection.
 *
 * @var bool $isLoggedIn
 * @var string $baseUrl
 * @var string $assetBase
 */

use Src\Controller\AdvertsController;
use Src\Service\AuthService;

$breadcrumbs = [['label' => 'Adverts']];
$breadcrumbAccent = 'teal';

$viewerId = $isLoggedIn ? AuthService::userId() : null;
$feedHtml = '';
$totalActive = AdvertsController::totalActiveCount();

if ($isLoggedIn) {
    $adverts = AdvertsController::browse(null, $viewerId);
    foreach ($adverts as $advert) {
        $feedHtml .= AdvertsController::renderCard($advert, $viewerId);
    }
}
?>
<div class="space-y-6">
    <?php include __DIR__ . '/../components/breadcrumbs.php'; ?>

    <section class="relative overflow-hidden rounded-3xl shadow-sm bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-6 p-6 sm:p-10">
            <div>
                <span class="inline-block text-xs font-semibold tracking-[0.2em] text-teal-600 dark:text-teal-400 uppercase mb-2">Adverts</span>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Reach Real Estate World, Directly</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 max-w-md">Promote your business to landlords, tenants, agents, contractors, and property managers.</p>

                <div class="flex flex-wrap items-center gap-3 mt-5">
                    <a href="<?= $baseUrl ?><?= $isLoggedIn ? 'my-adverts' : 'login' ?>" <?= $isLoggedIn ? 'data-partial' : 'data-login-button' ?>
                        class="inline-flex items-center px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm rounded-lg transition-colors shadow-sm">
                        Get Started
                    </a>
                    <?php if ($isLoggedIn && AuthService::isAdmin()): ?>
                        <a href="<?= $baseUrl ?>adverts-admin" data-partial class="inline-flex items-center px-6 py-2.5 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-sm rounded-lg transition-colors">
                            Adverts Admin
                        </a>
                    <?php endif; ?>
                    <?php if (!$isLoggedIn): ?>
                        <button type="button" class="register-btn inline-flex items-center px-6 py-2.5 border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300 font-bold text-sm rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/60 transition-colors">
                            Register Now
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex items-center space-x-4 bg-gray-50 dark:bg-gray-800/60 p-2 rounded-xl border border-gray-200 dark:border-gray-800 flex-shrink-0">
                <div class="px-4 py-2 text-center">
                    <span class="block text-2xl font-bold text-teal-600 dark:text-teal-400"><?= $totalActive ?></span>
                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Active Adverts</span>
                </div>
                <div class="px-4 py-2 border-l border-gray-200 dark:border-gray-800 text-center">
                    <span class="block text-2xl font-bold text-primary-600 dark:text-primary-400">$0</span>
                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Starting At</span>
                </div>
            </div>
        </div>
    </section>

    <?php if (!$isLoggedIn): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <?php
            $tiles = [
                ['Secure Payments', 'Every transaction is protected end-to-end.', 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'],
                ['24/7 Approval', 'Our team reviews new adverts around the clock.', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['Targeted Reach', 'Show your advert only to the audience you choose.', 'M9.348 14.652a3.75 3.75 0 010-5.304m5.304 0a3.75 3.75 0 010 5.304m-7.425 2.121a6.75 6.75 0 010-9.546m9.546 0a6.75 6.75 0 010 9.546M5.106 18.894c-3.808-3.807-3.808-9.98 0-13.788m13.788 0c3.808 3.807 3.808 9.98 0 13.788M12 12h.008v.008H12V12z'],
                ['Live Feed Ads', 'Your advert appears right in the Social Feed too.', 'M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z'],
            ];
            foreach ($tiles as $tile): ?>
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5">
                    <div class="w-10 h-10 rounded-xl bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 flex items-center justify-center mb-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $tile[2] ?>" /></svg>
                    </div>
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-1"><?= $tile[0] ?></h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400"><?= $tile[1] ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="flex items-center justify-between gap-4">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Public Advert Feed</h3>
            <div class="relative w-full max-w-xs">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" id="ad-search-input" placeholder="Search adverts..." class="w-full pl-9 pr-3 py-2 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none text-gray-900 dark:text-white" />
            </div>
        </div>

        <div id="ads-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 <?= $feedHtml === '' ? 'hidden' : '' ?>">
            <?= $feedHtml ?>
        </div>

        <div id="empty-ads-state" class="<?= $feedHtml !== '' ? 'hidden' : '' ?> bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-800 rounded-2xl p-10 text-center">
            <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300">No adverts to show yet</h4>
            <p class="text-xs text-gray-400 dark:text-gray-500 max-w-sm mx-auto mt-1">Approved adverts targeted to you will appear here.</p>
        </div>

        <div id="ads-infinite-sentinel" class="h-4"></div>

        <?php include __DIR__ . '/../components/adverts/view-advert-modal.php'; ?>
    <?php endif; ?>
</div>

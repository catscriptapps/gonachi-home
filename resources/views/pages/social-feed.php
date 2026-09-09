<?php
// /resources/views/pages/social-feed.php

declare(strict_types=1);

/**
 * Real Estate World - Social Feed
 *
 * Ported from the legacy gonachi/ platform (resources/views/pages/
 * social-feed.php): guests get marketing copy only (same compact hero-card +
 * feature-tiles convention as adverts.php/listings.php/quotations.php, not
 * a standalone full-screen landing — see the (!$isLoggedIn) branch below);
 * logged-in users get a feed scoped to their own posts + posts from people
 * they follow (never a global stream — see SocialFeedController::feed()).
 *
 * @var bool $isLoggedIn
 * @var string $baseUrl
 * @var string $assetBase
 * @var string|null $path
 */

use Src\Controller\SocialFeedController;
use Src\Service\AuthService;

$breadcrumbs = [['label' => 'Social Feed']];
$breadcrumbAccent = 'teal';

$viewerId = $isLoggedIn ? AuthService::userId() : null;
$totalPosts = SocialFeedController::totalPostsCount();
$feedHtml = '';

if ($isLoggedIn) {
    $posts = SocialFeedController::feed($viewerId);
    foreach ($posts as $post) {
        $feedHtml .= SocialFeedController::renderPostCard($post, $viewerId);
    }
}
?>
<div class="space-y-6">
    <?php include __DIR__ . '/../components/breadcrumbs.php'; ?>

    <?php if (!$isLoggedIn): ?>
        <section class="relative overflow-hidden rounded-3xl shadow-sm bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
            <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-6 p-6 sm:p-10">
                <div>
                    <span class="inline-block text-xs font-semibold tracking-[0.2em] text-teal-600 dark:text-teal-400 uppercase mb-2">Social Feed</span>
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">The Inner Circle of Professionals</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 max-w-md">Share updates, photos, and videos with landlords, tenants, agents, contractors, and property managers across the network.</p>

                    <div class="flex flex-wrap items-center gap-3 mt-5">
                        <a href="<?= $baseUrl ?>login" data-login-button class="inline-flex items-center px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm rounded-lg transition-colors shadow-sm">
                            Sign In to Access Feed
                        </a>
                        <button type="button" class="register-btn inline-flex items-center px-6 py-2.5 border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300 font-bold text-sm rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/60 transition-colors">
                            Register Now
                        </button>
                    </div>
                </div>

                <div class="flex items-center space-x-4 bg-gray-50 dark:bg-gray-800/60 p-2 rounded-xl border border-gray-200 dark:border-gray-800 flex-shrink-0">
                    <div class="px-4 py-2 text-center">
                        <span class="block text-2xl font-bold text-teal-600 dark:text-teal-400"><?= $totalPosts ?></span>
                        <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Posts Shared</span>
                    </div>
                    <div class="px-4 py-2 border-l border-gray-200 dark:border-gray-800 text-center">
                        <span class="block text-2xl font-bold text-primary-600 dark:text-primary-400">Free</span>
                        <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">To Join</span>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <?php
            $tiles = [
                ['Follow-Scoped Feed', 'See posts from people you follow — never a noisy global stream.', 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5.13a4 4 0 11-8 0 4 4 0 018 0zm6 3a4 4 0 10-8 0'],
                ['Photos & Videos', 'Share updates with rich media, not just text.', 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
                ['Real Network', 'Connect with landlords, tenants, agents, and contractors.', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                ['Likes & Comments', 'Engage with posts the way you would anywhere else.', 'M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z'],
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
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <div class="lg:col-span-8 space-y-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black text-gray-900 dark:text-white font-sans tracking-tight">Social Feed</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 font-medium">
                        See what's happening across the network. Share updates, photos, and videos.
                    </p>
                </div>
                <button type="button" id="create-post-btn"
                    class="inline-flex items-center justify-center rounded-xl bg-teal-600 px-6 py-2.5 text-sm font-black text-white shadow-lg shadow-teal-600/20 hover:bg-teal-700 transition-all active:scale-95">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Share Post
                </button>
            </div>

            <!-- Composer Shortcut -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-4">
                <div class="flex items-center space-x-3">
                    <?php $me = AuthService::currentUser(); $meAvatar = $me->avatar_url ?? null; ?>
                    <?php if ($meAvatar): ?>
                        <img src="<?= $assetBase ?>images/uploads/avatars/<?= htmlspecialchars($meAvatar) ?>" class="h-10 w-10 rounded-full object-cover flex-shrink-0 border border-gray-100 dark:border-gray-800">
                    <?php else: ?>
                        <div class="h-10 w-10 rounded-full bg-teal-600 flex items-center justify-center text-white font-bold flex-shrink-0"><?= strtoupper(substr($me->full_name ?? 'U', 0, 1)) ?></div>
                    <?php endif; ?>
                    <button type="button" id="composer-shortcut-btn" class="flex-1 text-left px-4 py-2.5 bg-gray-50 dark:bg-gray-800 rounded-full text-sm text-gray-400 dark:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800/70 transition-colors">
                        What's on your mind?
                    </button>
                </div>
                <div class="flex items-center gap-2 mt-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" id="composer-photo-btn" class="flex-1 inline-flex items-center justify-center gap-2 py-2 rounded-lg text-xs font-bold text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        Photo
                    </button>
                    <button type="button" id="composer-video-btn" class="flex-1 inline-flex items-center justify-center gap-2 py-2 rounded-lg text-xs font-bold text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                        Video
                    </button>
                </div>
            </div>

            <div id="social-feed-container" class="space-y-4">
                <?php if ($feedHtml === ''): ?>
                    <div data-empty-feed class="bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-800 rounded-2xl p-10 text-center">
                        <svg class="h-8 w-8 text-gray-300 dark:text-gray-700 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" /></svg>
                        <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300">Your feed is quiet</h4>
                        <p class="text-xs text-gray-400 dark:text-gray-500 max-w-sm mx-auto mt-1">
                            Share your first update, or follow people from the sidebar to see their posts here.
                        </p>
                    </div>
                <?php else: ?>
                    <?= $feedHtml ?>
                <?php endif; ?>
            </div>
        </div>

        <?php include __DIR__ . '/../components/social-feed/sidebar.php'; ?>
    </div>

    <?php include __DIR__ . '/../components/social-feed/view-post-modal.php'; ?>
    <?php endif; ?>
</div>

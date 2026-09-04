<?php
// /resources/views/layouts/portal.php

declare(strict_types=1);

/**
 * Gonachi Portal Layout
 * The umbrella "hub" shell used by the landing page and cross-project
 * pages — no project sidebar, since it sits a level above any one
 * project. Individual projects (e.g. Real Estate Leads) render inside
 * layouts/app.php instead, once a visitor picks a tab from here.
 */
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50 dark:bg-gray-950">

<head>
    <script>
        // Applies the saved theme before anything else paints, so there's no
        // light-mode flash on a dark-mode reload, and so Alpine's theme store
        // (app.js) reads the correct starting class instead of a stale one.
        (function () {
            if (localStorage.getItem('user-theme') === 'dark') {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($title) . ' | ' . htmlspecialchars($appName); ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <link rel="icon" type="image/x-icon" href="<?= $assetBase ?>images/logo/favicon.png">

    <script>
        window.APP_CONFIG = {
            baseUrl: <?= json_encode($baseUrl) ?>,
            assetBase: <?= json_encode($assetBase) ?>,
            appName: <?= json_encode($appName) ?>,
            protectedPaths: <?= json_encode($protectedPaths ?? []) ?>,
            mediaLimit: <?= getMediaLimit() ?>,
            isLoggedIn: <?= json_encode($isLoggedIn ?? false) ?>,
        };
    </script>

    <link rel="stylesheet" href="<?= $assetBase ?>assets/css/app.min.css">
</head>

<body class="font-sans antialiased h-full overflow-x-hidden text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-950" x-data="{}">

    <div class="flex flex-col min-h-screen">

        <header class="sticky top-0 z-40 h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8 border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 transition-colors duration-300">
            <a href="<?= $baseUrl ?>" class="flex items-center flex-shrink-0">
                <img src="<?= $assetBase ?>images/logo/favicon.png" alt="Gonachi Logo" class="h-10 w-10 rounded-full object-contain bg-white ring-1 ring-black/5 dark:ring-white/10" />
            </a>

            <div class="flex items-center space-x-3">
                <?php if ($isLoggedIn && \Src\Service\AuthService::isAdmin()): ?>
                    <?php // No data-partial: /admin renders under layouts/app.php (sidebar +
                    // sticky header), while this portal page has neither — a partial
                    // swap only replaces #main-content, so it can't retroactively add
                    // chrome this page's shell never had. Same reason project-switcher.php's
                    // cross-project links and home.php's project cards don't use it either. ?>
                    <a href="<?= $baseUrl ?>admin" class="relative flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h7v7H3V3zm0 11h7v7H3v-7zm11-11h7v7h-7V3zm0 11h7v7h-7v-7z" />
                        </svg>
                        <span class="hidden sm:inline">Admin</span>
                        <span id="live-chat-nav-badge" class="hidden absolute -top-1 -right-1 min-w-[1.25rem] h-5 px-1 rounded-full bg-red-600 text-white text-xs font-bold flex items-center justify-center border-2 border-white dark:border-gray-900">0</span>
                    </a>
                <?php endif; ?>

                <!-- DB Reset Trigger (Cat only) -->
                <?php if ($isLoggedIn && \Src\Service\AuthService::isCat()): ?>
                    <button data-reset-button data-tooltip="DB Reset" title="Reset Database"
                        class="p-2 rounded-xl text-gray-500 dark:text-gray-400 hover:bg-red-50 dark:hover:bg-red-950/40 hover:text-red-600 dark:hover:text-red-400 transition-all focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                <?php endif; ?>

                <button
                    @click="$store.theme.toggle()"
                    class="p-2 rounded-xl text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all focus:outline-none"
                    aria-label="Toggle Dark Mode">
                    <svg x-show="!$store.theme.isDark" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.344l-.707.707M12 5a7 7 0 100 14 7 7 0 000-14z" />
                    </svg>
                    <svg x-show="$store.theme.isDark" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-cloak>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>

                <?php if ($isLoggedIn): ?>
                    <?php $accountInfo = \Src\Config\NavigationConfig::getUserDisplayInfo(); ?>
                    <a href="#" data-logout-button class="flex items-center space-x-3 group cursor-pointer">
                        <div class="w-8 h-8 rounded-full bg-primary-600 text-white font-bold flex items-center justify-center text-sm shadow-sm">
                            <?= htmlspecialchars($accountInfo['initial']) ?>
                        </div>
                        <div class="hidden md:flex flex-col leading-tight">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors"><?= htmlspecialchars($accountInfo['displayName']) ?></span>
                            <span class="text-xs text-gray-400 group-hover:text-primary-500 dark:group-hover:text-primary-400 transition-colors">Sign Out</span>
                        </div>
                    </a>
                <?php else: ?>
                    <a href="<?= $baseUrl ?>login" data-login-button class="text-sm font-semibold text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                        Sign In
                    </a>
                <?php endif; ?>
            </div>
        </header>

        <main class="flex-1">
            <div id="modal-zone"></div>
            <div id="main-content">
                <?php include $pageFile; ?>
            </div>
        </main>

        <footer class="border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 py-6 transition-colors duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                <span class="text-xs text-gray-400 dark:text-gray-500">&copy; <?= date('Y') ?> Gonachi. All rights reserved.</span>
                <div class="flex space-x-6 text-xs font-medium text-gray-500 dark:text-gray-400">
                    <a href="#" class="hover:text-primary-600 transition-colors">Terms of Service</a>
                    <a href="#" class="hover:text-primary-600 transition-colors">Privacy Policy</a>
                </div>
            </div>
        </footer>
    </div>

    <?php include __DIR__ . '/../components/chat-widget.php'; ?>

    <?php if ($isLoggedIn && isset($_SESSION['user_id'])): ?>
        <script>
            window.sessionUserId = <?= json_encode($_SESSION['user_id']) ?>;
        </script>
    <?php endif; ?>

    <script type="module" src="<?= $assetBase ?>assets/js/app.min.js"></script>
</body>

</html>

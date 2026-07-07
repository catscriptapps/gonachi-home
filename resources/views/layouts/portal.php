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
        };
    </script>

    <link rel="stylesheet" href="<?= $assetBase ?>assets/css/app.min.css">
</head>

<body class="font-sans antialiased h-full overflow-x-hidden text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-950" x-data="{}">

    <div class="flex flex-col min-h-screen">

        <header class="h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8 border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 transition-colors duration-300">
            <a href="<?= $baseUrl ?>" class="flex items-center space-x-3">
                <img src="<?= $assetBase ?>images/logo/favicon.png" alt="Gonachi Logo" class="h-9 w-9 flex-shrink-0" />
                <span class="font-bold text-xl tracking-tight text-gray-900 dark:text-white">Gonachi</span>
            </a>

            <div class="flex items-center space-x-3">
                <button
                    @click="$store.theme.isDark = !$store.theme.isDark; document.documentElement.classList.toggle('dark', $store.theme.isDark)"
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
                    <div class="w-8 h-8 rounded-full bg-primary-600 text-white font-bold flex items-center justify-center text-sm shadow-sm">
                        <?= htmlspecialchars(strtoupper(substr($currentUser->full_name ?? 'U', 0, 1))) ?>
                    </div>
                <?php else: ?>
                    <a href="<?= $baseUrl ?>login" data-login-button class="text-sm font-semibold text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                        Sign In
                    </a>
                <?php endif; ?>
            </div>
        </header>

        <main class="flex-1">
            <div id="modal-zone"></div>
            <?php include $pageFile; ?>
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

    <?php if ($isLoggedIn && isset($_SESSION['user_id'])): ?>
        <script>
            window.sessionUserId = <?= json_encode($_SESSION['user_id']) ?>;
        </script>
    <?php endif; ?>

    <script type="module" src="<?= $assetBase ?>assets/js/app.min.js"></script>
</body>

</html>

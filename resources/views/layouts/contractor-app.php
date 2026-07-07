<?php
// /resources/views/layouts/contractor-app.php

declare(strict_types=1);

/**
 * Gonachi Contractor Discovery & Opportunity Engine - Main Application Layout
 * Mirrors layouts/app.php (the Real Estate Leads shell) but with its own
 * sidebar/header — each project keeps its own chrome so a visitor always
 * knows which engine they're in.
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

<body class="font-sans antialiased h-full overflow-x-hidden text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-950"
    x-data="{ mobileMenuOpen: false }">

    <?php include __DIR__ . '/../partials/contractor-sidebar.php'; ?>

    <div
        class="flex flex-col min-h-screen bg-gray-50 dark:bg-gray-950 transition-all duration-300 ease-in-out"
        :class="$store.sidebar.expanded ? 'pl-64' : 'pl-24'">

        <?php include __DIR__ . '/../partials/contractor-header.php'; ?>

        <main class="flex-1 py-10 px-4 sm:px-6 lg:px-8 bg-gray-50 dark:bg-gray-950 transition-colors duration-300">
            <div id="modal-zone"></div>

            <div id="main-content" class="max-w-7xl mx-auto space-y-8">

                <div class="w-full">
                    <?php include $pageFile; ?>
                </div>

                <div id="gonachi-ad-feed" class="w-full mx-auto mt-6 hidden sm:block">
                    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 transition-colors duration-300 overflow-hidden relative">
                        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-secondary-500 via-primary-400 to-secondary-500"></div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold tracking-wider text-secondary-600 dark:text-secondary-400 bg-secondary-50 dark:bg-secondary-950/40 px-2 py-0.5 rounded-full uppercase">
                                Sponsored Advertisement
                            </span>
                            <span class="text-xs text-gray-400 dark:text-gray-600 hover:text-secondary-600 dark:hover:text-secondary-400 hover:underline cursor-pointer transition-colors">Report Ad</span>
                        </div>
                        <div class="w-full min-h-[90px] flex items-center justify-center bg-gradient-to-br from-secondary-50/60 via-gray-50 to-primary-50/60 dark:from-secondary-950/20 dark:via-zinc-950 dark:to-primary-950/20 rounded-lg border border-dashed border-secondary-200 dark:border-gray-800 overflow-hidden">
                            <div id="ad-placement-slot-1"></div>
                        </div>
                    </div>
                </div>

            </div>
        </main>

        <?php include __DIR__ . '/../partials/image-preview-modal.php'; ?>

        <footer class="mt-auto border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 py-6 transition-colors duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center space-x-2">
                    <span class="text-sm font-bold tracking-tight text-gray-900 dark:text-white">Gonachi</span>
                    <span class="text-xs text-gray-400 dark:text-gray-500">&copy; <?= date('Y') ?> Contractor Discovery. All rights reserved.</span>
                </div>
                <div class="flex space-x-6 text-xs font-medium text-gray-500 dark:text-gray-400">
                    <a href="#" class="hover:text-secondary-600 transition-colors">Terms of Service</a>
                    <a href="#" class="hover:text-secondary-600 transition-colors">Privacy Policy</a>
                </div>
            </div>
        </footer>
    </div>

    <?php include __DIR__ . '/../components/scroll-top.php'; ?>

    <?php if ($isLoggedIn && isset($_SESSION['user_id'])): ?>
        <script>
            window.sessionUserId = <?= json_encode($_SESSION['user_id']) ?>;
        </script>
    <?php endif; ?>

    <script type="module" src="<?= $assetBase ?>assets/js/app.min.js"></script>
</body>

</html>

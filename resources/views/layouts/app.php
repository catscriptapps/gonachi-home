<?php
// /resources/views/layouts/app.php

declare(strict_types=1);

/** @var string $title */
/** @var string $appName */
/** @var string $assetBase */
/** @var string $baseUrl */
/** @var array<string, mixed>|null $protectedPaths */
/** @var object|null $currentUser */
/** @var bool $isLoggedIn */

// Setup mock content data cleanly inline since it replaces $pageFile for a complete file preview
$currentCategory = $currentCategory ?? 'Home Buyers in Lagos';
$leadCount = $leadCount ?? 20;
$leads = $leads ?? [
    [
        'id' => 1,
        'type' => 'Buyer',
        'looking_for' => '4 Bedroom Detached House',
        'location' => 'Lekki, Lagos',
        'posted_time' => '11 Hours Ago',
        'status' => 'Active',
        'intent' => 'High',
        'budget' => '₦85,000,000'
    ],
    [
        'id' => 2,
        'type' => 'Buyer',
        'looking_for' => 'Commercial Office Space',
        'location' => 'Ikoyi, Lagos',
        'posted_time' => '1 Day Ago',
        'status' => 'Active',
        'intent' => 'High',
        'budget' => '₦12,000,000/annum'
    ],
    [
        'id' => 3,
        'type' => 'Seller',
        'looking_for' => '2 Duplexes Ready for Market',
        'location' => 'Ikeja, Lagos',
        'posted_time' => '2 Days Ago',
        'status' => 'Active',
        'intent' => 'Medium',
        'budget' => 'Market Value'
    ]
];
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth overflow-x-clip"
    x-data="{ darkMode: document.documentElement.classList.contains('dark') }"
    @dark-toggle.window="darkMode = document.documentElement.classList.contains('dark')"
    :class="{ 'dark': darkMode }">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0" />
    <title><?= htmlspecialchars($title) . ' | ' . htmlspecialchars($appName); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="shortcut icon" type="image/png" href="<?= $assetBase ?>images/logo/favicon.png">
    <link rel="icon" type="image/png" href="<?= $assetBase ?>images/logo/favicon.png">

    <script>
        window.APP_CONFIG = {
            baseUrl: <?= json_encode($baseUrl) ?>,
            assetBase: <?= json_encode($assetBase) ?>,
            appName: <?= json_encode($appName) ?>,
            protectedPaths: <?= json_encode($protectedPaths ?? []) ?>,
            isLoggedIn: <?= json_encode($isLoggedIn) ?>,
            mediaLimit: <?= getMediaLimit() ?>,
            userDefaults: {
                country_id: <?= json_encode($currentUser->country_id ?? null) ?>,
                region_id: <?= json_encode($currentUser->region_id ?? null) ?>
            }
        };
    </script>

    <link rel="stylesheet" href="<?= $assetBase ?>assets/css/app.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        html {
            scrollbar-width: thin;
            scrollbar-color: rgba(156, 163, 175, 0.3) transparent;
        }

        html::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        html::-webkit-scrollbar-track {
            background: transparent;
        }

        html::-webkit-scrollbar-thumb {
            background-color: rgba(156, 163, 175, 0.3);
            border-radius: 9999px;
            transition: background-color 0.2s ease;
        }

        html::-webkit-scrollbar-thumb:hover {
            background-color: rgba(156, 163, 175, 0.5);
        }

        .dark html {
            scrollbar-color: rgba(75, 85, 99, 0.3) transparent;
        }

        .dark html::-webkit-scrollbar-thumb {
            background-color: rgba(75, 85, 99, 0.3);
        }

        .dark html::-webkit-scrollbar-thumb:hover {
            background-color: rgba(75, 85, 99, 0.5);
        }

        [x-cloak] {
            display: none !important;
        }

        @keyframes customKenBurns {
            0% {
                transform: scale(1) rotate(0.02deg);
                opacity: 0;
            }

            10% {
                opacity: 0.40;
            }

            90% {
                opacity: 0.40;
            }

            100% {
                transform: scale(1.12) rotate(0.02deg);
                opacity: 0;
            }
        }

        .animate-slideshow-zoom {
            animation: customKenBurns 8s ease-in-out infinite both;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 font-sans antialiased dark:bg-slate-900 dark:text-gray-100 transition-colors duration-300 overflow-x-clip w-full">

    <header class="bg-white dark:bg-slate-800 border-b border-gray-100 dark:border-slate-700/50 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="<?= $baseUrl ?>" class="flex items-center">
                    <div class="h-12 w-12 rounded-full overflow-hidden border-2 border-primary-400 p-1 bg-white dark:bg-gray-800 shadow-sm">
                        <img class="h-full w-full rounded-full object-cover block dark:hidden" src="<?= $assetBase ?>images/logo/logo-compact-light.png" alt="Logo">
                        <img class="h-full w-full rounded-full object-cover hidden dark:block" src="<?= $assetBase ?>images/logo/logo-compact-dark.png" alt="Logo">
                    </div>
                </a>
                <span class="text-xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center">
                    Gonachi <span class="text-secondary-400 dark:text-primary-400 font-medium text-sm px-2 py-0.5 rounded-md bg-secondary-50 dark:bg-primary-950/40 border border-secondary-200/50 dark:border-primary-900/30 ml-1.5">Lead Engine</span>
                </span>
            </div>
            <div class="flex items-center gap-4">
                <button @click="document.documentElement.classList.toggle('dark'); $dispatch('dark-toggle')" class="p-2 rounded-lg bg-gray-50 dark:bg-slate-700 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition">
                    <i class="fa-solid fa-moon dark:hidden"></i>
                    <i class="fa-solid fa-sun hidden dark:block"></i>
                </button>
                <a href="/login" class="text-sm font-semibold text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 transition">Sign In</a>
                <a href="/register" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold text-sm rounded-xl shadow-sm transition">Free Trial</a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ activeModal: false, selectedLead: null }">
        <div id="modal-zone"></div>
        <div id="main-content">

            <div class="py-8">
                <div class="mb-8 border-b border-gray-200 dark:border-slate-800 pb-5">
                    <nav class="flex mb-3 text-sm text-gray-500 dark:text-gray-400 space-x-2">
                        <a href="#" class="hover:text-primary-600 dark:hover:text-primary-400 transition">Leads</a>
                        <span>/</span>
                        <span class="text-gray-800 dark:text-gray-200 font-medium"><?= htmlspecialchars($currentCategory) ?></span>
                    </nav>
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                                <?= htmlspecialchars($currentCategory) ?>
                            </h1>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                Discover verified live market intent signals updated in real-time.
                            </p>
                        </div>
                        <div class="bg-secondary-50 dark:bg-secondary-950/40 px-4 py-2.5 rounded-xl border border-secondary-200/60 dark:border-secondary-900/50 self-start md:self-auto">
                            <span class="text-sm font-semibold text-secondary-700 dark:text-secondary-300">
                                <i class="fa-solid fa-bolt text-primary-500 mr-1.5 animate-pulse"></i>
                                <?= $leadCount ?> Active Live Signals Found
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                    <aside class="lg:col-span-1 space-y-6">
                        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-gray-100 dark:border-slate-700/50 shadow-sm">
                            <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4 flex items-center justify-between">
                                <span>Filter By Location</span>
                                <i class="fa-solid fa-sliders text-gray-400 text-xs"></i>
                            </h2>
                            <div class="space-y-2">
                                <a href="#" class="flex items-center justify-between px-3 py-2 rounded-lg bg-primary-50 dark:bg-secondary-700 text-primary-800 dark:text-primary-300 text-sm font-medium">
                                    <span>Lagos</span>
                                    <span class="text-xs bg-primary-200/60 dark:bg-secondary-600 px-2 py-0.5 rounded-md font-bold text-primary-900 dark:text-white"><?= $leadCount ?></span>
                                </a>
                                <a href="#" class="flex items-center justify-between px-3 py-2 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-slate-700/50 text-sm transition">
                                    <span>Abuja</span>
                                    <span class="text-xs bg-gray-100 dark:bg-slate-700 px-2 py-0.5 rounded-md text-gray-500">14</span>
                                </a>
                                <a href="#" class="flex items-center justify-between px-3 py-2 rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-slate-700/50 text-sm transition">
                                    <span>Port Harcourt</span>
                                    <span class="text-xs bg-gray-100 dark:bg-slate-700 px-2 py-0.5 rounded-md text-gray-500">8</span>
                                </a>
                            </div>
                        </div>
                    </aside>

                    <section class="lg:col-span-3 space-y-4">
                        <?php foreach ($leads as $lead): ?>
                            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700/50 shadow-sm hover:shadow-md transition-all duration-200 p-6 flex flex-col justify-between gap-4 relative overflow-hidden group">
                                <div class="absolute top-0 left-0 w-1.5 h-full bg-primary-500"></div>

                                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2 mb-2.5">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold uppercase tracking-wider bg-secondary-100 text-secondary-800 dark:bg-secondary-950/60 dark:text-secondary-300">
                                                <?= htmlspecialchars($lead['type']) ?>
                                            </span>
                                            <span class="text-xs text-gray-400 dark:text-gray-500">
                                                <i class="fa-regular fa-clock mr-1"></i><?= htmlspecialchars($lead['posted_time']) ?>
                                            </span>
                                        </div>
                                        <h3 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight group-hover:text-primary-600 dark:group-hover:text-primary-400 transition">
                                            <?= htmlspecialchars($lead['looking_for']) ?>
                                        </h3>
                                        <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-600 dark:text-gray-400">
                                            <span><i class="fa-solid fa-location-dot text-primary-500 mr-1.5"></i><?= htmlspecialchars($lead['location']) ?></span>
                                            <span><i class="fa-solid fa-wallet text-gray-400 mr-1.5"></i><?= htmlspecialchars($lead['budget']) ?></span>
                                        </div>
                                    </div>

                                    <div class="sm:text-right self-start sm:self-auto">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary-50 dark:bg-primary-950/30 text-primary-700 dark:text-primary-400 border border-primary-100 dark:border-primary-900/50">
                                            Intent: <?= htmlspecialchars($lead['intent']) ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="border-t border-gray-100 dark:border-slate-700/60 pt-4 mt-1 flex flex-col sm:flex-row items-center justify-between gap-4">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 italic">
                                        Public contact details hidden behind access tier rules.
                                    </p>
                                    <button
                                        @click="selectedLead = <?= htmlspecialchars(json_encode($lead)) ?>; activeModal = true"
                                        class="w-full sm:w-auto px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-semibold text-sm rounded-xl shadow-sm hover:shadow transition focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                                        View Full Details
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </section>
                </div>

                <template x-if="activeModal">
                    <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
                        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="activeModal = false"></div>

                        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                            <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 p-6 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100 dark:border-slate-800"
                                x-transition:enter="ease-out duration-300"
                                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">

                                <div class="absolute right-4 top-4">
                                    <button @click="activeModal = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                                        <i class="fa-solid fa-xmark text-lg"></i>
                                    </button>
                                </div>

                                <div class="text-center sm:text-left mt-2">
                                    <div class="mx-auto sm:mx-0 flex h-12 w-12 items-center justify-center rounded-xl bg-primary-50 dark:bg-primary-950/50 border border-primary-100 dark:border-primary-900/50 text-primary-600 dark:text-primary-400 mb-4">
                                        <i class="fa-solid fa-lock text-xl"></i>
                                    </div>
                                    <h3 class="text-xl font-bold leading-6 text-gray-900 dark:text-white">
                                        Unlock Full Lead Insights
                                    </h3>
                                    <div class="mt-3">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            Get instantaneous access to structural verified items, original source references, and direct contact avenues for this record.
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-6 space-y-3">
                                    <a href="/register" class="flex w-full justify-center items-center px-4 py-3 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-xl transition text-sm shadow-sm">
                                        Start Free Trial
                                    </a>
                                    <a href="/login" class="flex w-full justify-center items-center px-4 py-3 bg-gray-50 dark:bg-slate-800 hover:bg-gray-100 dark:hover:bg-slate-700/80 text-gray-800 dark:text-white font-semibold rounded-xl transition text-sm border border-gray-200 dark:border-slate-700">
                                        Sign In to Account
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

        </div>
    </main>

    <footer class="bg-white dark:bg-slate-800 border-t border-gray-100 dark:border-slate-700/50 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col sm:flex-row items-center justify-between text-sm text-gray-500 dark:text-gray-400 gap-4">
            <div>
                &copy; 2026 Gonachi Real Estate Lead Engine. All rights reserved.
            </div>
            <div class="flex gap-6">
                <a href="#" class="hover:text-primary-600 dark:hover:text-primary-400 transition">Privacy Policy</a>
                <a href="#" class="hover:text-primary-600 dark:hover:text-primary-400 transition">Terms of Service</a>
                <a href="#" class="hover:text-primary-600 dark:hover:text-primary-400 transition">Support Desk</a>
            </div>
        </div>
    </footer>

    <?php if (($isLoggedIn ?? false) && isset($_SESSION['user_id'])): ?>
        <script>
            window.sessionUserId = <?= json_encode($_SESSION['user_id']) ?>;
        </script>
    <?php endif; ?>
</body>

</html>
<?php
// /resources/views/partials/layout-header-nav.php

declare(strict_types=1);

/** @var bool $isLoggedIn */
/** @var string $baseUrl */
/** @var string $assetBase */
/** @var object|null $currentUser */

use Src\Config\NavigationConfig;
use Src\Service\AuthService;

// Conditionally load navigation links based on the authenticated entity type.
if (AuthService::currentLandlord()) {
    // If a landlord is logged in, show their specific workspace links.
    $navLinks = NavigationConfig::getLandlordWorkspaceLinks();

    // Explicitly inject the core Dashboard link to the front of their link collection
    $navLinks = array_merge([
        'Dashboard' => [
            'url'     => $baseUrl . 'dashboard',
            'title'   => 'Landlord Dashboard',
            'summary' => 'Access your workspace analytics, service states, and properties portfolio.'
        ]
    ], $navLinks);
} else {
    // Otherwise, show the standard backend or public navigation links.
    $navLinks = NavigationConfig::getNavLinks($isLoggedIn);
}

$currentUrlTrimmed = rtrim((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", '/');
?>

<header class="relative z-50 bg-transparent w-full">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-24 flex justify-between items-center">

        <div class="flex items-center shrink-0 py-2">
            <a href="<?= $baseUrl ?>" class="flex items-center transition-opacity hover:opacity-90 focus:outline-none focus:ring-4 focus:ring-amber-500 rounded-xl p-1">
                <img src="<?= $assetBase ?>images/logo/logo.png" alt="Logo" class="h-16 sm:h-20 w-auto object-contain">
            </a>
        </div>

        <nav class="hidden lg:flex items-center space-x-6 xl:space-x-8 text-base xl:text-lg font-extrabold text-white h-full">
            <?php foreach ($navLinks as $name => $config): ?>
                <?php
                // Detect if this element represents the Home link
                $isHomeItem = (strtolower($name) === 'home' || rtrim($config['url'], '/') === rtrim($baseUrl, '/'));
                $targetUrl = $isHomeItem ? $baseUrl : $config['url'];
                ?>

                <?php if (isset($config['children'])): ?>
                    <div class="relative group flex items-center h-full cursor-pointer">
                        <?php
                        $isActive = ($currentUrlTrimmed === rtrim($targetUrl, '/'));
                        $desktopClasses = $isActive
                            ? "text-amber-400 group-hover:text-amber-300 transition-colors flex items-center gap-2 drop-shadow-[0_2px_4px_rgba(0,0,0,0.8)] focus:outline-none focus:underline"
                            : "text-white hover:text-amber-300 transition-colors flex items-center gap-2 drop-shadow-[0_2px_4px_rgba(0,0,0,0.8)] focus:outline-none focus:underline";
                        ?>
                        <a href="<?= $targetUrl ?>" data-partial data-title="<?= htmlspecialchars($config['title']) ?>" data-summary="<?= htmlspecialchars($config['summary']) ?>" class="<?= $desktopClasses ?>">
                            <span><?= $name ?></span>
                            <svg class="w-4 h-4 transform group-hover:rotate-180 transition-transform duration-200 text-white group-hover:text-amber-300 stroke-[3]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </a>

                        <div class="absolute top-[calc(100%-8px)] left-0 min-w-[240px] bg-slate-950 dark:bg-black border-2 border-slate-800 dark:border-slate-900 rounded-xl shadow-2xl py-3 opacity-0 scale-95 pointer-events-none group-hover:opacity-100 group-hover:scale-100 group-hover:pointer-events-auto transition-all duration-150 z-50">
                            <?php foreach ($config['children'] as $childName => $childConfig): ?>
                                <?php $isChildActive = ($currentUrlTrimmed === rtrim($childConfig['url'], '/')); ?>
                                <a href="<?= $childConfig['url'] ?>" data-partial data-title="<?= htmlspecialchars($childConfig['title']) ?>" data-summary="<?= htmlspecialchars($childConfig['summary']) ?>"
                                    class="block px-5 py-3 text-sm font-bold tracking-wide transition-colors <?= $isChildActive ? 'text-amber-400 bg-slate-900' : 'text-slate-200 hover:bg-slate-900 hover:text-amber-300' ?>">
                                    <?= $childName ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <?php
                    $isActive = ($currentUrlTrimmed === rtrim($targetUrl, '/'));
                    $desktopClasses = $isActive
                        ? "text-amber-400 hover:text-amber-300 transition-colors py-2 drop-shadow-[0_2px_4px_rgba(0,0,0,0.8)] focus:outline-none focus:underline"
                        : "text-white hover:text-amber-300 transition-colors py-2 drop-shadow-[0_2px_4px_rgba(0,0,0,0.8)] focus:outline-none focus:underline";
                    ?>
                    <a href="<?= $targetUrl ?>" data-partial data-title="<?= htmlspecialchars($config['title']) ?>" data-summary="<?= htmlspecialchars($config['summary']) ?>" class="<?= $desktopClasses ?>"><?= $name ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>

        <div class="flex items-center lg:hidden">
            <button type="button"
                @click="mobileMenuOpen = !mobileMenuOpen"
                aria-label="Toggle Navigation Menu"
                class="text-white hover:text-amber-300 focus:outline-none p-3 rounded-xl bg-black/30 hover:bg-black/50 transition-colors drop-shadow-md border border-white/10">
                <svg class="h-8 w-8 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-show="!mobileMenuOpen">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg class="h-8 w-8 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-show="mobileMenuOpen" x-cloak">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <div x-show="mobileMenuOpen" x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="lg:hidden border-t-2 border-slate-200 dark:border-slate-800 bg-white dark:bg-black px-4 py-5 space-y-3 shadow-2xl max-h-[calc(100vh-96px)] overflow-y-auto"
        x-data="{ activeMobileSection: null }">

        <?php foreach ($navLinks as $name => $config): ?>
            <?php
            $isHomeItem = (strtolower($name) === 'home' || rtrim($config['url'], '/') === rtrim($baseUrl, '/'));
            $targetUrl = $isHomeItem ? $baseUrl : $config['url'];
            ?>

            <?php if (isset($config['children'])): ?>
                <?php $slug = md5($name); ?>
                <div class="space-y-1.5">
                    <button @click="activeMobileSection = (activeMobileSection === '<?= $slug ?>' ? null : '<?= $slug ?>')"
                        class="w-full flex justify-between items-center px-4 py-3 rounded-xl text-slate-800 dark:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-900 font-bold text-base transition-colors text-left border border-transparent hover:border-slate-200 dark:hover:border-slate-800">
                        <span><?= $name ?></span>
                        <svg class="w-5 h-5 transform transition-transform duration-200 text-slate-500 dark:text-slate-400 stroke-[3]"
                            :class="activeMobileSection === '<?= $slug ?>' ? 'rotate-180 text-primary-600 dark:text-amber-400' : ''"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="activeMobileSection === '<?= $slug ?>'" x-cloak
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="pl-4 border-l-4 border-slate-300 dark:border-slate-700 space-y-2 ml-4">
                        <?php foreach ($config['children'] as $childName => $childConfig): ?>
                            <?php $isChildActive = ($currentUrlTrimmed === rtrim($childConfig['url'], '/')); ?>
                            <a href="<?= $childConfig['url'] ?>" data-partial data-title="<?= htmlspecialchars($childConfig['title']) ?>" data-summary="<?= htmlspecialchars($childConfig['summary']) ?>" @click="mobileMenuOpen = false"
                                class="block px-4 py-3 rounded-lg text-sm <?= $isChildActive ? 'text-primary-600 dark:text-amber-400 font-black bg-primary-50/50 dark:bg-amber-400/10' : 'text-slate-600 dark:text-slate-300 font-bold hover:text-primary-600 dark:hover:text-amber-400' ?> transition-colors">
                                <?= $childName ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <?php
                $isActive = ($currentUrlTrimmed === rtrim($targetUrl, '/'));
                $mobileClasses = $isActive
                    ? "block px-4 py-3 rounded-xl bg-primary-50 dark:bg-amber-400/10 text-primary-600 dark:text-amber-400 font-black text-base border-2 border-primary-200 dark:border-amber-400/30"
                    : "block px-4 py-3 rounded-xl text-slate-800 dark:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-900 font-bold text-base transition-colors hover:text-primary-600 dark:hover:text-amber-400 border border-transparent hover:border-slate-200 dark:hover:border-slate-800";
                ?>
                <a href="<?= $targetUrl ?>" data-partial data-title="<?= htmlspecialchars($config['title']) ?>" data-summary="<?= htmlspecialchars($config['summary']) ?>" @click="mobileMenuOpen = false" class="<?= $mobileClasses ?>"><?= $name ?></a>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if ($isLoggedIn && $currentUser): ?>
            <div class="pt-5 border-t-2 border-slate-100 dark:border-slate-900 flex items-center gap-4 px-4">
                <div class="h-10 w-10 rounded-full bg-primary-500/10 border-2 border-primary-500 text-primary-600 dark:text-amber-400 flex items-center justify-center font-black text-sm uppercase">
                    <?= substr($currentUser->name ?? 'U', 0, 1) ?>
                </div>
                <div>
                    <p class="text-sm font-black text-slate-900 dark:text-slate-100"><?= htmlspecialchars($currentUser->name ?? 'User') ?></p>
                    <p class="text-xs font-bold text-slate-400">Authorized Profile</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</header>
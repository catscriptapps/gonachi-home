<?php
// /resources/views/partials/layout-header.php

declare(strict_types=1);

use Src\Config\NavigationConfig;
use Src\Service\AuthService;

/** @var bool $isLoggedIn */
/** @var string $assetBase */
/** @var string $appName */
/** @var string $baseUrl */

// 1. Add or remove your slider pictures cleanly here:
$slideshowImages = [
    '1.jpg',
    '18.jpg',
    '12.webp',
    '13.webp',
    '14.webp',
    '15.webp',
    '16.webp',
    '4.jpg',
    '5.jpg',
    '8.jpg',
    '11.jpg',
    '17.jpg',
    '19.webp',
    '20.jpg',
];

$totalSlides = count($slideshowImages);

// Detect initial load context for server rendering state
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$initialIsHome = ($currentPath === '/' || $currentPath === '/index.php');

// --- Universal Title & Summary Injection for Hard Refreshes ---
$initialPageTitle = '';
$initialPageSummary = '';

if ($isLoggedIn) {
    if (AuthService::currentLandlord()) {
        // Set default title/summary for a logged-in Landlord
        $initialPageTitle = 'Landlord Dashboard';
        $initialPageSummary = 'Manage your property portfolio, view statements, and access subscribed services.';
    } else {
        // Set default title/summary for a logged-in backend User
        $initialPageTitle = 'Operational Dashboard';
        $initialPageSummary = 'Real-time performance index metrics, pending verification alerts, and systemic portfolio analysis logs.';
    }
}

$normalizedBasePath = rtrim($baseUrl, '/');
$normalizedCurrentPath = rtrim($currentPath, '/');

// Get all possible navigation links (for both users and landlords)
$allNavLinks = array_merge(
    NavigationConfig::getNavLinks(true),
    NavigationConfig::getLandlordWorkspaceLinks()
);

// Find the matching link for the current path
foreach ($allNavLinks as $config) {
    $normalizedLinkUrl = rtrim($config['url'], '/');
    if ($normalizedCurrentPath === $normalizedLinkUrl) {
        $initialPageTitle = $config['title'] ?? '';
        $initialPageSummary = $config['summary'] ?? '';
        break;
    }
}

// Dynamic detail-route pages (e.g., the token-based tenant portal) can't be
// matched by NavigationConfig above, since their URL differs per instance.
// resolvePageRoute() stashes a summary (and resolves $title dynamically)
// for these before this file is included — respect it here if present.
if (!empty($GLOBALS['pageSummary'])) {
    $initialPageTitle = $title ?? $initialPageTitle;
    $initialPageSummary = $GLOBALS['pageSummary'];
}
?>

<div class="w-full relative bg-gray-900 dark:bg-black transition-all duration-500 font-sans pt-[50px] sm:pt-[52px]"
    :class="(isHome && !isLoggedIn) ? 'min-h-[600px]' : 'min-h-[220px] sm:min-h-[240px]'"
    x-data="{ 
        activeSlide: 1,
        slidesCount: <?= $totalSlides ?>,
        mobileMenuOpen: false,
        isHome: <?= $initialIsHome ? 'true' : 'false' ?>,
        isLoggedIn: <?= $isLoggedIn ? 'true' : 'false' ?>,
        pageTitle: '<?= addslashes($initialPageTitle) ?>',
        pageSummary: '<?= addslashes($initialPageSummary) ?>',
        init() {
            setInterval(() => {
                this.activeSlide = this.activeSlide === this.slidesCount ? 1 : this.activeSlide + 1;
            }, 8000);
        }
    }"
    @spa-navigation.window="
        isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>; // Re-evaluate on navigation
        isHome = $event.detail.isHome;
        pageTitle = $event.detail.title || '';
        pageSummary = $event.detail.summary || '';
    ">

    <div class="absolute inset-0 z-0 overflow-hidden">
        <?php foreach ($slideshowImages as $index => $imageName): ?>
            <?php $slideNumber = $index + 1; ?>
            <div
                x-show="activeSlide === <?= $slideNumber ?>"
                <?php if ($slideNumber > 1): ?>x-cloak<?php endif; ?>
                x-transition:enter="transition ease-in-out duration-1000"
                class="absolute inset-0 bg-cover bg-center animate-slideshow-zoom"
                style="background-image: url('<?= $assetBase ?>images/home/<?= $imageName ?>');">
            </div>
        <?php endforeach; ?>

        <div class="absolute inset-0 bg-gradient-to-b from-slate-900/40 via-slate-900/25 to-secondary-950/50 dark:from-black/50 dark:via-black/35 dark:to-black/60"></div>
    </div>

    <div class="relative z-10 w-full flex flex-col flex-1">

        <?php include __DIR__ . '/layout-header-nav.php'; ?>

        <section x-show="isHome && !isLoggedIn" x-collapse.duration.500ms class="flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-8 py-16 sm:py-24">
            <div class="max-w-4xl mx-auto text-center">
                <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-semibold uppercase tracking-widest bg-primary-500/20 text-primary-300 border border-primary-500/30 backdrop-blur-sm mb-6 drop-shadow-sm"
                    data-aos="fade-down"
                    data-aos-duration="600">
                    <i class="fa-solid fa-circle-nodes text-[10px] text-primary-400"></i> <?= htmlspecialchars($appName) ?> Secure Ecosystem
                </span>

                <h1 class="text-4xl sm:text-6xl font-extrabold text-white tracking-tight leading-tight drop-shadow-md mb-6 uppercase"
                    data-aos="fade-up"
                    data-aos-duration="800"
                    data-aos-delay="100">
                    Centralized Landlord <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-400 to-indigo-300">Infrastructure</span>
                </h1>

                <p class="max-w-2xl mx-auto text-base sm:text-lg text-slate-100 drop-shadow mb-10 font-medium"
                    data-aos="fade-up"
                    data-aos-duration="800"
                    data-aos-delay="200">
                    Automate compliance, optimize overhead screening, and manage portfolios flawlessly through our synchronized core architecture.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4"
                    data-aos="fade-up"
                    data-aos-duration="800"
                    data-aos-delay="300">
                    <a href="<?= $baseUrl ?>get-started"
                        data-partial
                        data-title="Primary Engine Deployment"
                        data-summary="Initialize onboarding provisioning node topology allocation sequence"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-gradient-to-r from-primary-600 to-indigo-600 hover:from-primary-500 hover:to-indigo-500 text-white font-bold px-8 py-4 rounded-xl shadow-xl shadow-primary-950/50 hover:shadow-primary-600/20 transition-all transform hover:-translate-y-0.5 text-base sm:text-lg">
                        Get Started Now <i class="fa-solid fa-arrow-right text-sm"></i>
                    </a>
                    <a href="#features"
                        data-title="System Infrastructure Briefing"
                        data-summary="Render automated audiovisual asset optimization overview"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-white/10 hover:bg-white/15 text-white font-semibold px-8 py-4 rounded-xl border border-white/20 backdrop-blur-md transition-all transform hover:-translate-y-0.5 text-base sm:text-lg">
                        <i class="fa-solid fa-circle-play text-secondary-300"></i> Watch Platform Demo
                    </a>
                </div>
            </div>
        </section>

        <section x-show="!isHome || isLoggedIn" x-cloak class="flex-1 flex items-center px-4 sm:px-6 lg:px-8 py-3 sm:py-5">
            <div class="max-w-7xl mx-auto w-full">
                <h1 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight uppercase mb-1 drop-shadow-md" x-text="pageTitle"></h1>
                <p class="text-xs text-slate-200/90 max-w-2xl font-normal drop-shadow-sm leading-normal" x-text="pageSummary"></p>
            </div>
        </section>

    </div>
</div>
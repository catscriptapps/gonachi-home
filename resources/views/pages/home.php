<?php
// /resources/views/pages/home.php

declare(strict_types=1);

/**
 * Gonachi Portal — landing hub for every project under gonachi-home.
 * Each tab below is its own project with its own layout, database
 * tables, and (eventually) its own deployment; this page is just the
 * front door that routes a visitor to one.
 */

$projects = [
    [
        'name' => 'Real Estate Leads',
        'tagline' => 'Find people who are actively looking to buy or sell property.',
        'href' => $baseUrl . 'real-estate-leads',
        'status' => 'live',
        'accent' => 'primary',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />',
    ],
    [
        'name' => 'Contractor Discovery',
        'tagline' => 'The largest searchable contractor database and job marketplace in Nigeria.',
        'href' => $baseUrl . 'contractor-discovery',
        'status' => 'live',
        'accent' => 'secondary',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />',
    ],
    [
        'name' => 'Landlord & Tenant Validation',
        'tagline' => 'A searchable database of landlord and tenant records — a credit bureau, but for renting.',
        'href' => $baseUrl . 'landlord-tenant-validation',
        'status' => 'live',
        'accent' => 'indigo',
        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />',
    ],
];

$accentClasses = [
    'primary' => [
        'bar' => 'from-primary-500 to-primary-400',
        'icon' => 'bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 group-hover:bg-primary-500 group-hover:text-white',
        'badgeLive' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400',
    ],
    'secondary' => [
        'bar' => 'from-secondary-500 to-secondary-400',
        'icon' => 'bg-secondary-50 dark:bg-secondary-950/40 text-secondary-600 dark:text-secondary-400 group-hover:bg-secondary-500 group-hover:text-white',
        'badgeLive' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400',
    ],
    'indigo' => [
        'bar' => 'from-indigo-500 to-indigo-400',
        'icon' => 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 group-hover:bg-indigo-500 group-hover:text-white',
        'badgeLive' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400',
    ],
];

// --------------------------------------------------
// Hero slideshow: every image dropped into public/images/home/ is picked
// up automatically, no code change needed to add/remove one.
// --------------------------------------------------
$heroImages = [];
$heroImagesPath = __DIR__ . '/../../../public/images/home';

if (is_dir($heroImagesPath)) {
    $files = scandir($heroImagesPath) ?: [];
    $files = array_values(array_filter($files, fn($f) => preg_match('/\.(jpe?g|png|webp|gif)$/i', $f)));
    sort($files);

    foreach ($files as $file) {
        $heroImages[] = $assetBase . 'images/home/' . rawurlencode($file);
    }
}

$secondsPerSlide = 6;
$slideCount = count($heroImages);
$heroCycleDuration = max($slideCount, 1) * $secondsPerSlide;
$heroSlotPercent = $slideCount > 0 ? 100 / $slideCount : 100;
?>

<?php if ($slideCount > 0): ?>
    <style>
        @keyframes gonachiHeroSlide {
            0% { opacity: 0; transform: scale(1.15); }
            <?= round($heroSlotPercent * 0.08, 4) ?>% { opacity: 1; transform: scale(1.15); }
            <?= round($heroSlotPercent * 0.85, 4) ?>% { opacity: 1; transform: scale(1); }
            <?= round($heroSlotPercent, 4) ?>% { opacity: 0; transform: scale(1); }
            100% { opacity: 0; transform: scale(1); }
        }
        .gonachi-hero-slide {
            animation: gonachiHeroSlide <?= $heroCycleDuration ?>s ease-in-out infinite;
        }
        @media (prefers-reduced-motion: reduce) {
            .gonachi-hero-slide { animation: none; opacity: 1; }
        }
    </style>
<?php endif; ?>

<section class="relative overflow-hidden">
    <?php if ($slideCount > 0): ?>
        <div class="absolute inset-0 blur-[2px] scale-105" aria-hidden="true">
            <?php foreach ($heroImages as $i => $src): ?>
                <div class="gonachi-hero-slide absolute inset-0 bg-cover bg-center"
                    style="background-image: url('<?= htmlspecialchars($src) ?>'); animation-delay: -<?= $i * $secondsPerSlide ?>s;"></div>
            <?php endforeach; ?>
        </div>
        <div class="absolute inset-0 bg-gray-50/90 dark:bg-gray-950/90"></div>
    <?php endif; ?>

    <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 sm:pt-28 pb-32 sm:pb-44">
        <div class="text-center max-w-2xl mx-auto">
            <span class="inline-block text-xs font-semibold tracking-[0.2em] text-primary-600 dark:text-primary-400 uppercase mb-4">Gonachi</span>
            <h1 class="text-4xl sm:text-5xl font-bold tracking-tight text-gray-900 dark:text-white">
                One platform. Three engines built to find opportunity.
            </h1>
            <p class="mt-4 text-base text-gray-500 dark:text-gray-400">
                Choose a project to get started.
            </p>
        </div>
    </div>
</section>

<div class="relative -mt-20 sm:-mt-28 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-20 sm:pb-28">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8">
        <?php foreach ($projects as $project): ?>
            <?php $accent = $accentClasses[$project['accent']]; ?>
            <a href="<?= htmlspecialchars($project['href']) ?>"
                class="group relative flex flex-col bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-3xl p-8 shadow-sm overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r <?= $accent['bar'] ?>"></div>

                <div class="flex items-center justify-between mb-6">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center transition-colors duration-300 <?= $accent['icon'] ?>">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><?= $project['icon'] ?></svg>
                    </div>

                    <?php if ($project['status'] === 'live'): ?>
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-full <?= $accent['badgeLive'] ?>">
                            <span class="relative flex h-1.5 w-1.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
                            </span>
                            Live
                        </span>
                    <?php else: ?>
                        <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-full bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            Coming Soon
                        </span>
                    <?php endif; ?>
                </div>

                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2"><?= htmlspecialchars($project['name']) ?></h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 flex-1"><?= htmlspecialchars($project['tagline']) ?></p>

                <div class="mt-6 flex items-center text-sm font-semibold text-gray-700 dark:text-gray-300 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                    <?= $project['status'] === 'live' ? 'Enter' : 'Preview' ?>
                    <svg class="h-4 w-4 ml-1.5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Unified Summary: the three projects, tied together in one story -->
<section class="border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 py-20 sm:py-28">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <span class="inline-block text-xs font-semibold tracking-[0.2em] text-primary-600 dark:text-primary-400 uppercase mb-4">The Big Picture</span>
        <h2 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 dark:text-white max-w-3xl mx-auto">
            Three engines. One idea: turn public information into opportunity.
        </h2>
        <p class="mt-4 text-base text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
            Every Gonachi project follows the same playbook — continuously surface signals hiding in plain sight across the public web, structure them into something searchable, and put the right person in front of the right opportunity. Real estate leads for realtors. Verified contractors for property owners. Landlord and tenant records for renters.
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-px bg-gray-200 dark:bg-gray-800 rounded-2xl overflow-hidden mt-12 max-w-4xl mx-auto">
            <?php foreach ($projects as $project): ?>
                <?php $accent = $accentClasses[$project['accent']]; ?>
                <div class="bg-white dark:bg-gray-900 p-6 flex items-start gap-4 text-left">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 <?= $accent['icon'] ?>">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><?= $project['icon'] ?></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($project['name']) ?></h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1"><?= htmlspecialchars($project['tagline']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php
// /resources/views/pages/dashboard.php

declare(strict_types=1);

/** @var bool $isLoggedIn */
/** @var string $appName */
/** @var string $baseUrl */

use Src\Config\NavigationConfig;
use Src\Controller\RecentActivitiesController;
use Src\Service\AuthService;
use App\Models\Landlord;
use App\Models\User;

// --- DUAL DASHBOARD ROUTING LOGIC ---
// Check if the current session belongs to a landlord.
$landlord = AuthService::currentLandlord();
if ($landlord) {
    // If it is a landlord, load their specific dashboard view and stop rendering this one.
    // The 'landlord-dashboard.php' view will have access to the $landlord object.
    $GLOBALS['landlord'] = $landlord;
    require_once __DIR__ . '/landlord-dashboard.php';
    return; // Stop processing the rest of the backend user dashboard.
}

// Fetch data
$navLinks = NavigationConfig::getNavLinks((bool)$isLoggedIn);
$icons = NavigationConfig::getIcons();
$totalLandlords = Landlord::count();
$totalUsers = User::count();

/** @var \App\Models\User|null $currentUser */
$userName = $currentUser->full_name ?? 'there';
$recentActivities = RecentActivitiesController::latest(10);

$getCategoryIcon = function ($category) use ($icons) {
    return match ($category) {
        'Landlords' => $icons['Landlords'] ?? '🏢',
        'Invoices'  => $icons['Invoices'] ?? '📄',
        'Tasks'     => $icons['Tasks'] ?? '✅',
        'Customers' => $icons['Customers'] ?? '👥',
        'Users'     => $icons['Users'] ?? '👤',
        'Chats'     => '💬',
        default     => '🔔',
    };
};
?>

<style>
    @keyframes float {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-10px);
        }
    }

    .animate-float {
        animation: float 4s ease-in-out infinite;
    }

    .glow-primary {
        box-shadow: 0 0 20px rgba(252, 131, 43, 0.2);
    }

    .group:hover .glow-primary-hover {
        box-shadow: 0 0 30px rgba(252, 131, 43, 0.4);
    }
</style>

<title>Dashboard | <?= htmlspecialchars($appName ?? 'PMB') ?></title>

<div class="space-y-10 font-sans pb-10">

    <div class="animate-in fade-in slide-in-from-top-10 duration-1000 fill-mode-both">
        <?php include __DIR__ . '/../components/dashboard/hero-header.php'; ?>
    </div>

    <div class="space-y-4">
        <h2 class="text-xs font-black uppercase tracking-[0.3em] text-gray-400 dark:text-gray-500 px-2 animate-in fade-in delay-500">
            Workspace Hub
        </h2>
        <div class="grid grid-cols-2 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <?php
            $delay = 100;
            // Updated loop structure to correctly extract the sub-configuration attributes
            foreach ($navLinks as $name => $appConfig):
                if (in_array($name, ['Home', 'About', 'Contact', 'Dashboard'])) continue;
                $url = $appConfig['url'] ?? '#';
            ?>
                <a href="<?= $url ?>"
                    data-partial
                    data-title="<?= htmlspecialchars($appConfig['title'] ?? $name) ?>"
                    data-summary="<?= htmlspecialchars($appConfig['summary'] ?? '') ?>"
                    style="animation-delay: <?= $delay ?>ms"
                    class="group animate-in fade-in zoom-in-95 duration-700 fill-mode-both relative overflow-hidden rounded-[2.5rem] bg-primary-100 dark:bg-black p-8 shadow-sm border border-gray-100 dark:border-gray-800 transition-all hover:shadow-[0_20px_60px_-15px_rgba(252,131,43,0.3)] hover:-translate-y-2 hover:border-primary-400/50">

                    <div class="flex items-center justify-between relative z-10">
                        <div class="rounded-2xl bg-primary-50 dark:bg-primary-900/20 p-4 text-primary-600 dark:text-primary-400 group-hover:bg-primary-400 group-hover:text-white transition-all duration-500 group-hover:scale-110 group-hover:rotate-6 glow-primary-hover">
                            <div class="w-6 h-6"><?= $icons[$name] ?? '' ?></div>
                        </div>
                        <div class="p-2 opacity-0 group-hover:opacity-100 group-hover:translate-x-0 -translate-x-4 transition-all duration-300">
                            <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </div>
                    </div>

                    <div class="mt-8 relative z-10">
                        <h3 class="text-xl font-black text-secondary-900 dark:text-white"><?= htmlspecialchars($name) ?></h3>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-primary-500"></span>
                            </span>
                            <p class="text-[10px] text-gray-400 uppercase tracking-widest font-black">Active Module</p>
                        </div>
                    </div>

                    <div class="absolute -bottom-6 -right-6 text-gray-50 dark:text-white/5 opacity-0 group-hover:opacity-100 transition-all duration-700 group-hover:rotate-0 rotate-45">
                        <div class="scale-[4.0]"><?= $icons[$name] ?? '' ?></div>
                    </div>
                </a>
            <?php $delay += 100;
            endforeach; ?>

        </div>
    </div>

    <div class="space-y-5 animate-in fade-in slide-in-from-bottom-10 duration-1000 delay-700 fill-mode-both">
        <div class="flex flex-col sm:flex-row items-start sm:items-end justify-between px-2 gap-4 sm:gap-0">
            <div>
                <h2 class="text-2xl font-black text-secondary-900 dark:text-white tracking-tight flex items-center gap-3">
                    Recent Activity
                    <span class="px-2 py-0.5 rounded-md bg-primary-400 text-[10px] text-white animate-pulse">LIVE</span>
                </h2>
                <p class="text-sm text-gray-500 font-medium italic">Ecosystem feed updated <?= date('H:i') ?></p>
            </div>
            <a href="<?= $baseUrl ?>history" class="group flex items-center gap-2 px-6 py-3 rounded-2xl bg-secondary-900 dark:bg-white/10 text-[10px] font-black text-white dark:text-white uppercase tracking-widest transition-all hover:bg-primary-400 hover:shadow-lg hover:shadow-primary-400/40">
                Full Logs
                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>
        </div>

        <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-[3rem] shadow-2xl shadow-gray-200/50 dark:shadow-none overflow-hidden">
            <div class="divide-y divide-gray-50 dark:divide-gray-800">
                <?php if (!$recentActivities || $recentActivities->isEmpty()): ?>
                    <div class="p-24 text-center">
                        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-50 dark:bg-gray-800 text-gray-300 mb-6 animate-float">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-gray-400 font-black uppercase tracking-tighter">System is currently quiet...</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recentActivities as $activity): ?>
                        <div class="group p-8 hover:bg-primary-50/20 dark:hover:bg-primary-400/5 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                            <div class="flex items-center gap-8">
                                <div class="h-14 w-14 shrink-0 rounded-2xl bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 flex items-center justify-center text-primary-400 group-hover:bg-primary-400 group-hover:text-white transition-all duration-500">
                                    <div class="w-7 h-7 transform group-hover:scale-125 transition-transform"><?= $getCategoryIcon($activity->category ?? 'General') ?></div>
                                </div>
                                <div>
                                    <span class="text-lg font-black text-secondary-900 dark:text-gray-100 group-hover:text-primary-500 transition-colors"><?= htmlspecialchars($activity->action ?? 'Action') ?></span>
                                    <div class="mt-1 flex items-center gap-3 text-[10px] font-black uppercase tracking-widest text-gray-400">
                                        <span class="text-primary-500"><?= htmlspecialchars($activity->category ?? 'General') ?></span>
                                        <span>&bull;</span>
                                        <span><?= htmlspecialchars($activity->user->full_name ?? 'System') ?></span>
                                    </div>
                                </div>
                            </div>
                            <span class="text-[10px] font-black text-gray-400 bg-gray-100 dark:bg-white/5 px-4 py-2 rounded-full border border-transparent group-hover:border-primary-400/30 transition-all">
                                <?= $activity->created_at ? $activity->created_at->diffForHumans() : 'Just now' ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
// /resources/views/pages/landlord-dashboard.php

declare(strict_types=1);

use Src\Config\NavigationConfig;

/**
 * @var \App\Models\Landlord $landlord
 * @var string $baseUrl
 * @var string $appName
 */
$landlord = $GLOBALS['landlord'] ?? null;

if (!$landlord) {
    // Fallback in case the controller didn't run.
    include __DIR__ . '/auth-required.php';
    return;
}

// --- Fetch Landlord-Specific Data ---
$properties = $landlord->properties()->orderBy('property_name')->get();
$services = $landlord->services()->wherePivot('status_id', 1)->get();

$workspaceLinks = NavigationConfig::getLandlordWorkspaceLinks();

?>

<!-- Hero Header -->
<div class="w-screen relative left-1/2 -translate-x-1/2 overflow-hidden bg-primary-100 dark:bg-gray-900/50 py-8 lg:py-12 border-y border-gray-200/60 dark:border-white/5 transition-colors duration-300 mt-6 mb-12 lg:mb-16 shadow-sm">
    <div class="absolute inset-0 bg-gradient-to-br from-primary-500/5 via-transparent to-secondary-500/5 opacity-100"></div>
    <div class="absolute -top-24 -right-24 w-64 h-64 bg-primary-400/10 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-secondary-400/10 rounded-full blur-3xl"></div>

    <div class="w-full max-w-5xl mx-auto px-4 md:px-6 relative flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div class="flex items-center gap-6 w-full md:w-auto">
            <div class="shrink-0 w-16 h-16 md:w-20 md:h-20 rounded-full bg-gradient-to-br from-primary-500 to-secondary-600 flex items-center justify-center text-white text-2xl md:text-3xl font-black shadow-lg">
                <?= strtoupper(substr($landlord->company_name ?? 'L', 0, 1)) ?>
            </div>

            <div class="flex-1">
                <h1 class="text-3xl lg:text-4xl font-black tracking-tight text-gray-900 dark:text-white leading-tight">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-600 to-secondary-500 dark:from-primary-400 dark:to-secondary-400"><?= htmlspecialchars($landlord->company_name) ?></span>
                </h1>

                <div class="mt-3 flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-secondary-500/10 border border-secondary-500/20 text-secondary-600 dark:text-secondary-400 text-[10px] font-black uppercase tracking-widest">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-secondary-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-secondary-500"></span>
                        </span>
                        Secure Portal
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium md:border-l md:border-gray-300 dark:md:border-white/10 md:pl-3">
                        Manage your assets, optimize profiles, and view deployable extensions.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4 self-start md:self-auto">
            <div class="px-5 py-2.5 rounded-2xl bg-white dark:bg-white/5 border border-gray-200/60 dark:border-white/10 shadow-sm text-center min-w-[5rem]">
                <span class="text-2xl font-black text-primary-600 dark:text-primary-400 block leading-none"><?= count($properties) ?></span>
                <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-wider block mt-1">Properties</span>
            </div>
            <div class="px-5 py-2.5 rounded-2xl bg-white dark:bg-white/5 border border-gray-200/60 dark:border-white/10 shadow-sm text-center min-w-[5rem]">
                <span class="text-2xl font-black text-secondary-600 dark:text-secondary-400 block leading-none"><?= count($services) ?></span>
                <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-wider block mt-1">Services</span>
            </div>
        </div>
    </div>
</div>

<div class="space-y-10 font-sans pb-10">
    <!-- Workspace Hub -->
    <div class="space-y-4">
        <h2 class="text-xs font-black uppercase tracking-[0.3em] text-gray-400 dark:text-gray-500 px-2 animate-in fade-in delay-500">
            Workspace Hub
        </h2>
        <div class="grid grid-cols-2 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <?php foreach ($workspaceLinks as $name => $config) : ?>
                <a href="<?= $config['url'] ?>"
                    data-partial
                    data-title="<?= htmlspecialchars($config['title'] ?? $name) ?>"
                    data-summary="<?= htmlspecialchars($config['summary'] ?? '') ?>"
                    class="group animate-in fade-in zoom-in-95 duration-700 fill-mode-both relative overflow-hidden rounded-[2.5rem] bg-primary-100 dark:bg-black p-8 shadow-sm border border-gray-100 dark:border-gray-800 transition-all hover:shadow-xl hover:-translate-y-2 hover:border-primary-400/50">

                    <div class="flex items-center justify-between relative z-10">
                        <div class="rounded-2xl bg-primary-50 dark:bg-primary-900/20 p-4 text-primary-600 dark:text-primary-400 group-hover:bg-primary-500 group-hover:text-white transition-all duration-500 group-hover:scale-110 group-hover:rotate-6">
                            <div class="w-6 h-6"><?= $config['icon'] ?></div>
                        </div>
                    </div>
                    <div class="mt-8 relative z-10">
                        <h3 class="text-xl font-black text-gray-900 dark:text-white"><?= htmlspecialchars($name) ?></h3>
                    </div>
                    <div class="absolute -bottom-6 -right-6 text-gray-50 dark:text-white/5 opacity-0 group-hover:opacity-100 transition-all duration-700 group-hover:rotate-0 rotate-45">
                        <div class="scale-[4.0]"><?= $config['icon'] ?></div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- My Properties List -->
    <div class="space-y-5 animate-in fade-in slide-in-from-bottom-10 duration-1000 delay-700 fill-mode-both">
        <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3 px-2">
            My Property Portfolio
        </h2>
        <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-[3rem] shadow-lg overflow-hidden">
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                <?php if (empty($properties)) : ?>
                    <div class="p-12 text-center">
                        <p class="text-gray-500 font-medium">No properties have been assigned to your portfolio yet.</p>
                    </div>
                <?php else : ?>
                    <?php foreach ($properties as $property) : ?>
                        <div class="group p-6 hover:bg-primary-50/20 dark:hover:bg-primary-900/10 transition-colors flex items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-primary-500">
                                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M12 21V4.5m0 16.5h-3.75m-7.5 0h7.5" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-800 dark:text-white"><?= htmlspecialchars($property->property_name) ?></h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400"><?= htmlspecialchars($property->unit_number . ', ' . $property->address_line1) ?></p>
                                </div>
                            </div>
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase <?= $property->is_active ? 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400' : 'bg-gray-200 dark:bg-gray-700 text-gray-500' ?>">
                                <?= $property->is_active ? 'Active' : 'Inactive' ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
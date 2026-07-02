<?php
// /resources/views/pages/about.php

declare(strict_types=1);

use Src\Config\NavigationConfig;

/** @var string $assetBase */
/** @var string $appName */
/** @var string $baseUrl */

// Pull entirely centralized route mappings for context continuity
$publicLinks = NavigationConfig::getNavLinks(false);
$getStartedUrl = $baseUrl . 'get-started';

// Pull entirely centralized infrastructure matrices
$icons              = NavigationConfig::getIcons();
$servicesLinks      = NavigationConfig::getModuleLinks();
$moduleLabels       = NavigationConfig::getModuleLabels();
$moduleDescriptions = NavigationConfig::getModuleDescriptions();
?>

<!-- Hero Section Matrix -->
<section class="relative overflow-hidden bg-gradient-to-b from-primary-300 via-white to-primary-200 text-slate-800 dark:from-slate-950 dark:via-black dark:to-slate-950 py-20 lg:py-28 px-6 sm:px-12 lg:px-24 xl:px-32 transition-colors duration-300 border-b border-slate-200 dark:border-slate-800 relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen max-w-[100vw]">
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#00000003_1px,transparent_1px),linear-gradient(to_bottom,#00000003_1px,transparent_1px)] dark:bg-[linear-gradient(to_right,#ffffff03_1px,transparent_1px),linear-gradient(to_bottom,#ffffff03_1px,transparent_1px)] bg-[size:32px_32px] pointer-events-none"></div>

    <div class="absolute -top-40 -right-20 w-[500px] h-[500px] bg-primary-500/[0.04] dark:bg-primary-500/[0.07] rounded-full blur-[140px] pointer-events-none"></div>
    <div class="absolute -bottom-40 -left-20 w-[500px] h-[500px] bg-secondary-500/[0.02] dark:bg-secondary-500/[0.04] rounded-full blur-[140px] pointer-events-none"></div>

    <div class="relative z-10 max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

        <div class="flex flex-col space-y-5 lg:col-span-7" data-aos="fade-right" data-aos-duration="800">
            <div class="inline-flex items-center gap-2">
                <span class="h-1.5 w-1.5 rounded-full bg-primary-500 dark:bg-primary-400 animate-pulse"></span>
                <p class="uppercase tracking-[0.25em] text-[10px] font-black text-primary-600 dark:text-primary-400">
                    Ecosystem Architecture
                </p>
            </div>

            <h1 class="text-3xl sm:text-4xl xl:text-5xl font-black text-slate-900 dark:text-white tracking-tight leading-tight uppercase font-sans">
                The Centralized Landlord <br />
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-600 via-primary-500 to-indigo-600 dark:from-primary-400 dark:via-primary-300 dark:to-secondary-400">
                    Utility Pipeline
                </span>
            </h1>

            <p class="text-slate-600 dark:text-slate-400 max-w-xl font-medium leading-relaxed">
                PMB functions as a unified digital infrastructure node engineered to resolve systemic operational friction between residential landlords and compliance-bound tenants. By running modular software subscriptions, we isolate risk layers and process secure verifications at scale.
            </p>

            <div class="pt-4 flex flex-wrap gap-4">
                <a href="<?= $getStartedUrl ?>"
                    data-partial
                    data-title="Get Started | Infrastructure Portal"
                    data-summary="Initialize your system access node as an administrative landlord or validating tenant pipeline."
                    class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white dark:bg-primary-500 dark:hover:bg-primary-600 dark:text-slate-950 font-bold shadow-md shadow-primary-500/10 transition-all duration-300 font-sans tracking-wide uppercase text-xs">
                    Initialize Setup Gateway
                </a>
            </div>
        </div>

        <div class="hidden lg:block lg:col-span-5 bg-slate-100/80 dark:bg-secondary-900/60 border border-slate-200 dark:border-secondary-800 shadow-xl rounded-2xl p-6 backdrop-blur-sm" data-aos="fade-left" data-aos-duration="800" data-aos-delay="150">
            <div class="space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-secondary-800">
                    <span class="text-[11px] font-black text-slate-500 dark:text-secondary-500 uppercase tracking-widest">Ecosystem Matrix</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-200 text-slate-800 dark:bg-primary-950/50 dark:text-primary-400 border border-slate-300 dark:border-primary-900/50">v2.1.0-Stable</span>
                </div>

                <div class="space-y-2">
                    <p class="text-slate-600 dark:text-secondary-400 leading-normal font-medium text-sm sm:text-xs">
                        By integrating deep real-time logs and automated tenant validation frameworks, PMB gives property operators complete sovereign data oversight while accelerating tenant processing speed.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-2">
                    <div class="p-3 rounded-xl bg-white dark:bg-primary-950 border border-slate-200 dark:border-primary-800">
                        <span class="text-[10px] text-slate-400 dark:text-primary-500 block uppercase font-bold">Data Sovereignty</span>
                        <span class="text-sm font-black text-slate-800 dark:text-white font-sans">100% Isolated</span>
                    </div>
                    <div class="p-3 rounded-xl bg-white border border-slate-200 dark:bg-primary-950 dark:border-primary-800">
                        <span class="text-[10px] text-slate-400 dark:text-primary-500 block uppercase font-bold">App Sync Engines</span>
                        <span class="text-sm font-black text-primary-600 dark:text-primary-400 font-sans">Multi-Tenant</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- Dual Framework Node Breakdown -->
<section class="py-16 lg:py-24 px-6 sm:px-12 lg:px-24 xl:px-32 bg-slate-50 dark:bg-black transition-colors duration-300 relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen max-w-[100vw] border-b border-slate-200 dark:border-slate-800">
    <div class="max-w-7xl mx-auto">
        <div class="text-center max-w-2xl mx-auto mb-16 space-y-3">
            <span class="text-xs font-black uppercase tracking-[0.2em] text-secondary-600 dark:text-secondary-400">Operational Separation</span>
            <h2 class="text-3xl font-black text-slate-900 dark:text-white uppercase font-sans tracking-tight">Two Entities. One Shared Matrix.</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium leading-relaxed">
                Our platform isolates specialized functional modules based on connection source points to preserve rigorous compliance standards.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12">
            <!-- Landlord Infrastructure Card -->
            <div class="p-8 sm:p-10 rounded-[2.5rem] bg-white border-2 border-slate-200/80 dark:bg-slate-900/40 dark:border-slate-800/80 flex flex-col justify-between space-y-8 shadow-sm">
                <div class="space-y-6">
                    <div class="w-14 h-14 rounded-2xl bg-primary-500/10 dark:bg-primary-500/5 flex items-center justify-center border border-primary-500/20 text-primary-600 dark:text-primary-400">
                        <i class="fa-solid fa-shield-halved text-2xl"></i>
                    </div>
                    <div class="space-y-3">
                        <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Portfolio Operators</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 font-medium leading-relaxed">
                            Landlords deploy specialized real-time micro-applications to manage structural assets, issue legal structural requests, review cryptographically sound tenant records, and subscribe to tailored tracking layouts.
                        </p>
                    </div>
                </div>
                <div class="h-px bg-slate-100 dark:bg-slate-800 w-full"></div>
                <ul class="space-y-2.5 text-xs font-bold text-slate-600 dark:text-slate-400">
                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-primary-500 text-[10px]"></i> On-Demand Micro-App Provisioning</li>
                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-primary-500 text-[10px]"></i> Centralized Inspection Report Sinks</li>
                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-primary-500 text-[10px]"></i> Custom Layout Asset Controls</li>
                </ul>
            </div>

            <!-- Tenant Compliance Card -->
            <div class="p-8 sm:p-10 rounded-[2.5rem] bg-white border-2 border-slate-200/80 dark:bg-slate-900/40 dark:border-slate-800/80 flex flex-col justify-between space-y-8 shadow-sm">
                <div class="space-y-6">
                    <div class="w-14 h-14 rounded-2xl bg-secondary-500/10 dark:bg-secondary-500/5 flex items-center justify-center border border-secondary-500/20 text-secondary-600 dark:text-secondary-400">
                        <i class="fa-solid fa-user-check text-2xl"></i>
                    </div>
                    <div class="space-y-3">
                        <h3 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Compliance Profiles</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 font-medium leading-relaxed">
                            Tenants initialize structural authentication logs. By utilizing safe real-time validation layers, applicant targets seamlessly link screening profiles directly to target landlord asset trees.
                        </p>
                    </div>
                </div>
                <div class="h-px bg-slate-100 dark:bg-slate-800 w-full"></div>
                <ul class="space-y-2.5 text-xs font-bold text-slate-600 dark:text-slate-400">
                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-secondary-500 text-[10px]"></i> Real-Time Processing Logs</li>
                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-secondary-500 text-[10px]"></i> Secure Vault Identity Keys</li>
                    <li class="flex items-center gap-2"><i class="fa-solid fa-check text-secondary-500 text-[10px]"></i> Continuous Verification States</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . "/../components/general/operational-matrix.php"; ?>
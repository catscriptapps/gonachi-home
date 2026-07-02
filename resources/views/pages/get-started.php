<?php
// /resources/views/pages/get-started.php

declare(strict_types=1);

use Src\Config\NavigationConfig;

/** @var string $assetBase */
/** @var string $appName */
/** @var string $baseUrl */

// Pull entirely centralized route mappings for context continuity
$publicLinks = NavigationConfig::getNavLinks(false);
$homeUrl = $publicLinks['Home']['url'] ?? $baseUrl;
?>

<section class="py-12 lg:py-20 px-6 sm:px-12 lg:px-24 xl:px-32 bg-white dark:bg-slate-900 transition-colors duration-300 relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen max-w-[100vw] overflow-hidden">
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#00000001_1px,transparent_1px),linear-gradient(to_bottom,#00000001_1px,transparent_1px)] dark:bg-[linear-gradient(to_right,#ffffff01_1px,transparent_1px),linear-gradient(to_bottom,#ffffff01_1px,transparent_1px)] bg-[size:32px_48px] pointer-events-none"></div>
    <div class="absolute top-0 right-1/4 w-[600px] h-[600px] bg-primary-500/[0.02] rounded-full blur-[160px] pointer-events-none"></div>

    <div class="max-w-6xl mx-auto relative z-10" x-data="{ userRole: null }">

        <nav class="flex items-center gap-2 mb-10 text-sm font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">
            <a href="<?= $homeUrl ?>" data-partial data-title="Home" data-summary="Centralized Landlord Infrastructure" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Home</a>
            <i class="fa-solid fa-angle-right text-[10px] text-slate-300 dark:text-slate-700 stroke-[3]"></i>
            <span class="text-slate-900 dark:text-white font-black">Get Started</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start mb-16" x-show="!userRole" x-transition:enter="transition ease-out duration-300">
            <div class="flex flex-col space-y-4 lg:col-span-7">
                <div class="inline-flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-primary-500 dark:bg-primary-400 animate-pulse"></span>
                    <p class="uppercase tracking-[0.25em] text-xs font-black text-primary-600 dark:text-primary-400">
                        Initialization Node
                    </p>
                </div>
                <h1 class="text-3xl sm:text-4xl xl:text-5xl font-black text-slate-900 dark:text-white tracking-tight uppercase font-sans leading-none">
                    Select Your <br />
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-600 to-secondary-600 dark:from-primary-400 dark:to-secondary-400">
                        Operational Path
                    </span>
                </h1>
                <p class="text-base text-slate-500 dark:text-slate-400 max-w-xl font-bold leading-relaxed">
                    Welcome to the core platform setup terminal. Route your connection to deploy administrative landlord properties or initialize real-time tenant authentication channels.
                </p>
            </div>

            <div class="lg:col-span-5 bg-slate-50/50 dark:bg-black/40 border-2 border-slate-200/60 dark:border-slate-800/80 rounded-[2rem] p-6 backdrop-blur-sm hidden sm:block">
                <div class="space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b-2 border-slate-200/40 dark:border-slate-800/60">
                        <span class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Initialization Context</span>
                        <span class="text-[10px] font-black text-slate-400 dark:text-slate-400 uppercase tracking-wide">v1.4.2</span>
                    </div>
                    <p class="text-xs text-slate-400 dark:text-slate-500 font-bold leading-normal">
                        Your identity scope configures the layout options, compliance guardrails, and dashboard components initialized inside your browser session.
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-stretch" x-show="!userRole" x-transition:enter="transition ease-out duration-300">

            <div @click="userRole = 'landlord'" class="p-8 sm:p-10 rounded-[2.5rem] bg-slate-50/60 border-2 border-slate-200/80 hover:border-primary-500/50 dark:bg-black dark:border-slate-800/80 dark:hover:border-primary-500/40 transition-all duration-300 cursor-pointer group flex flex-col justify-between text-left shadow-lg">
                <div class="space-y-6">
                    <div class="w-20 h-20 rounded-2xl bg-primary-500/10 dark:bg-primary-500/5 flex items-center justify-center border-2 border-primary-500/20 text-primary-600 dark:text-primary-400 group-hover:scale-110 transition-transform duration-300">
                        <i class="fa-solid fa-building-user text-4xl"></i>
                    </div>
                    <div class="space-y-2">
                        <span class="text-xs uppercase font-black tracking-widest text-primary-600 dark:text-primary-400 block">Infrastructure Management</span>
                        <h2 class="text-3xl font-black text-slate-900 dark:text-white font-sans uppercase tracking-tight">I am a Landlord</h2>
                        <p class="text-base text-slate-500 dark:text-slate-400 font-bold leading-relaxed">Access core provisioning options, manage property portfolios, or deploy automated tenant risk profiling configurations.</p>
                    </div>
                </div>
                <div class="pt-10 flex items-center gap-2 text-sm font-black uppercase text-primary-600 dark:text-primary-400 tracking-widest">
                    Configure Gateway <i class="fa-solid fa-arrow-right stroke-[3] transition-transform group-hover:translate-x-1.5"></i>
                </div>
            </div>

            <div @click="userRole = 'tenant'" class="p-8 sm:p-10 rounded-[2.5rem] bg-slate-50/60 border-2 border-slate-200/80 hover:border-secondary-500/50 dark:bg-black dark:border-slate-800/80 dark:hover:border-secondary-500/40 transition-all duration-300 cursor-pointer group flex flex-col justify-between text-left shadow-lg">
                <div class="space-y-6">
                    <div class="w-20 h-20 rounded-2xl bg-secondary-500/10 dark:bg-secondary-500/5 flex items-center justify-center border-2 border-secondary-500/20 text-secondary-600 dark:text-secondary-400 group-hover:scale-110 transition-transform duration-300">
                        <i class="fa-solid fa-key text-4xl"></i>
                    </div>
                    <div class="space-y-2">
                        <span class="text-xs uppercase font-black tracking-widest text-secondary-600 dark:text-secondary-400 block">Compliance Pipeline</span>
                        <h2 class="text-3xl font-black text-slate-900 dark:text-white font-sans uppercase tracking-tight">I am a Tenant</h2>
                        <p class="text-base text-slate-500 dark:text-slate-400 font-bold leading-relaxed">Initialize a new secure real-time rental application node or review status layers of existing validation profiles.</p>
                    </div>
                </div>
                <div class="pt-10 flex items-center gap-2 text-sm font-black uppercase text-secondary-600 dark:text-secondary-400 tracking-widest">
                    Access Pipeline <i class="fa-solid fa-arrow-right stroke-[3] transition-transform group-hover:translate-x-1.5"></i>
                </div>
            </div>

        </div>

        <div class="max-w-2xl mx-auto" x-show="userRole" x-cloak x-transition:enter="transition ease-out duration-300 delay-100">

            <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-100 dark:border-slate-800">
                <button @click="userRole = null" class="inline-flex items-center gap-2 text-sm font-black text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 uppercase tracking-wider transition-colors">
                    <i class="fa-solid fa-chevron-left text-[11px] stroke-[3]"></i> Change Profile Target
                </button>
                <span class="px-3 py-1 text-xs font-black uppercase rounded-lg tracking-widest border"
                    :class="userRole === 'landlord' ? 'bg-primary-500/10 text-primary-600 dark:text-primary-400 border-primary-500/20' : 'bg-secondary-500/10 text-secondary-600 dark:text-secondary-400 border-secondary-500/20'"
                    x-text="userRole === 'landlord' ? 'Landlord Module' : 'Tenant Module'"></span>
            </div>

            <div class="space-y-4" x-show="userRole === 'landlord'">
                <a href="<?= $baseUrl ?>services" data-partial data-title="Service Suite Modules" data-summary="Browse core real-time ecosystem applications"
                    class="flex items-center justify-between p-6 rounded-2xl bg-slate-50/50 border-2 border-slate-200 hover:border-primary-500/40 dark:bg-black dark:border-slate-800 text-left transition-all group">
                    <div class="flex items-center gap-5">
                        <div class="w-14 h-14 rounded-xl bg-primary-500/10 text-primary-600 dark:text-primary-400 flex items-center justify-center shrink-0 border border-primary-500/20"><i class="fa-solid fa-cubes text-xl"></i></div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white uppercase tracking-tight">Preview Available Services</h3>
                            <p class="text-sm text-slate-400 dark:text-slate-500 font-bold mt-0.5">Explore subscription options, inspection modules, and structural scale tiers.</p>
                        </div>
                    </div>
                    <i class="fa-solid fa-angle-right text-slate-300 group-hover:text-primary-500 text-lg transition-colors pl-4 stroke-[2]"></i>
                </a>

                <a href="<?= $baseUrl ?>login" data-login-button title="Authenticate environment identity credentials"
                    class="flex items-center justify-between p-6 rounded-2xl bg-slate-50/50 border-2 border-slate-200 hover:border-primary-500/40 dark:bg-black dark:border-slate-800 text-left transition-all group">
                    <div class="flex items-center gap-5">
                        <div class="w-14 h-14 rounded-xl bg-primary-500/10 text-primary-600 dark:text-primary-400 flex items-center justify-center shrink-0 border border-primary-500/20"><i class="fa-solid fa-right-to-bracket text-xl"></i></div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white uppercase tracking-tight">Returning Landlord Dashboard</h3>
                            <p class="text-sm text-slate-400 dark:text-slate-500 font-bold mt-0.5">Sign in to initialize secure operations management interfaces.</p>
                        </div>
                    </div>
                    <i class="fa-solid fa-angle-right text-slate-300 group-hover:text-primary-500 text-lg transition-colors pl-4 stroke-[2]"></i>
                </a>

                <a href="javascript:" title="Establish new centralized management credentials"
                    class="register-btn flex items-center justify-between p-6 rounded-2xl bg-slate-50/50 border-2 border-slate-200 hover:border-primary-500/40 dark:bg-black dark:border-slate-800 text-left transition-all group">
                    <div class="flex items-center gap-5">
                        <div class="w-14 h-14 rounded-xl bg-primary-500/10 text-primary-600 dark:text-primary-400 flex items-center justify-center shrink-0 border border-primary-500/20"><i class="fa-solid fa-user-plus text-xl"></i></div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white uppercase tracking-tight">Create New Landlord Account</h3>
                            <p class="text-sm text-slate-400 dark:text-slate-500 font-bold mt-0.5">Provision an isolated core deployment profile immediately.</p>
                        </div>
                    </div>
                    <i class="fa-solid fa-angle-right text-slate-300 group-hover:text-primary-500 text-lg transition-colors pl-4 stroke-[2]"></i>
                </a>
            </div>

            <div class="space-y-4" x-show="userRole === 'tenant'">
                <a href="<?= $baseUrl ?>apply" data-partial data-title="Initialize Compliance Pipeline" data-summary="Begin screening validation framework authorization"
                    class="flex items-center justify-between p-6 rounded-2xl bg-slate-50/50 border-2 border-slate-200 hover:border-secondary-500/40 dark:bg-black dark:border-slate-800 text-left transition-all group">
                    <div class="flex items-center gap-5">
                        <div class="w-14 h-14 rounded-xl bg-secondary-500/10 text-secondary-600 dark:text-secondary-400 flex items-center justify-center shrink-0 border border-secondary-500/20"><i class="fa-solid fa-file-signature text-xl"></i></div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white uppercase tracking-tight">Start New Rental Application</h3>
                            <p class="text-sm text-slate-400 dark:text-slate-500 font-bold mt-0.5">Initialize background verification profiles and profile assets.</p>
                        </div>
                    </div>
                    <i class="fa-solid fa-angle-right text-slate-300 group-hover:text-secondary-500 text-lg transition-colors pl-4 stroke-[2]"></i>
                </a>

                <a href="<?= $baseUrl ?>applications" data-partial data-title="Manage Application Pipelines" data-summary="Track validation statuses and continuous processing history"
                    class="flex items-center justify-between p-6 rounded-2xl bg-slate-50/50 border-2 border-slate-200 hover:border-secondary-500/40 dark:bg-black dark:border-slate-800 text-left transition-all group">
                    <div class="flex items-center gap-5">
                        <div class="w-14 h-14 rounded-xl bg-secondary-500/10 text-secondary-600 dark:text-secondary-400 flex items-center justify-center shrink-0 border border-secondary-500/20"><i class="fa-solid fa-folder-open text-xl"></i></div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white uppercase tracking-tight">Manage Existing Applications</h3>
                            <p class="text-sm text-slate-400 dark:text-slate-500 font-bold mt-0.5">Check progress state layers or supply missing validation logs.</p>
                        </div>
                    </div>
                    <i class="fa-solid fa-angle-right text-slate-300 group-hover:text-secondary-500 text-lg transition-colors pl-4 stroke-[2]"></i>
                </a>
            </div>

        </div>

    </div>
</section>
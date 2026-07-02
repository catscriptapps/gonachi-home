<?php
// /resources/views/pages/rental-applications.php

declare(strict_types=1);

/** @var string $assetBase */
/** @var string $appName */
/** @var string $baseUrl */
/** @var bool $isLoggedIn */
?>

<!-- Embedded Accessible Motion Overlays -->
<style>
    @keyframes subtlePulse {

        0%,
        100% {
            transform: scale(1);
            opacity: 0.9;
        }

        50% {
            transform: scale(1.03);
            opacity: 1;
        }
    }

    @keyframes floatElement {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-6px);
        }
    }

    .animate-subtle-pulse {
        animation: subtlePulse 3s ease-in-out infinite;
    }

    .animate-float {
        animation: floatElement 4s ease-in-out infinite;
    }
</style>

<!-- Hero Matrix & Intake Pipeline Panel -->
<section class="relative overflow-hidden bg-slate-50 dark:bg-slate-950 py-16 lg:py-24 px-6 sm:px-12 lg:px-24 xl:px-32 transition-colors duration-300 border-b-4 border-primary-600 relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen max-w-[100vw] font-sans">
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#00000005_2px,transparent_2px),linear-gradient(to_bottom,#00000005_2px,transparent_2px)] dark:bg-[linear-gradient(to_right,#ffffff03_2px,transparent_2px),linear-gradient(to_bottom,#ffffff03_2px,transparent_2px)] bg-[size:30px_40px] pointer-events-none"></div>

    <!-- Radiant Visual Amber Orbs -->
    <div class="absolute -top-12 -right-12 w-96 h-96 bg-primary-600/10 dark:bg-primary-600/5 rounded-full blur-[120px] pointer-events-none animate-subtle-pulse"></div>
    <div class="absolute -bottom-12 -left-12 w-96 h-96 bg-primary-500/[0.06] dark:bg-primary-500/[0.03] rounded-full blur-[120px] pointer-events-none"></div>

    <div class="relative z-10 max-w-7xl mx-auto space-y-8">

        <!-- Regional Path Breadcrumb Matrix -->
        <nav class="flex items-center space-x-2 text-xs font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 pb-4 border-b border-slate-200/60 dark:border-slate-800/80" aria-label="Breadcrumb" data-aos="fade-down" data-aos-duration="500">
            <a href="<?= $baseUrl ?>" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors duration-200">
                Home
            </a>
            <span class="text-slate-300 dark:text-slate-700 font-normal">/</span>
            <a href="<?= $baseUrl ?>services" data-partial data-title="Service Suite Modules" data-summary="Browse core real-time ecosystem applications" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors duration-200">
                Services
            </a>
            <span class="text-slate-300 dark:text-slate-700 font-normal">/</span>
            <span class="text-slate-800 dark:text-slate-200" aria-current="page">
                Rental Applications
            </span>
        </nav>

        <div class="grid lg:grid-cols-12 gap-16 items-center" x-data="{ tab: 'tenant' }">

            <!-- Left Column: Premium Pitch Matrix -->
            <div class="flex flex-col space-y-6 lg:col-span-6 transition-all duration-500" data-aos="fade-right" data-aos-duration="800">
                <div class="inline-flex items-center gap-3 bg-secondary-100 dark:bg-primary-950/40 px-4 py-2 rounded-lg w-fit border border-primary-200 dark:border-primary-900/50">
                    <span class="h-3 w-3 rounded-full bg-primary-600 animate-ping"></span>
                    <p class="uppercase tracking-wider text-sm font-black text-primary-700 dark:text-primary-400">
                        PMB Core Intake System
                    </p>
                </div>

                <h1 class="text-4xl sm:text-5xl xl:text-6xl font-black text-slate-900 dark:text-white tracking-tight leading-tight uppercase">
                    Residential Rental <br />
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-600 via-primary-500 to-primary-600 dark:from-primary-400 dark:via-primary-400 dark:to-primary-400">
                        Applications
                    </span>
                </h1>

                <p class="text-slate-700 dark:text-slate-300 max-w-xl text-base sm:text-lg font-bold leading-relaxed">
                    Welcome to the PMB Residential Portal. Prospective tenants can initiate secure screening via their property-specific authorization token. Landlords can scale real-time portfolio allocation sequences effortlessly.
                </p>

                <div class="pt-6 flex flex-wrap gap-4">
                    <a href="<?= $baseUrl ?>subscriptions"
                        data-partial
                        data-title="Landlord Subscription Deck | Asset Token Provisioning"
                        data-summary="Unlock comprehensive scaling matrix arrays for your real estate portfolio. Provision real-time applicant background tracking nodes, activate automated credit registry evaluation layers, and instantiate unique one-time secure intake tokens for multi-unit townhouse or single-family asset complexes down through Simcoe County."
                        class="inline-flex items-center justify-center px-8 py-4 rounded-xl bg-gradient-to-r from-primary-600 to-primary-500 hover:from-primary-500 hover:to-primary-400 text-white font-black text-sm shadow-xl shadow-primary-500/30 transition-all duration-300 tracking-wider uppercase transform hover:-translate-y-0.5">
                        Landlord Subscriptions
                    </a>
                    <a href="#application-matrix" class="inline-flex items-center justify-center px-6 py-4 rounded-xl bg-slate-200 hover:bg-slate-300 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-900 dark:text-slate-100 font-black text-sm transition-all duration-300 border-2 border-transparent dark:border-slate-800">
                        Explore Parameters
                    </a>
                </div>
            </div>

            <!-- Right Column: Dual-State Form Engine -->
            <div class="w-full lg:col-span-6 bg-white dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-800 shadow-2xl rounded-3xl p-8 sm:p-10 backdrop-blur-sm relative overflow-hidden transition-all duration-300 hover:border-gray-500/30" data-aos="fade-left" data-aos-duration="800">
                <div class="absolute top-0 right-0 w-32 h-32 bg-primary-500/[0.04] rounded-bl-full pointer-events-none"></div>

                <!-- Segmented Interface Control Switching Nodes -->
                <div class="flex p-2 bg-slate-100 dark:bg-slate-950 rounded-xl mb-8 border border-slate-200/60 dark:border-slate-800">
                    <button @click="tab = 'tenant'" :class="tab === 'tenant' ? 'bg-primary-600 text-white shadow-md scale-105' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'" class="flex-1 py-3.5 text-center text-sm font-black uppercase tracking-wider rounded-lg transition-all duration-300">
                        <i class="fa-solid fa-key mr-2 text-base"></i> Tenant Access
                    </button>
                    <button @click="tab = 'login'" :class="tab === 'login' ? 'bg-primary-600 text-white shadow-md scale-105' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'" class="flex-1 py-3.5 text-center text-sm font-black uppercase tracking-wider rounded-lg transition-all duration-300">
                        <i class="fa-solid fa-user-shield mr-2 text-base"></i> Portal Login
                    </button>
                </div>

                <!-- View Frame A: Access Code Route -->
                <div x-show="tab === 'tenant'" x-transition:enter="transition ease-out duration-300" class="space-y-6">
                    <div class="pb-4 border-b-2 border-slate-100 dark:border-slate-800">
                        <span class="text-xs font-black text-primary-600 dark:text-primary-400 uppercase tracking-widest block">Intake Pipeline Activation</span>
                        <p class="text-sm sm:text-base text-slate-600 dark:text-slate-400 mt-2 font-bold leading-normal">Input your unique one-time provisioning code provided by the property manager to map your profile to your designated property.</p>
                    </div>

                    <form class="space-y-6" method="POST" action="<?= $baseUrl ?>rental-applications/initialize">
                        <div class="space-y-2">
                            <label class="text-xs uppercase font-black tracking-wider text-slate-500 dark:text-slate-400">One-Time Property Access Code</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                    <i class="fa-solid fa-qrcode text-lg"></i>
                                </div>
                                <input type="text" name="access_code" placeholder="e.g., ACC-Simcoe-XXXX" required class="w-full pl-12 pr-4 py-4 bg-slate-50 dark:bg-slate-950 border-2 border-slate-200 dark:border-slate-800 focus:border-primary-500 dark:focus:border-primary-500 text-slate-900 dark:text-white rounded-xl text-sm sm:text-base font-black font-mono tracking-widest focus:outline-none transition-colors">
                            </div>
                        </div>
                        <button type="submit" class="w-full text-center py-4 px-6 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-black text-sm sm:text-base transition-all duration-300 shadow-md uppercase tracking-wider transform hover:scale-[1.01]">
                            Start New Application <i class="fa-solid fa-arrow-right ml-2"></i>
                        </button>
                    </form>
                </div>

                <!-- View Frame B: User Portal Frame Authentication -->
                <div x-show="tab === 'login'" x-cloak x-transition:enter="transition ease-out duration-300" class="space-y-6">
                    <div class="pb-4 border-b-2 border-slate-100 dark:border-slate-800">
                        <span class="text-xs font-black text-primary-600 dark:text-primary-400 uppercase tracking-widest block">Resume Session Framework</span>
                        <p class="text-sm sm:text-base text-slate-600 dark:text-slate-400 mt-2 font-bold leading-normal">Log in using your secure username parameters to review, save, or track an outstanding application submission.</p>
                    </div>

                    <form class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-xs uppercase font-black tracking-wider text-slate-500 dark:text-slate-400">User Name / Email</label>
                            <input type="text" name="user_name" placeholder="Enter registration identifier" required class="w-full px-4 py-4 bg-slate-50 dark:bg-slate-950 border-2 border-slate-200 dark:border-slate-800 focus:border-primary-500 dark:focus:border-primary-500 text-slate-900 dark:text-white rounded-xl text-sm sm:text-base font-bold focus:outline-none transition-colors">
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <label class="text-xs uppercase font-black tracking-wider text-slate-500 dark:text-slate-400">Password</label>
                                <a href="javascript:" class="text-xs sm:text-sm font-black text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300 underline btn-forgot-password">Forgot Password?</a>
                            </div>
                            <input type="password" name="password" placeholder="••••••••••••" required class="w-full px-4 py-4 bg-slate-50 dark:bg-slate-950 border-2 border-slate-200 dark:border-slate-800 focus:border-primary-500 dark:focus:border-primary-500 text-slate-900 dark:text-white rounded-xl text-sm sm:text-base font-bold focus:outline-none transition-colors">
                        </div>

                        <input type="hidden" name="c" value="app">
                        <input type="hidden" name="a" value="login_verify">

                        <button type="button" class="w-full text-center py-4 px-6 rounded-xl bg-slate-950 hover:bg-slate-900 dark:bg-primary-600 dark:hover:bg-primary-700 text-white font-black text-sm sm:text-base transition-all duration-300 shadow-md uppercase tracking-wider btn-login-verify transform hover:scale-[1.01]">
                            Verify Authenticated Access
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</section>

<!-- Active Parameter Analytics Framework Grid -->
<section id="application-matrix" class="py-24 px-6 sm:px-12 lg:px-24 xl:px-32 bg-primary-200 dark:bg-slate-900 transition-colors duration-300 relative overflow-hidden relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen max-w-[100vw] font-sans">
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#00000004_2px,transparent_2px),linear-gradient(to_bottom,#00000004_2px,transparent_2px)] dark:bg-[linear-gradient(to_right,#ffffff02_2px,transparent_2px),linear-gradient(to_bottom,#ffffff02_2px,transparent_2px)] bg-[size:30px_40px] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto space-y-12 relative z-10">
        <div class="flex flex-col space-y-3 border-b-2 border-slate-200 dark:border-white/10 pb-8" data-aos="fade-up" data-aos-duration="600">
            <h2 class="text-sm font-black uppercase tracking-widest text-primary-600 dark:text-primary-400 px-2">
                Workspace Hub
            </h2>
            <p class="text-2xl sm:text-3xl font-black text-slate-800 dark:text-slate-100 tracking-tight px-2 uppercase">Active Intake Parameters</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
            <!-- Parameter Block One -->
            <div class="group relative overflow-hidden rounded-[2rem] bg-white dark:bg-black p-10 shadow-xl border-2 border-slate-200/80 dark:border-slate-800 transition-all duration-300 hover:-translate-y-2 hover:border-gray-500" data-aos="fade-up" data-aos-delay="100" data-aos-duration="700">
                <div class="absolute inset-0 bg-gradient-to-br from-primary-500/[0.02] via-transparent to-primary-500/[0.02] opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>

                <div class="flex items-center justify-between relative z-10 animate-float">
                    <div class="rounded-2xl bg-secondary-100 dark:bg-primary-950/50 border-2 border-primary-200 dark:border-primary-900 text-primary-600 dark:text-primary-400 group-hover:bg-primary-600 group-hover:text-white group-hover:border-primary-600 transition-all duration-500 p-5 shadow-inner">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                </div>

                <div class="mt-10 relative z-10 space-y-4">
                    <h3 class="text-2xl font-black tracking-tight text-slate-900 dark:text-slate-100 transition-colors duration-300 group-hover:text-primary-600 dark:group-hover:text-primary-400 uppercase">
                        Identity & Background Verification
                    </h3>
                    <p class="text-base text-slate-600 dark:text-slate-400 leading-relaxed font-bold">
                        Securely capture consumer authentication profiles. Scan background pipelines and regional history registries automatically via absolute data protection keys.
                    </p>

                    <div class="flex items-center gap-3 pt-4 border-t-2 border-slate-100 dark:border-slate-900/60">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-primary-600"></span>
                        </span>
                        <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider font-black">Active Module Channel</p>
                    </div>
                </div>
            </div>

            <!-- Parameter Block Two -->
            <div class="group relative overflow-hidden rounded-[2rem] bg-white dark:bg-black p-10 shadow-xl border-2 border-slate-200/80 dark:border-slate-800 transition-all duration-300 hover:-translate-y-2 hover:border-gray-500" data-aos="fade-up" data-aos-delay="300" data-aos-duration="700">
                <div class="absolute inset-0 bg-gradient-to-br from-primary-500/[0.02] via-transparent to-primary-500/[0.02] opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>

                <div class="flex items-center justify-between relative z-10 animate-float" style="animation-delay: 0.5s;">
                    <div class="rounded-2xl bg-secondary-100 dark:bg-primary-950/50 border-2 border-primary-200 dark:border-primary-900 text-primary-600 dark:text-primary-400 group-hover:bg-primary-600 group-hover:text-white group-hover:border-primary-600 transition-all duration-500 p-5 shadow-inner">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z" />
                        </svg>
                    </div>
                </div>

                <div class="mt-10 relative z-10 space-y-4">
                    <h3 class="text-2xl font-black tracking-tight text-slate-900 dark:text-slate-100 transition-colors duration-300 group-hover:text-primary-600 dark:group-hover:text-primary-400 uppercase">
                        Sovereignty Credit Monitoring
                    </h3>
                    <p class="text-base text-slate-600 dark:text-slate-400 leading-relaxed font-bold">
                        Deploy real-time evaluation layers targeting applicant balance indexes. Streamline complex histories cleanly into simple financial status nodes.
                    </p>

                    <div class="flex items-center gap-3 pt-4 border-t-2 border-slate-100 dark:border-slate-900/60">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-primary-600"></span>
                        </span>
                        <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider font-black">Active Module Channel</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Video Infrastructure Briefing & Regional Overview -->
<section class="py-24 px-6 sm:px-12 lg:px-24 xl:px-32 bg-slate-50 dark:bg-slate-950 border-t-2 border-slate-200 dark:border-slate-900 transition-colors duration-200 overflow-hidden relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen max-w-[100vw] font-sans">
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-primary-500/[0.04] rounded-full blur-[120px] pointer-events-none animate-subtle-pulse"></div>

    <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-16 items-center relative z-10">
        <div class="space-y-6" data-aos="fade-right" data-aos-duration="800">
            <span class="inline-block bg-secondary-100 dark:bg-primary-950 text-primary-700 dark:text-primary-400 px-4 py-2 rounded-xl text-sm font-black tracking-widest uppercase border border-primary-200 dark:border-primary-900">
                System Briefing
            </span>

            <h2 class="text-4xl font-black text-slate-900 dark:text-white tracking-tight uppercase leading-tight">Precision Tenant Intake</h2>

            <p class="text-slate-700 dark:text-slate-300 leading-relaxed text-base sm:text-lg font-bold">
                Optimizing multi-unit pipelines requires surgical system precision. We filter applicant metrics down to clear risk signals, helping landlords manage townhouse and single-family asset blocks throughout Simcoe County with flawless operational fidelity.
            </p>

            <ul class="space-y-4 text-slate-700 dark:text-slate-300 text-sm sm:text-base font-black list-none pl-0">
                <li class="flex items-start gap-3">
                    <span class="text-primary-600 dark:text-primary-400 text-xl font-black">✓</span>
                    <span>Compliant document storage nodes secure applicant sovereignty.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-primary-600 dark:text-primary-400 text-xl font-black">✓</span>
                    <span>Direct parsing engine eliminates external validation gaps.</span>
                </li>
            </ul>
        </div>

        <!-- High-Tech Interactive Video Launch Block -->
        <div class="bg-white dark:bg-slate-900 rounded-[2rem] p-10 border-2 border-slate-200 dark:border-slate-800 shadow-2xl relative overflow-hidden group flex flex-col justify-between min-h-[320px] transition-all duration-300 hover:border-gray-500" data-aos="fade-left" data-aos-duration="800">
            <div class="absolute -top-12 -right-12 w-32 h-32 bg-primary-500/10 rounded-full blur-2xl group-hover:bg-primary-500/20 transition-colors duration-500"></div>

            <div class="space-y-4">
                <h3 class="font-black text-2xl text-slate-900 dark:text-white tracking-tight uppercase">Ecosystem Walkthrough</h3>
                <p class="text-slate-600 dark:text-slate-400 text-base font-bold leading-relaxed">
                    Observe how our screening nodes operate under full loads. This automated demonstration reveals real-time validation tracking, income checker flags, and dynamic reporting layers.
                </p>
            </div>

            <div class="pt-8 flex flex-col sm:flex-row gap-6 items-center">
                <a href="#watch-demo-modal" class="w-full sm:w-auto inline-flex items-center justify-center gap-3 py-4 px-8 rounded-xl bg-primary-600 text-white font-black text-sm sm:text-base transition-all duration-300 shadow-lg shadow-primary-600/30 uppercase tracking-wider hover:bg-primary-700 transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-circle-play text-lg animate-pulse"></i> Watch System Demo
                </a>
                <div class="w-full sm:flex-1 p-4 rounded-xl bg-slate-50 dark:bg-slate-950 border-2 border-slate-200 dark:border-slate-900 text-xs sm:text-sm font-mono font-black text-slate-500 dark:text-slate-400 flex items-center justify-between">
                    <span>Ecosystem Status:</span>
                    <span class="text-primary-600 dark:text-primary-400 font-black uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-primary-600 dark:bg-primary-400 animate-ping"></span>
                        Operational
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Onboarding License Activation Area -->
<section id="landlord-matrix" class="py-24 px-6 sm:px-12 lg:px-24 xl:px-32 bg-primary-50 dark:bg-black transition-colors duration-300 relative overflow-hidden border-t-4 border-primary-600 relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen max-w-[100vw] font-sans" data-aos="fade-up" data-aos-duration="900">
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#00000003_2px,transparent_2px),linear-gradient(to_bottom,#00000003_2px,transparent_2px)] dark:bg-[linear-gradient(to_right,#ffffff01_2px,transparent_2px),linear-gradient(to_bottom,#ffffff01_2px,transparent_2px)] bg-[size:40px_40px] pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[36rem] h-[36rem] bg-primary-500/[0.04] rounded-full blur-[160px] pointer-events-none"></div>

    <div class="max-w-4xl mx-auto text-center relative z-10 space-y-10">
        <div class="flex flex-col items-center space-y-4">
            <span class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest bg-primary-600 text-white shadow-md shadow-primary-500/20">
                Ecosystem Expansion
            </span>
            <h2 class="text-4xl font-black text-slate-900 dark:text-white tracking-tight sm:text-5xl uppercase">
                Unlock the Applications Engine
            </h2>
            <p class="text-slate-700 dark:text-slate-300 text-base sm:text-lg font-bold max-w-2xl mx-auto leading-relaxed">
                Ready to deploy custom portals for your properties? Transition out of guest context right now. Subscribing maps structured application nodes straight onto your active broker command layer.
            </p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border-2 border-primary-200 dark:border-slate-800 p-8 sm:p-12 shadow-2xl relative overflow-hidden max-w-2xl mx-auto transition-all duration-300 hover:border-gray-500" data-aos="zoom-in" data-aos-delay="200" data-aos-duration="600">
            <div class="absolute top-0 inset-x-0 h-2 bg-gradient-to-r from-primary-600 via-primary-500 to-primary-600"></div>

            <div class="grid sm:grid-cols-2 gap-10 items-center text-left">
                <div class="space-y-4">
                    <p class="text-xs font-black text-primary-600 dark:text-primary-400 uppercase tracking-widest">Active License Matrix</p>
                    <div class="flex items-baseline gap-2">
                        <span class="text-5xl font-black text-slate-900 dark:text-white tracking-tight">$0</span>
                        <span class="text-sm font-black text-slate-400 dark:text-slate-500">/ monthly channel</span>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-400 font-bold leading-normal">
                        Requires a registered landlord profile. Includes zero overhead screening hooks, custom portfolio parameters, and live status parsing layers.
                    </p>
                </div>

                <div class="space-y-5">
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 text-sm sm:text-base font-black text-slate-800 dark:text-slate-200">
                            <span class="text-primary-600 text-base">●</span> Unlimited Tokens
                        </div>
                        <div class="flex items-center gap-3 text-sm sm:text-base font-black text-slate-800 dark:text-slate-200">
                            <span class="text-primary-600 text-base">●</span> Real-Time Pipeline
                        </div>
                        <div class="flex items-center gap-3 text-sm sm:text-base font-black text-slate-800 dark:text-slate-200">
                            <span class="text-primary-600 text-base">●</span> Simcoe Node Branding
                        </div>
                    </div>

                    <?php if ($isLoggedIn ?? false): ?>
                        <!-- User authenticated: execute provisioning submission -->
                        <form method="POST" action="<?= $baseUrl ?>rental-applications/subscribe">
                            <button type="submit" class="w-full text-center py-4 px-6 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-black text-sm sm:text-base transition-all duration-300 shadow-md uppercase tracking-wider transform hover:scale-[1.02]">
                                Confirm Activation
                            </button>
                        </form>
                    <?php else: ?>
                        <!-- Guest user context: redirect to system auth layout -->
                        <a href="javascript:void(0);" class="register-btn w-full inline-flex items-center justify-center text-center py-4 px-6 rounded-xl bg-slate-950 hover:bg-slate-900 dark:bg-primary-600 dark:hover:bg-primary-700 text-white font-black text-sm sm:text-base transition-all duration-300 shadow-md uppercase tracking-wider transform hover:scale-[1.02]">
                            Create Account
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <p class="text-xs text-slate-500 dark:text-slate-400 font-black font-mono tracking-wide">
            Secure transactional layer verification node: active channel initialization parameters apply.
        </p>
    </div>
</section>
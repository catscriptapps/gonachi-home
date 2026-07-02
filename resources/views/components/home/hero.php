<?php
// /resources/views/components/home/hero.php

declare(strict_types=1);

/** @var string $assetBase */
/** @var string $appName */
/** @var string $baseUrl */

?>

<style>
    @keyframes slideshow-zoom-out-1 {
        0% {
            opacity: 0;
            transform: scale(1.1);
        }

        8% {
            opacity: 1;
        }

        45% {
            opacity: 1;
        }

        50% {
            opacity: 0;
            transform: scale(1.0);
        }

        100% {
            opacity: 0;
        }
    }

    @keyframes slideshow-zoom-out-2 {
        0% {
            opacity: 0;
        }

        50% {
            opacity: 0;
            transform: scale(1.1);
        }

        58% {
            opacity: 1;
        }

        95% {
            opacity: 1;
        }

        100% {
            opacity: 0;
            transform: scale(1.0);
        }
    }

    .animate-hero-slide-1 {
        animation: slideshow-zoom-out-1 16s infinite ease-in-out;
    }

    .animate-hero-slide-2 {
        animation: slideshow-zoom-out-2 16s infinite ease-in-out;
    }
</style>

<section class="relative overflow-hidden w-full min-h-[90vh] block lg:flex lg:flex-col lg:justify-between transition-colors duration-300">

    <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
        <div class="absolute inset-0 bg-cover bg-center animate-hero-slide-1"
            style="background-image: url('<?= $assetBase ?>images/home/barrie.jpg');"></div>
        <div class="absolute inset-0 bg-cover bg-center animate-hero-slide-2"
            style="background-image: url('<?= $assetBase ?>images/home/orillia.jpg');"></div>

        <div class="absolute inset-0 bg-primary-950/45 mix-blend-multiply"></div>

        <div class="absolute inset-0 bg-gradient-to-b from-primary-950/95 via-primary-950/60 to-transparent h-4/5"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-primary-950 via-primary-950/40 to-transparent"></div>
    </div>

    <div class="hidden lg:block lg:h-48 xl:h-56 w-full shrink-0 relative z-10 pointer-events-none"></div>

    <div class="relative z-10 w-full max-w-7xl mx-auto px-6 sm:px-8 lg:px-8 pt-40 pb-16 lg:py-20 flex-1 flex flex-col justify-center">

        <div class="grid lg:grid-cols-12 gap-10 lg:gap-16 items-center w-full">
            <div data-aos="fade-right" data-aos-duration="800" class="flex flex-col justify-center lg:col-span-7 space-y-6">

                <div class="flex items-start sm:items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-slate-300 animate-pulse mt-1.5 sm:mt-0 shrink-0"></span>
                    <p class="uppercase tracking-wider sm:tracking-[0.25em] text-[10px] sm:text-[11px] font-black text-slate-300 drop-shadow-md leading-normal">
                        <?= htmlspecialchars($appName) ?> · Centralized Landlord Infrastructure
                    </p>
                </div>

                <h1 class="text-3xl sm:text-5xl xl:text-6xl font-black leading-tight text-white tracking-tight drop-shadow-md">
                    Optimize Your <br />
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-300 via-gray-100 to-white">
                        Property Ecosystem
                    </span>
                </h1>

                <p class="text-gray-100 max-w-2xl leading-relaxed text-sm sm:text-base font-semibold drop-shadow-md">
                    Empower your real estate business operations from a singular control terminal. Deploy specialized application modules, synchronize custom tenant rental logic, and initialize targeted on-demand subscriptions designed to keep management fluid and profitable.
                </p>

                <div class="col-span-2 flex items-center gap-3 pt-3 border-t border-white/10">
                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-gray-400"></span>
                    <p class="text-xs text-gray-300 font-semibold italic leading-normal drop-shadow-sm">
                        Core Architecture Standard: Integrated modular utility configurations for scaling modern property operations.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 items-center pt-4 w-full sm:w-auto">
                    <a href="<?= $baseUrl ?>subscriptions" data-partial
                        class="group/btn1 relative inline-flex items-center justify-center px-8 py-4 rounded-xl bg-primary-600 text-white font-black text-sm shadow-xl shadow-primary-900/50 hover:-translate-y-1 hover:scale-[1.02] transition-all duration-300 overflow-hidden font-sans w-full sm:min-w-[220px] text-center">
                        <span class="relative z-10 flex items-center justify-center gap-2 w-full">
                            Explore Application Modules
                            <svg class="w-4 h-4 transition-transform duration-300 group-hover/btn1:translate-x-1 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </span>
                        <div class="absolute inset-0 z-0 bg-gradient-to-r from-primary-700 to-primary-500 opacity-0 group-hover/btn1:opacity-100 transition-opacity duration-300 ease-out"></div>
                    </a>

                    <a href="<?= $baseUrl ?>contact" data-partial
                        class="group/btn2 relative inline-flex items-center justify-center px-8 py-4 rounded-xl bg-white/10 backdrop-blur-md border border-white/20 hover:bg-white/20 text-white font-black text-sm hover:-translate-y-1 hover:scale-[1.02] transition-all duration-300 shadow-lg font-sans w-full sm:min-w-[200px] text-center">
                        <span class="flex items-center justify-center gap-2 w-full">
                            Enterprise Setup
                            <svg class="w-4 h-4 text-gray-300 transition-transform duration-300 group-hover/btn2:rotate-12 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </span>
                    </a>
                </div>
            </div>

            <div class="hidden lg:grid grid-cols-2 gap-4 lg:col-span-5">
                <div data-aos="fade-left" data-aos-duration="800" data-aos-delay="150" class="p-6 rounded-2xl bg-primary-950/30 backdrop-blur-md border border-white/20 shadow-sm transition-transform duration-300 hover:-translate-y-0.5">
                    <div class="flex items-center gap-2.5 mb-2">
                        <div class="p-2 rounded-lg bg-primary-400/30 text-primary-200">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold tracking-wider uppercase text-gray-300 drop-shadow-sm">Integrations</span>
                    </div>
                    <p class="text-2xl font-black text-white tracking-tight drop-shadow-sm">Active</p>
                    <p class="text-xs text-gray-300 font-medium mt-1 leading-normal drop-shadow-sm">System Modules Engaged</p>
                </div>

                <div class="p-6 rounded-2xl bg-primary-950/30 backdrop-blur-md border border-white/20 shadow-sm transition-transform duration-300 hover:-translate-y-0.5" data-aos="fade-left" data-aos-duration="800" data-aos-delay="300">
                    <div class="flex items-center gap-2.5 mb-2">
                        <div class="p-2 rounded-lg bg-white/10 text-gray-200">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold tracking-wider uppercase text-gray-300 drop-shadow-sm">Subscribers</span>
                    </div>
                    <p class="text-2xl font-black text-white tracking-tight drop-shadow-sm">Portfolios</p>
                    <p class="text-xs text-gray-300 font-medium mt-1 leading-normal drop-shadow-sm">Centralized Hub Terminals</p>
                </div>
            </div>
        </div>

    </div>
</section>
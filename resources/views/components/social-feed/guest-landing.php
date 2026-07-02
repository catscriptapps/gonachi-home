<?php
// /resources/views/components/social-feed/guest-landing.php

/** @var string $baseUrl */
?>

<div class="relative min-h-[80vh] flex items-center justify-center px-4 overflow-hidden">
    <div class="absolute top-0 left-1/4 w-96 h-96 bg-primary-500/10 rounded-full blur-[120px] -z-10 animate-pulse"></div>
    <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-secondary-500/10 rounded-full blur-[120px] -z-10 animate-pulse" style="animation-delay: 2s"></div>

    <div class="max-w-4xl w-full text-center space-y-8 animate-in fade-in zoom-in-95 duration-1000">
        <div class="inline-flex items-center justify-center p-4 rounded-3xl bg-gradient-to-tr from-primary-500 to-secondary-500 shadow-2xl shadow-primary-500/20 mb-4 rotate-3 hover:rotate-0 transition-transform duration-500">
            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
        </div>

        <div class="space-y-4">
            <h1 class="text-5xl md:text-7xl font-black text-secondary-900 dark:text-white tracking-tight leading-none">
                The New Era of <br />
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-500 to-secondary-500">Asset Exchange.</span>
            </h1>
            <p class="max-w-xl mx-auto text-lg text-gray-500 dark:text-gray-400 font-medium leading-relaxed">
                Unlock the market. Swap properties, trade high-value services, and negotiate directly with verified owners in the Gonachi Swap ecosystem.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
            <a href="<?= $baseUrl ?>login" data-login-button
                class="group relative inline-flex items-center justify-center px-8 py-4 font-black text-white transition-all duration-200 bg-secondary-900 dark:bg-primary-500 font-sans rounded-2xl hover:bg-secondary-800 dark:hover:bg-primary-600 active:scale-95 shadow-xl shadow-secondary-500/10">
                <span class="mr-3">Sign in to Start Swapping</span>
                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>

            <a href="javascript:"
                class="register-btn inline-flex items-center justify-center px-8 py-4 bg-gray-300 dark:bg-gray-800 font-black text-secondary-900 dark:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-2xl transition-all active:scale-95">
                Create an Account
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round" class="ml-2 w-5 h-5 transition-transform duration-300 group-hover:translate-x-1">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <line x1="19" y1="8" x2="19" y2="14" />
                    <line x1="16" y1="11" x2="22" y2="11" />
                </svg>
            </a>
        </div>

        <div class="grid grid-cols-3 gap-8 pt-12 border-t border-gray-100 dark:border-white/5 max-w-2xl mx-auto">
            <div>
                <div class="text-2xl font-black text-secondary-900 dark:text-white">$42M+</div>
                <div class="text-xs uppercase tracking-widest font-bold text-gray-400">Asset Value</div>
            </div>
            <div>
                <div class="text-2xl font-black text-secondary-900 dark:text-white">1.2k</div>
                <div class="text-xs uppercase tracking-widest font-bold text-gray-400">Active Swaps</div>
            </div>
            <div>
                <div class="text-2xl font-black text-secondary-900 dark:text-white">Instant</div>
                <div class="text-xs uppercase tracking-widest font-bold text-gray-400">Matching</div>
            </div>
        </div>
    </div>
</div>
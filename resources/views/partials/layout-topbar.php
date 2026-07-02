<?php
// /resources/views/partials/layout-topbar.php

use Src\Config\NavigationConfig;
use Src\Service\AuthService;

/** @var bool $isLoggedIn */
/** @var string $baseUrl */

// Centralize display logic by calling the new method in NavigationConfig.
// The extract() function imports the 'displayName' and 'initial' keys
// from the returned array into the current scope.
extract(NavigationConfig::getUserDisplayInfo());
?>

<div class="fixed top-0 left-0 w-full bg-black text-slate-200 px-4 sm:px-6 lg:px-8 py-3 text-sm sm:text-base flex justify-between items-center border-b-2 border-gray-700 min-h-[50px] sm:min-h-[52px] transition-colors duration-200 shadow-xl select-none z-[9999]">

    <div class="flex items-center gap-4 sm:gap-6">
        <a href="tel:1-866-709-9416" class="hover:text-secondary-400 flex items-center gap-2 transition-colors duration-200 group py-1">
            <svg class="w-4 h-4 text-secondary-500 group-hover:text-secondary-400 transition-colors group-hover:animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
            </svg>
            <span class="font-extrabold tracking-wide">1-866-709-9416</span>
        </a>

        <?php if (!$isLoggedIn) : ?>
            <span class="hidden md:inline text-slate-800 font-bold">|</span>

            <div class="hidden md:flex items-center gap-2 text-slate-300 font-bold">
                <svg class="w-4 h-4 text-secondary-500 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>137 Essa Rd Unit 1, Barrie, ON L4N 3K8</span>
            </div>

            <span class="text-slate-800 font-bold">|</span>

            <div class="flex items-center gap-3.5 pl-0.5">
                <a target="_blank" rel="noopener" href="https://www.facebook.com/PropertyManagementBrokers" class="text-slate-400 hover:text-secondary-500 transition-all duration-200 transform hover:scale-125" title="Facebook">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" />
                    </svg>
                </a>
                <a target="_blank" rel="noopener" href="https://www.instagram.com/propertymanagementbrokers/" class="text-slate-400 hover:text-secondary-500 transition-all duration-200 transform hover:scale-125" title="Instagram">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204 .013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.051.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z" />
                    </svg>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div class="flex items-center gap-4 sm:gap-6 font-bold">
        <div x-data="{ 
            timeString: '', 
            dateString: '',
            updateClock() {
                const now = new Date();
                this.timeString = now.toLocaleTimeString('en-CA', { 
                    hour: '2-digit', 
                    minute: '2-digit', 
                    second: '2-digit', 
                    hour12: true 
                });
                this.dateString = now.toLocaleDateString('en-CA', { 
                    year: 'numeric', 
                    month: 'short', 
                    day: '2-digit' 
                });
            } 
        }"
            x-init="updateClock(); setInterval(() => updateClock(), 1000)"
            class="hidden sm:flex items-center gap-4 px-4 py-1.5 rounded-xl bg-slate-950 border-2 border-slate-900 shadow-inner font-mono tracking-wide text-sm text-slate-400">

            <div class="flex items-center gap-2 border-r border-slate-800 pr-3">
                <span class="w-2 h-2 rounded-full bg-secondary-500 animate-pulse"></span>
                <span x-text="timeString" class="text-white font-black"></span>
            </div>

            <div x-text="dateString" class="uppercase font-black text-xs tracking-widest text-slate-500"></div>
        </div>

        <span class="text-slate-800 font-bold">|</span>

        <div class="flex items-center gap-2 text-slate-300">
            <button id="search-trigger" aria-label="Search" data-tooltip="Search (⌘K)"
                class="hidden group p-2 rounded-xl hover:bg-slate-900 hover:text-white transition-all duration-200">
                <svg class="w-5 h-5 group-hover:scale-125 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </button>

            <?php if ($isLoggedIn) : ?>
                <?php if (AuthService::isCat()) : ?>
                    <button data-reset-button data-tooltip="DB Reset"
                        class="hidden md:block group p-2 rounded-xl hover:bg-slate-900 hover:text-secondary-500 transition-all duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 group-hover:animate-bounce">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                <?php endif; ?>

                <?php if (AuthService::isAdmin()) : ?>
                    <div class="relative group">
                        <button id="messages-btn" aria-label="Messages" data-tooltip="Messages"
                            class="p-2 rounded-xl hover:bg-slate-900 hover:text-white transition-all duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 group-hover:scale-110 transition-transform">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25H4.5a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0L12 13.5 2.25 6.75" />
                            </svg>
                        </button>
                        <span id="messages-badge" class="absolute -top-0.5 -right-0.5 bg-secondary-600 text-white text-xs font-black h-4.5 w-4.5 flex items-center justify-center rounded-full hidden border-2 border-black shadow-md"></span>
                    </div>
                <?php endif; ?>

                <button data-tooltip="History" id="history-btn"
                    class="group p-2 rounded-xl hover:bg-slate-900 hover:text-white transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 group-hover:rotate-12 transition-transform">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </button>

                <button data-tooltip="Settings" id="settings-btn"
                    class="group p-2 rounded-xl hover:bg-slate-900 hover:text-white transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 group-hover:rotate-45 transition-transform duration-500">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a7.75 7.75 0 0 1 0 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281Z" />
                    </svg>
                </button>
            <?php endif; ?>

            <button id="dark-toggle" title="Toggle Theme"
                class="group p-2 rounded-xl hover:bg-slate-900 hover:text-white transition-all duration-200">
                <svg class="w-5 h-5 text-slate-400 block dark:hidden group-hover:scale-125 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                </svg>
                <svg class="w-5 h-5 text-secondary-400 hidden dark:block group-hover:scale-125 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>

        <span class="text-slate-800 font-bold">|</span>

        <div class="flex items-center pl-1">
            <?php if ($isLoggedIn): ?>
                <div class="relative group flex items-center gap-2">
                    <a href="<?= $baseUrl ?>logout" data-logout-button title="Sign out"
                        class="flex items-center gap-3 rounded-xl px-3 py-1.5 bg-slate-900 border border-slate-800 hover:border-red-900/60 hover:bg-red-950/40 text-slate-200 hover:text-red-400 transition-all duration-200">
                        <div class="h-6 w-6 rounded-full border border-secondary-400 bg-black flex items-center justify-center text-secondary-400 font-black text-xs shrink-0 group-hover:scale-110 transition-transform shadow-inner">
                            <?= htmlspecialchars($initial ?? 'U') ?>
                        </div>
                        <span class="hidden lg:inline max-w-[120px] truncate text-slate-300 group-hover:text-red-300 transition-colors font-bold"><?= htmlspecialchars($displayName) ?></span>
                        <span class="text-xs uppercase tracking-wider font-black opacity-90 group-hover:opacity-100">Sign Out</span>
                    </a>
                </div>
            <?php else: ?>
                <div class="relative group">
                    <a href="<?= $baseUrl ?>login" data-login-button title="Sign in"
                        class="flex items-center gap-2 rounded-xl px-4 py-2 bg-secondary-600 hover:bg-secondary-500 text-white shadow-md transition-all duration-200 transform hover:-translate-y-0.5">
                        <div class="h-5 w-5 rounded-full bg-white/20 flex items-center justify-center text-white font-black shrink-0 text-xs">G</div>
                        <span class="uppercase tracking-widest text-xs font-black">Sign In</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>
<?php
// /resources/views/partials/layout-footer.php

declare(strict_types=1);

/** @var string $assetBase */
/** @var string $appName */
/** @var string $baseUrl */
?>

<!-- Secondary Brand Theme Footer Terminal Grid -->
<footer class="bg-black border-t border-slate-900 pt-16 pb-10 mt-auto transition-colors duration-300 text-slate-400 font-medium">
    <div class="max-w-7xl mx-auto px-6 sm:px-8 lg:px-12">

        <!-- Main Structured Directory Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-10 lg:gap-8 pb-12 border-b border-slate-900">

            <!-- Column 1: Corporate Profile Identity (Span 5) -->
            <div class="space-y-5 lg:col-span-5">
                <div class="flex items-center space-x-3 group/logo">
                    <span class="font-black text-white tracking-tight text-lg"><?= htmlspecialchars($appName) ?></span>
                </div>
                <p class="text-xs text-slate-400 dark:text-slate-500 max-w-sm leading-relaxed font-normal">
                    Professional asset preservation and residential management throughout the premium corridors of Simcoe County.
                </p>
                <div class="space-y-3 text-xs pt-1">
                    <a href="tel:1-866-709-9416" class="inline-flex items-center gap-2.5 text-slate-200 hover:text-primary-400 transition-colors group/link">
                        <svg class="w-3.5 h-3.5 shrink-0 text-slate-500 group-hover/link:text-primary-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <span class="font-bold tracking-tight">1-866-709-9416</span>
                    </a>
                    <div class="flex items-start gap-2.5 text-slate-400">
                        <svg class="w-3.5 h-3.5 shrink-0 text-slate-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <p class="leading-relaxed font-normal">
                            137 Essa Rd Unit 1<br />
                            Barrie, ON L4N 3K8
                        </p>
                    </div>
                </div>
            </div>

            <!-- Column 2: Corporate Ecosystem (Span 3) -->
            <div class="lg:col-span-3 lg:pl-8">
                <h3 class="text-[11px] font-black text-white uppercase tracking-[0.2em] mb-4">Ecosystem</h3>
                <div class="flex flex-col gap-3 text-xs">
                    <?php foreach (['Home' => '', 'About' => 'about', 'Contact' => 'contact', 'FAQs' => 'faqs'] as $title => $slug): ?>
                        <a href="<?= $baseUrl . $slug ?>" data-partial data-title="<?= $title ?>" class="relative text-slate-400 hover:text-white transition-colors duration-200 w-fit before:absolute before:-bottom-0.5 before:left-0 before:w-0 before:h-px before:bg-primary-500 hover:before:w-full before:transition-all before:duration-300">
                            <?= $title ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Column 3: Premium Integrations & Portals (Span 4) -->
            <div class="space-y-5 lg:col-span-4 lg:pl-4">
                <div>
                    <h3 class="text-[11px] font-black text-white uppercase tracking-[0.2em] mb-3.5">Connect</h3>
                    <div class="flex items-center gap-2.5">
                        <a target="_blank" rel="noopener" href="https://www.facebook.com/PropertyManagementBrokers" class="w-8 h-8 rounded-lg bg-slate-900 hover:bg-slate-800 text-slate-400 hover:text-white border border-slate-800 flex items-center justify-center transition-all duration-200 hover:-translate-y-0.5 shadow-sm" title="Facebook">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                                <path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" />
                            </svg>
                        </a>
                        <a target="_blank" rel="noopener" href="https://www.instagram.com/propertymanagementbrokers/" class="w-8 h-8 rounded-lg bg-slate-900 hover:bg-slate-800 text-slate-400 hover:text-white border border-slate-800 flex items-center justify-center transition-all duration-200 hover:-translate-y-0.5 shadow-sm" title="Instagram">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.051.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

        </div>

        <!-- System Architecture & Trademark Alignment Base -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-8 text-[11px] text-slate-500 font-normal">
            <div class="tracking-tight">
                &copy; <?= date('Y') ?> <span class="font-bold text-slate-300"><?= htmlspecialchars($appName) ?></span> &bull; All rights reserved.
            </div>

            <div class="flex items-center gap-1.5 opacity-60 hover:opacity-100 transition-opacity duration-300">
                <span class="font-black uppercase tracking-[0.2em] text-[9px] text-slate-500">
                    A CatScript Application
                </span>
            </div>
        </div>

    </div>
</footer>
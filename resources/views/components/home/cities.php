<?php
// /resources/views/components/home/cities.php

declare(strict_types=1);

/** @var string $assetBase */
/** @var string $appName */

?>

<!-- Explore Cities Section -->
<section class="py-20 px-6 sm:px-12 lg:px-24 xl:px-32 bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 transition-colors duration-200">
    <div class="max-w-7xl mx-auto">

        <div class="text-center mb-12" data-aos="fade-up">
            <p class="uppercase tracking-[0.2em] text-[10px] font-black text-secondary-600 dark:text-secondary-400 mb-2">
                Explore Our Cities
            </p>
            <h2 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">
                Find Us At These Cities!
            </h2>
        </div>

        <div class="grid sm:grid-cols-2 gap-8 max-w-4xl mx-auto" data-aos="fade-up" data-aos-delay="100">

            <!-- City 1: Barrie -->
            <div class="group relative overflow-hidden rounded-2xl h-72 shadow-lg border border-gray-100 dark:border-gray-800/50">
                <img src="<?= $assetBase ?>images/home/barrie.jpg" alt="Barrie Waterfront" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                <div class="absolute inset-0 bg-gradient-to-t from-gray-950/80 via-gray-950/20 to-transparent transition-opacity duration-300 group-hover:via-gray-950/40"></div>
                <div class="absolute inset-0 flex flex-col justify-end p-6 z-10">
                    <a href="javascript:" class="inline-block">
                        <h3 class="text-2xl font-black text-white tracking-tight group-hover:text-secondary-400 transition-colors duration-200 flex items-center gap-2">
                            Barrie
                            <svg class="w-5 h-5 opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </h3>
                    </a>
                </div>
            </div>

            <!-- City 2: Orillia -->
            <div class="group relative overflow-hidden rounded-2xl h-72 shadow-lg border border-gray-100 dark:border-gray-800/50">
                <img src="<?= $assetBase ?>images/home/orillia.jpg" alt="Orillia Waterfront" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                <div class="absolute inset-0 bg-gradient-to-t from-gray-950/80 via-gray-950/20 to-transparent transition-opacity duration-300 group-hover:via-gray-950/40"></div>
                <div class="absolute inset-0 flex flex-col justify-end p-6 z-10">
                    <a href="javascript:" class="inline-block">
                        <h3 class="text-2xl font-black text-white tracking-tight group-hover:text-secondary-400 transition-colors duration-200 flex items-center gap-2">
                            Orillia
                            <svg class="w-5 h-5 opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </h3>
                    </a>
                </div>
            </div>

        </div>

    </div>
</section>
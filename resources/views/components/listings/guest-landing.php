<?php
// /resources/views/components/listings/guest-landing.php

declare(strict_types=1);

/** @var string $assetBase */
/** @var string $baseUrl */

// Mock data for guest view
$categories = ['All', 'Electronics', 'Furniture', 'Fashion', 'Hobbies', 'Gifts'];
$listings = [
    ['title' => 'Vintage Camera', 'type' => 'Swap', 'price' => 'Trade Only', 'img' => '1.webp', 'loc' => 'Downtown'],
    ['title' => 'Gaming Console', 'type' => 'Sale', 'price' => '$250', 'img' => '2.jpg', 'loc' => 'North End'],
    ['title' => 'Designer Sofa', 'type' => 'Gift', 'price' => 'Free', 'img' => '3.jpg', 'loc' => 'Westside'],
    ['title' => 'Mountain Bike', 'type' => 'Swap', 'price' => 'Trade Only', 'img' => '4.jpg', 'loc' => 'East Side'],
    ['title' => 'Vinyl Records', 'type' => 'Sale', 'price' => '$40', 'img' => '5.jpg', 'loc' => 'South Side'],
    ['title' => 'Smart Watch', 'type' => 'Swap', 'price' => 'Trade Only', 'img' => '1.webp', 'loc' => 'Midtown'],
];
?>

<div class="max-w-7xl mx-auto px-6 lg:px-10 py-12 font-sans">

    <header class="mb-12" data-aos="fade-down">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-500/10 text-primary-500 text-[10px] font-black uppercase tracking-widest mb-4 border border-primary-500/20">
            Browse Market
        </div>
        <h1 class="text-4xl lg:text-6xl font-black text-secondary-900 dark:text-white leading-none tracking-tighter">
            Current <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-500 to-orange-600">Listings</span>
        </h1>
    </header>

    <div class="flex flex-col lg:flex-row gap-6 mb-12 items-center justify-between" data-aos="fade-up">
        <div class="relative w-full lg:max-w-md group">
            <input type="text" placeholder="Search for items..."
                class="w-full pl-14 pr-6 py-5 bg-white dark:bg-secondary-950 border-2 border-gray-100 dark:border-white/5 rounded-2xl text-secondary-900 dark:text-white font-bold focus:border-primary-500 outline-none transition-all shadow-lg shadow-gray-200/50 dark:shadow-none">
            <svg class="absolute left-6 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 group-focus-within:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>

        <div class="flex items-center gap-3 overflow-x-auto pb-2 w-full lg:w-auto no-scrollbar">
            <?php foreach ($categories as $cat): ?>
                <button class="px-6 py-3 rounded-xl whitespace-nowrap text-xs font-black uppercase tracking-widest transition-all <?= $cat === 'All' ? 'bg-secondary-900 text-white shadow-lg' : 'bg-gray-100 dark:bg-white/5 text-gray-500 hover:bg-primary-500 hover:text-secondary-950' ?>">
                    <?= $cat ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 mb-20">
        <?php foreach ($listings as $item): ?>
            <div data-aos="fade-up" class="group relative bg-white dark:bg-secondary-950 rounded-[2.5rem] overflow-hidden border border-gray-100 dark:border-white/5 hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">

                <div class="aspect-[4/3] overflow-hidden relative">
                    <img src="<?= $assetBase ?>images/home/<?= $item['img'] ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 opacity-90 group-hover:opacity-100" alt="<?= $item['title'] ?>">

                    <div class="absolute top-5 left-5 flex flex-col gap-2">
                        <span class="px-4 py-1.5 bg-secondary-950/80 backdrop-blur-md text-white text-[10px] font-black uppercase tracking-widest rounded-lg">
                            <?= $item['loc'] ?>
                        </span>
                        <span class="px-4 py-1.5 <?= $item['type'] === 'Swap' ? 'bg-primary-500 text-secondary-950' : 'bg-white text-secondary-950' ?> text-[10px] font-black uppercase tracking-widest rounded-lg shadow-xl">
                            <?= $item['type'] ?>
                        </span>
                    </div>
                </div>

                <div class="p-8">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-2xl font-black text-secondary-900 dark:text-white tracking-tight leading-none group-hover:text-primary-500 transition-colors">
                            <?= $item['title'] ?>
                        </h3>
                        <span class="text-sm font-black text-secondary-900 dark:text-primary-500"><?= $item['price'] ?></span>
                    </div>

                    <div class="flex items-center gap-4 mb-8 text-gray-400 dark:text-gray-500">
                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span class="text-[11px] font-bold uppercase">2h ago</span>
                        </div>
                        <div class="w-1 h-1 rounded-full bg-gray-300"></div>
                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span class="text-[11px] font-bold uppercase">3km away</span>
                        </div>
                    </div>

                    <a href="<?= $baseUrl ?>login"
                        class="block w-full text-center py-4 bg-gray-100 dark:bg-white/5 hover:bg-primary-500 dark:hover:bg-primary-500 text-secondary-900 dark:text-white hover:text-secondary-950 font-black uppercase tracking-widest text-xs rounded-2xl transition-all">
                        View Details
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </section>

    <section class="bg-primary-500 rounded-[3rem] p-12 text-center relative overflow-hidden" data-aos="zoom-in">
        <div class="relative z-10">
            <h2 class="text-3xl lg:text-5xl font-black text-secondary-950 mb-4 tracking-tighter">Ready to join the swap?</h2>
            <p class="text-secondary-900/70 text-lg font-medium mb-8 max-w-xl mx-auto">Create an account to list your own items, chat with owners, and start trading today.</p>
            <a href="javascript:" class="register-btn inline-flex px-12 py-5 bg-secondary-950 text-white rounded-2xl font-black text-xl hover:-translate-y-1 transition-all shadow-2xl">
                Get Started Now
            </a>
        </div>
        <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/20 rounded-full blur-3xl"></div>
    </section>
</div>

<style>
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }

    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
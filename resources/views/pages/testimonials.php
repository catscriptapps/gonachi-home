<?php
// /resources/views/pages/testimonials.php

// Extracted testimonial records from legacy markup
$testimonials = [
    [
        'id'      => 4437,
        'name'    => 'Samantha G.',
        'date'    => 'December 28, 2014',
        'rating'  => 5,
        'content' => 'Amazing and professional!'
    ],
    [
        'id'      => 4436,
        'name'    => 'Tony A.',
        'date'    => 'September 10, 2016',
        'rating'  => 5,
        'content' => "I've worked with Richard on various properties and he always provides excellent client service. He's always professional and gets the job done. I highly recommend his services."
    ],
    [
        'id'      => 4435,
        'name'    => 'Donna',
        'date'    => 'November 18, 2014',
        'rating'  => 5,
        'content' => 'I am writing to express my sincere appreciation for the first-class service that was provided to me by you and your staff. Your professional, courteous, friendly and attentive to all my requests and concerns. Such professionalism and good work ethics are rare nowadays. Thank you Richard, keep up the excellent work!'
    ],
    [
        'id'      => 4434,
        'name'    => 'Monica',
        'date'    => 'February 04, 2018',
        'rating'  => 5,
        'content' => 'Richard is the best. He helped me and my boyfriend get our first place, and had no biases. He is down to earth and always helped with any questions. Best company ever and the other staff I met was super nice too. Wish more people like them were in the real estate market, they really know what they are doing, and they do it well.'
    ],
    [
        'id'      => 4433,
        'name'    => 'Alanna & Jennifer',
        'date'    => 'January 01, 2016',
        'rating'  => 0, // No rating block rendered in legacy markup for this item
        'content' => 'We cannot thank you enough for your kindness and care over the last two years. You have been an incredible manager and we will miss you greatly. If you ever need a recommendation on the quality of your management skills please do not hesitate to ask. We have nothing but good praises to sing about you and the only reason we are leaving this here is because it will allow us to start saving for our dream of owning our own home again'
    ],
    [
        'id'      => 4432,
        'name'    => 'Randy G.',
        'date'    => 'February 06, 2015',
        'rating'  => 0, // No rating block rendered in legacy markup for this item
        'content' => 'I wanted to convert my investment into a legal two-unit house, so I hired Richard and his team to take charge of this project for me. With his knowledge of who to talk to at City Hall to move this forward, it seemed almost effortless.'
    ]
];
?>

<div
    class="min-h-screen bg-white dark:bg-black text-slate-800 dark:text-slate-100 font-sans transition-colors duration-300"
    x-data="{ activeModal: null }"
    @keydown.escape.window="activeModal = null">

    <!-- High-Fidelity Hero Header Matrix -->
    <section class="relative overflow-hidden bg-gradient-to-b from-primary-300 via-white to-primary-200 text-slate-800 dark:from-slate-950 dark:via-black dark:to-slate-950 py-20 lg:py-24 px-6 sm:px-12 lg:px-24 xl:px-32 transition-colors duration-300 border-b border-slate-200 dark:border-slate-800 relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen max-w-[100vw]">
        <!-- Alignment Matrix Grid Lines -->
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#00000003_1px,transparent_1px),linear-gradient(to_bottom,#00000003_1px,transparent_1px)] dark:bg-[linear-gradient(to_right,#ffffff03_1px,transparent_1px),linear-gradient(to_bottom,#ffffff03_1px,transparent_1px)] bg-[size:32px_32px] pointer-events-none"></div>

        <!-- Ambient Chromatic Radial Blur Sinks -->
        <div class="absolute -top-40 -right-20 w-[500px] h-[500px] bg-primary-500/[0.05] dark:bg-primary-500/[0.08] rounded-full blur-[140px] pointer-events-none"></div>
        <div class="absolute -bottom-40 -left-20 w-[500px] h-[500px] bg-secondary-500/[0.03] dark:bg-secondary-500/[0.05] rounded-full blur-[140px] pointer-events-none"></div>

        <div class="relative z-10 max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            <div class="flex flex-col space-y-5 lg:col-span-8" data-aos="fade-right" data-aos-duration="800">
                <div class="inline-flex items-center gap-2">
                    <span class="h-1.5 w-1.5 rounded-full bg-primary-500 dark:bg-primary-400 animate-pulse"></span>
                    <p class="uppercase tracking-[0.25em] text-[10px] font-black text-primary-600 dark:text-primary-400">
                        Historical Ledger Records
                    </p>
                </div>

                <h1 class="text-3xl sm:text-4xl xl:text-5xl font-black text-slate-900 dark:text-white tracking-tight leading-tight uppercase font-sans">
                    Ecosystem Trust <br />
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-600 via-primary-500 to-indigo-600 dark:from-primary-400 dark:via-primary-300 dark:to-secondary-400">
                        & Verified Output Logs
                    </span>
                </h1>

                <p class="text-slate-600 dark:text-slate-400 max-w-xl font-medium leading-relaxed text-sm sm:text-base">
                    Read unedited feedback loops and compliance testimonials from asset operators, property owners, and tenants managing active subscriptions within the PMB architecture matrix.
                </p>
            </div>

            <!-- Dynamic System Metadata Display -->
            <div class="hidden lg:block lg:col-span-4 bg-slate-100/80 dark:bg-secondary-900/60 border border-slate-200 dark:border-secondary-800 shadow-xl rounded-2xl p-6 backdrop-blur-sm" data-aos="fade-left" data-aos-duration="800" data-aos-delay="150">
                <div class="space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-200 dark:border-secondary-800">
                        <span class="text-[10px] font-black text-slate-500 dark:text-secondary-500 uppercase tracking-widest">Aggregate Rating</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-white text-primary-600 dark:bg-primary-950/50 dark:text-primary-400 border border-slate-200 dark:border-primary-900/50">Verified Sinks</span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">5.0</span>
                        <span class="text-xs font-bold text-slate-400 dark:text-secondary-500">/ Core Operations</span>
                    </div>
                    <div class="flex items-center gap-1 text-amber-500">
                        <?php for ($i = 0; $i < 5; $i++): ?>
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Structural Grid System -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-10">
            <?php
            foreach ($testimonials as $index => $item):
                $delay = $index * 100;
                $isLongText = strlen($item['content']) > 220;
                $displaySummary = $isLongText ? substr($item['content'], 0, 190) . '...' : $item['content'];

                // Toggle card style properties based on specific item signatures
                $hasStars = $item['rating'] > 0;
            ?>
                <div
                    class="group relative flex flex-col justify-between bg-white dark:bg-slate-900/40 border-2 border-slate-200/80 dark:border-slate-800/80 rounded-[2rem] p-7 sm:p-8 transition-all duration-300 hover:border-primary-500/50 dark:hover:border-primary-400/50 hover:bg-slate-50/50 dark:hover:bg-slate-900/80 shadow-sm"
                    data-aos="fade-up"
                    data-aos-delay="<?= $delay; ?>"
                    data-aos-duration="700"
                    data-aos-once="true">

                    <div>
                        <!-- System Identity Top Row Matrices -->
                        <div class="flex items-center justify-between gap-4 mb-6">
                            <div class="w-10 h-10 rounded-xl bg-primary-500/10 dark:bg-primary-500/5 flex items-center justify-center border border-primary-500/20 text-primary-600 dark:text-primary-400">
                                <i class="fa-solid <?= $hasStars ? 'fa-quote-left' : 'fa-sliders' ?> text-sm"></i>
                            </div>

                            <?php if ($hasStars): ?>
                                <div class="flex items-center gap-0.5 text-amber-500 dark:text-amber-500 bg-amber-500/5 px-2.5 py-1 rounded-lg border border-amber-500/10">
                                    <?php for ($i = 0; $i < $item['rating']; $i++): ?>
                                        <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    <?php endfor; ?>
                                </div>
                            <?php else: ?>
                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-secondary-500/10 text-secondary-600 dark:bg-secondary-950/40 dark:text-secondary-400 border border-secondary-500/20">
                                    Portfolio Case
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Main Content Context Template Block -->
                        <div class="text-sm text-slate-600 dark:text-slate-300 font-medium leading-relaxed italic mb-8">
                            &ldquo;<?= htmlspecialchars($displaySummary); ?>&rdquo;
                        </div>
                    </div>

                    <!-- Footer Details Panel -->
                    <div class="pt-5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-sm font-black uppercase tracking-tight text-slate-900 dark:text-white">
                                <?= htmlspecialchars($item['name']); ?>
                            </h3>
                            <p class="text-[11px] font-bold text-slate-400 dark:text-secondary-500 mt-0.5 font-sans">
                                <?= htmlspecialchars($item['date']); ?>
                            </p>
                        </div>

                        <?php if ($isLongText): ?>
                            <button
                                type="button"
                                @click="activeModal = <?= $item['id']; ?>"
                                class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white dark:bg-primary-500 dark:hover:bg-primary-600 dark:text-slate-950 font-black uppercase text-[10px] tracking-wider transition-all duration-200 shadow-sm">
                                Read Node
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- Dynamic Alpine Modal Pipeline -->
                    <?php if ($isLongText): ?>
                        <template x-if="activeModal === <?= $item['id']; ?>">
                            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true">

                                <!-- Backdrop Modal Underlay Blur -->
                                <div
                                    class="fixed inset-0 bg-slate-950/60 backdrop-blur-md transition-opacity"
                                    @click="activeModal = null"
                                    x-transition:enter="ease-out duration-300"
                                    x-transition:enter-start="opacity-0"
                                    x-transition:enter-end="opacity-100"
                                    x-transition:leave="ease-in duration-200"
                                    x-transition:leave-start="opacity-100"
                                    x-transition:leave-end="opacity-0"></div>

                                <!-- Dialog Panel Content Box -->
                                <div
                                    class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl overflow-y-auto max-h-[85vh]"
                                    x-transition:enter="ease-out duration-300"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="ease-in duration-200"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95">

                                    <!-- Close Trigger Action Button -->
                                    <button
                                        type="button"
                                        @click="activeModal = null"
                                        class="absolute top-5 right-5 w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 border border-slate-200 dark:border-slate-700 transition-colors"
                                        aria-label="Close modal">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>

                                    <!-- Internal Content Render Section -->
                                    <div class="mt-4 space-y-5">
                                        <?php if ($hasStars): ?>
                                            <div class="inline-flex items-center gap-0.5 text-amber-500 bg-amber-500/5 px-2.5 py-1 rounded-lg border border-amber-500/10">
                                                <?php for ($i = 0; $i < $item['rating']; $i++): ?>
                                                    <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                <?php endfor; ?>
                                            </div>
                                        <?php endif; ?>

                                        <p class="text-base text-slate-700 dark:text-slate-300 font-medium italic leading-relaxed">
                                            &ldquo;<?= htmlspecialchars($item['content']); ?>&rdquo;
                                        </p>

                                        <div class="pt-5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                                            <div>
                                                <h4 class="text-sm font-black uppercase tracking-tight text-slate-900 dark:text-white">
                                                    <?= htmlspecialchars($item['name']); ?>
                                                </h4>
                                                <p class="text-xs text-slate-400 dark:text-secondary-500 mt-0.5">
                                                    <?= htmlspecialchars($item['date']); ?>
                                                </p>
                                            </div>
                                            <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-600">
                                                ID: #<?= $item['id'] ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </template>
                    <?php endif; ?>

                </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>
<?php
// /resources/views/pages/contact.php
//
// Redesigned to match the marketing/landing-page visual language used by
// about.php — full-bleed gradient hero with a faint grid overlay + blurred
// color blobs, an eyebrow dot + uppercase kicker, and a gradient-text H1 —
// rather than the generic dashboard-form look this page had before. The
// form itself (field names/ids) is unchanged so resources/js/pages/
// contact-page.js keeps working without modification.

declare(strict_types=1);
?>

<!-- Hero Section -->
<section class="relative overflow-hidden bg-gradient-to-b from-primary-100 via-white to-white dark:from-gray-950 dark:via-black dark:to-gray-950 py-10 lg:py-12 px-6 sm:px-12 lg:px-24 xl:px-32 transition-colors duration-300 border-b border-gray-200 dark:border-gray-800 left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen max-w-[100vw]">
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#00000003_1px,transparent_1px),linear-gradient(to_bottom,#00000003_1px,transparent_1px)] dark:bg-[linear-gradient(to_right,#ffffff03_1px,transparent_1px),linear-gradient(to_bottom,#ffffff03_1px,transparent_1px)] bg-[size:32px_32px] pointer-events-none"></div>
    <div class="absolute -top-40 -right-20 w-[500px] h-[500px] bg-primary-500/[0.04] dark:bg-primary-500/[0.07] rounded-full blur-[140px] pointer-events-none"></div>
    <div class="absolute -bottom-40 -left-20 w-[500px] h-[500px] bg-secondary-500/[0.02] dark:bg-secondary-500/[0.04] rounded-full blur-[140px] pointer-events-none"></div>

    <div class="relative z-10 max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

        <div class="flex flex-col space-y-5 lg:col-span-7">
            <div class="inline-flex items-center gap-2">
                <span class="h-1.5 w-1.5 rounded-full bg-primary-500 dark:bg-primary-400 animate-pulse"></span>
                <p class="uppercase tracking-[0.25em] text-[10px] font-black text-primary-600 dark:text-primary-400">Get In Touch</p>
            </div>

            <h1 class="text-3xl sm:text-4xl xl:text-5xl font-black text-gray-900 dark:text-white tracking-tight leading-tight uppercase font-sans">
                We'd Love To <br />
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-600 via-primary-500 to-indigo-600 dark:from-primary-400 dark:via-primary-300 dark:to-secondary-400">
                    Hear From You
                </span>
            </h1>

            <p class="text-gray-600 dark:text-gray-400 max-w-xl font-medium leading-relaxed">
                Have a question about Gonachi, need a hand with your account, or just want to say hello? Our team reads every message and typically replies within a day.
            </p>
        </div>

        <div class="hidden lg:block lg:col-span-5 bg-gray-100/80 dark:bg-gray-900/60 border border-gray-200 dark:border-gray-800 shadow-xl rounded-2xl p-6 backdrop-blur-sm">
            <div class="space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-800">
                    <span class="text-[11px] font-black text-gray-500 dark:text-gray-500 uppercase tracking-widest">Direct Channel</span>
                    <span class="relative flex h-2.5 w-2.5" title="Operational">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                    </span>
                </div>

                <a href="mailto:info@gonachi.com" class="block text-gray-900 dark:text-white font-bold text-lg hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                    info@gonachi.com
                </a>
                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium leading-relaxed">
                    API nodes are 100% operational and the outbound mail queue is healthy.
                </p>

                <div class="grid grid-cols-2 gap-3 pt-2">
                    <div class="p-3 rounded-xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800">
                        <span class="text-[10px] text-gray-400 dark:text-gray-500 block uppercase font-bold">Response Time</span>
                        <span class="text-sm font-black text-gray-900 dark:text-white font-sans">&lt; 24 Hours</span>
                    </div>
                    <div class="p-3 rounded-xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800">
                        <span class="text-[10px] text-gray-400 dark:text-gray-500 block uppercase font-bold">Coverage</span>
                        <span class="text-sm font-black text-primary-600 dark:text-primary-400 font-sans">5 Engines</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- Message Form -->
<section class="py-16 lg:py-20 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">

        <?php
        $breadcrumbs = ['Contact' => $baseUrl . 'contact'];
        include __DIR__ . '/../components/ui/breadcrumbs.php';
        ?>

        <div class="bg-white dark:bg-gray-900 p-8 lg:p-10 rounded-[2.5rem] border-2 border-gray-200/80 dark:border-gray-800/80 shadow-sm">

            <div class="flex items-center gap-4 mb-8">
                <div class="w-14 h-14 rounded-2xl bg-primary-500/10 dark:bg-primary-500/5 flex items-center justify-center border border-primary-500/20 text-primary-600 dark:text-primary-400 flex-shrink-0">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-black uppercase tracking-[0.2em] text-secondary-600 dark:text-secondary-400">Send A Message</span>
                    <h2 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Tell Us What's On Your Mind</h2>
                </div>
            </div>

            <form id="contact-form" class="grid grid-cols-1 gap-7" novalidate>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-7">
                    <div class="group">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 ml-1 mb-2 block">Full Name</label>
                        <input type="text" name="full_name"
                            class="w-full px-5 py-4 rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all outline-none font-semibold text-sm placeholder-gray-400"
                            placeholder="Alex Rivera" required>
                    </div>

                    <div class="group">
                        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 ml-1 mb-2 block">Email Address</label>
                        <input type="email" name="email"
                            class="w-full px-5 py-4 rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all outline-none font-semibold text-sm placeholder-gray-400"
                            placeholder="alex@company.com" required>
                    </div>
                </div>

                <div class="group">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 ml-1 mb-2 block">Subject</label>
                    <input type="text" name="subject"
                        class="w-full px-5 py-4 rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all outline-none font-semibold text-sm placeholder-gray-400"
                        placeholder="What's this about?" required>
                </div>

                <div class="group">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 ml-1 mb-2 block">Message</label>
                    <textarea name="message" rows="5"
                        class="w-full px-5 py-4 rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50 text-gray-900 dark:text-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 transition-all outline-none resize-none font-semibold text-sm placeholder-gray-400"
                        placeholder="How can we help you today?" required></textarea>
                </div>

                <div class="pt-4">
                    <button type="submit" id="contact-submit"
                        class="group w-full sm:w-auto inline-flex items-center justify-center py-4 px-10 rounded-xl shadow-lg shadow-primary-500/20 text-white bg-primary-600 hover:bg-primary-700 transition-all duration-300 font-black uppercase tracking-widest text-xs active:scale-[0.97]">
                        <span class="flex items-center gap-3">
                            Send Message
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

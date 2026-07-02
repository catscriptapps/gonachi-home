<?php
// /resources/views/pages/inspections.php

declare(strict_types=1);

/** @var string $assetBase */
/** @var string $appName */
/** @var string $baseUrl */

?>

<section class="relative overflow-hidden bg-slate-50 dark:bg-slate-950 py-16 lg:py-24 px-6 sm:px-12 lg:px-24 xl:px-32 transition-colors duration-300 w-screen left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] border-b border-slate-200/60 dark:border-slate-900">
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#00000003_1px,transparent_1px),linear-gradient(to_bottom,#00000003_1px,transparent_1px)] dark:bg-[linear-gradient(to_right,#ffffff02_1px,transparent_1px),linear-gradient(to_bottom,#ffffff02_1px,transparent_1px)] bg-[size:20px_30px] pointer-events-none"></div>

    <div class="absolute -top-12 -right-12 w-96 h-96 bg-primary-500/10 dark:bg-primary-500/5 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute -bottom-12 -left-12 w-96 h-96 bg-secondary-500/[0.04] dark:bg-secondary-500/[0.02] rounded-full blur-[120px] pointer-events-none"></div>

    <div class="relative z-10 max-w-7xl mx-auto grid lg:grid-cols-12 gap-12 items-center">
        <div class="flex flex-col space-y-4 lg:col-span-7">
            <div class="inline-flex items-center gap-2">
                <span class="h-1.5 w-1.5 rounded-full bg-secondary-500 animate-pulse"></span>
                <p class="uppercase tracking-[0.25em] text-[10px] font-black text-secondary-600 dark:text-secondary-400">
                    Real-Time Property Verification
                </p>
            </div>

            <h1 class="text-3xl sm:text-4xl xl:text-5xl font-black text-slate-900 dark:text-white tracking-tight leading-tight">
                High-Resolution <br />
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-600 via-secondary-500 to-primary-700 dark:from-primary-400 dark:via-secondary-400 dark:to-primary-600">
                    Asset Inspection Suite
                </span>
            </h1>

            <p class="text-slate-600 dark:text-slate-400 max-w-xl text-xs sm:text-sm font-medium leading-relaxed">
                Generate highly detailed, structured multi-point maintenance records effortlessly. Dynamically map structural sections, append rich telemetry media, and instantly execute situational tracking blueprints.
            </p>

            <div class="pt-4 flex flex-wrap gap-4">
                <a href="#subscription-matrix" class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 text-white font-black text-xs shadow-lg shadow-primary-500/20 transition-all duration-300 font-sans tracking-wider uppercase">
                    Subscribe to Module
                </a>
                <a href="#structural-matrix" class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-slate-200/80 hover:bg-slate-200 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-800 dark:text-slate-200 font-bold text-xs transition-all duration-300 font-sans">
                    View Verification Protocols
                </a>
            </div>
        </div>

        <div class="hidden lg:block lg:col-span-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-2xl p-6 backdrop-blur-sm relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-24 h-24 bg-primary-500/[0.03] rounded-bl-full pointer-events-none"></div>
            <div class="space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Inspection Node</span>
                    <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 border border-primary-100 dark:border-primary-900/40">Guest Mode</span>
                </div>

                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-900/60 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Current Template</span>
                        <span class="text-[10px] font-black text-primary-600 dark:text-primary-400 uppercase">Move-In Protocol</span>
                    </div>
                    <div class="h-1.5 w-full bg-slate-200 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full w-2/3 bg-primary-500 rounded-full"></div>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2 text-center">
                    <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-900">
                        <span class="text-[9px] text-slate-400 dark:text-slate-500 block uppercase font-bold">Sections</span>
                        <span class="text-xs font-black text-slate-800 dark:text-white font-sans">Dynamic</span>
                    </div>
                    <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-900">
                        <span class="text-[9px] text-slate-400 dark:text-slate-500 block uppercase font-bold">Media Nodes</span>
                        <span class="text-xs font-black text-slate-800 dark:text-white font-sans">HD Video</span>
                    </div>
                    <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-900 group-hover:border-primary-500/30 transition-colors duration-300">
                        <span class="text-[9px] text-secondary-600 dark:text-secondary-400 block uppercase font-bold">Telemetry</span>
                        <span class="text-xs font-black text-secondary-600 dark:text-secondary-400 font-sans">Synced</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="structural-matrix" class="py-20 px-6 sm:px-12 lg:px-24 xl:px-32 bg-primary-50 dark:bg-slate-950 transition-colors duration-300 w-screen left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] relative overflow-hidden">
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#00000003_1px,transparent_1px),linear-gradient(to_bottom,#00000003_1px,transparent_1px)] dark:bg-[linear-gradient(to_right,#ffffff02_1px,transparent_1px),linear-gradient(to_bottom,#ffffff02_1px,transparent_1px)] bg-[size:20px_30px] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto space-y-10 relative z-10">
        <div class="flex flex-col space-y-2 border-b border-slate-200/60 dark:border-white/5 pb-6">
            <h2 class="text-xs font-black uppercase tracking-[0.3em] text-primary-700 dark:text-primary-400 px-2 animate-in fade-in delay-500">
                Workspace Hub
            </h2>
            <p class="text-xl font-black text-slate-800 dark:text-slate-100 tracking-tight px-2">Core Pipeline Blueprints</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="group relative overflow-hidden rounded-[2rem] bg-slate-900 dark:bg-black p-8 sm:p-10 shadow-xl border border-slate-800 dark:border-slate-900 transition-all duration-300 hover:shadow-[0_25px_60px_-15px_rgba(var(--primary-rgb),0.12)] hover:-translate-y-1.5 hover:border-primary-500/40">
                <div class="absolute inset-0 bg-gradient-to-br from-white/[0.02] via-transparent to-primary-500/[0.03] opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                <div class="flex items-center justify-between relative z-10">
                    <div class="rounded-xl bg-slate-800 dark:bg-slate-900 border border-slate-700/50 dark:border-slate-800/80 p-4 text-primary-400 group-hover:bg-primary-500 group-hover:text-slate-950 group-hover:border-primary-500 transition-all duration-500 group-hover:scale-105 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <span class="text-[9px] font-bold font-mono px-2.5 py-1 rounded bg-slate-800 text-slate-400 border border-slate-700">Interval Mode</span>
                </div>
                <div class="mt-10 relative z-10 space-y-2.5">
                    <h3 class="text-lg font-black tracking-tight text-slate-100 font-sans transition-colors duration-300 group-hover:text-primary-400">
                        6-Month Routine Protocol
                    </h3>
                    <p class="text-xs text-slate-400 leading-relaxed font-medium">
                        Mid-lease verification parameter layers. Specifically mapped to analyze general wear data structures, scheduled compliance checks, and structural safety parameters.
                    </p>
                </div>
                <div class="absolute -bottom-8 -right-8 text-slate-800 dark:text-slate-900 opacity-20 group-hover:opacity-15 group-hover:text-primary-500/10 transition-all duration-700 group-hover:scale-105 pointer-events-none">
                    <svg class="w-20 h-20 scale-[3.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-[2rem] bg-slate-900 dark:bg-black p-8 sm:p-10 shadow-xl border border-slate-800 dark:border-slate-900 transition-all duration-300 hover:shadow-[0_25px_60px_-15px_rgba(var(--primary-rgb),0.12)] hover:-translate-y-1.5 hover:border-primary-500/40">
                <div class="absolute inset-0 bg-gradient-to-br from-white/[0.02] via-transparent to-primary-500/[0.03] opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                <div class="flex items-center justify-between relative z-10">
                    <div class="rounded-xl bg-slate-800 dark:bg-slate-900 border border-slate-700/50 dark:border-slate-800/80 p-4 text-primary-400 group-hover:bg-primary-500 group-hover:text-slate-950 group-hover:border-primary-500 transition-all duration-500 group-hover:scale-105 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-3-3m0 0l3-3m-3 3h8m-13 1a9 9 0 1118 0 9 9 0 01-18 0z" />
                        </svg>
                    </div>
                    <span class="text-[9px] font-bold font-mono px-2.5 py-1 rounded bg-slate-800 text-slate-400 border border-slate-700">Inbound Node</span>
                </div>
                <div class="mt-10 relative z-10 space-y-2.5">
                    <h3 class="text-lg font-black tracking-tight text-slate-100 font-sans transition-colors duration-300 group-hover:text-primary-400">
                        Inbound Baseline Matrix
                    </h3>
                    <p class="text-xs text-slate-400 leading-relaxed font-medium">
                        Initial baseline initialization. Enforces mandatory component status checking, utility meter capturing fields, and comprehensive lockset authentication metrics.
                    </p>
                </div>
                <div class="absolute -bottom-8 -right-8 text-slate-800 dark:text-slate-900 opacity-20 group-hover:opacity-15 group-hover:text-primary-500/10 transition-all duration-700 group-hover:scale-105 pointer-events-none">
                    <svg class="w-20 h-20 scale-[3.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 16l-3-3m0 0l3-3m-3 3h8m-13 1a9 9 0 1118 0 9 9 0 01-18 0z" />
                    </svg>
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-[2rem] bg-slate-900 dark:bg-black p-8 sm:p-10 shadow-xl border border-slate-800 dark:border-slate-900 transition-all duration-300 hover:shadow-[0_25px_60px_-15px_rgba(var(--primary-rgb),0.12)] hover:-translate-y-1.5 hover:border-primary-500/40">
                <div class="absolute inset-0 bg-gradient-to-br from-white/[0.02] via-transparent to-primary-500/[0.03] opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                <div class="flex items-center justify-between relative z-10">
                    <div class="rounded-xl bg-slate-800 dark:bg-slate-900 border border-slate-700/50 dark:border-slate-800/80 p-4 text-primary-400 group-hover:bg-primary-500 group-hover:text-slate-950 group-hover:border-primary-500 transition-all duration-500 group-hover:scale-105 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 8l4 4m0 0l-4 4m4-4H3m13-5a9 9 0 11-12 0" />
                        </svg>
                    </div>
                    <span class="text-[9px] font-bold font-mono px-2.5 py-1 rounded bg-slate-800 text-slate-400 border border-slate-700">Outbound Node</span>
                </div>
                <div class="mt-10 relative z-10 space-y-2.5">
                    <h3 class="text-lg font-black tracking-tight text-slate-100 font-sans transition-colors duration-300 group-hover:text-primary-400">
                        Outbound Reconciliation
                    </h3>
                    <p class="text-xs text-slate-400 leading-relaxed font-medium">
                        Differential degradation checking. Side-by-side automated comparison nodes tracking structural variations, tenant accountability factors, and deposit claim mappings.
                    </p>
                </div>
                <div class="absolute -bottom-8 -right-8 text-slate-800 dark:text-slate-900 opacity-20 group-hover:opacity-15 group-hover:text-primary-500/10 transition-all duration-700 group-hover:scale-105 pointer-events-none">
                    <svg class="w-20 h-20 scale-[3.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 8l4 4m0 0l-4 4m4-4H3m13-5a9 9 0 11-12 0" />
                    </svg>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-20 px-6 sm:px-12 lg:px-24 xl:px-32 bg-slate-50 dark:bg-slate-950 border-t border-slate-100 dark:border-slate-900/60 transition-colors duration-200 overflow-hidden w-screen left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] relative">
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-secondary-500/[0.03] dark:bg-secondary-500/[0.01] rounded-full blur-[120px] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-12 items-center relative z-10">
        <div data-aos="fade-right" class="space-y-6">
            <span class="inline-block bg-secondary-50 dark:bg-secondary-950/50 text-secondary-700 dark:text-secondary-400 px-3 py-1 rounded-full text-xs font-black tracking-widest uppercase border border-secondary-200/30 dark:border-secondary-500/10">
                Core Operation Spec
            </span>

            <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Structured Field Auditing</h2>

            <p class="text-slate-600 dark:text-slate-400 leading-relaxed text-sm font-medium">
                Our application converts chaotic real-world physical verification pipelines into pristine data structures. Inspectors cleanly flag specific zones—such as kitchens, premium layouts, or exterior nodes—and inject granular descriptions instantly.
            </p>

            <ul class="space-y-3 text-slate-600 dark:text-slate-400 text-xs font-semibold list-none pl-0">
                <li class="flex items-center gap-2.5">
                    <span class="text-secondary-500 font-bold text-sm">✓</span>
                    <span>Zone Selection: Isolate structural fields (Living spaces, Basements, Mechanical Nodes).</span>
                </li>
                <li class="flex items-center gap-2.5">
                    <span class="text-secondary-500 font-bold text-sm">✓</span>
                    <span>Executive Summary: Formulate macro condition reports upon initialization.</span>
                </li>
                <li class="flex items-center gap-2.5">
                    <span class="text-secondary-500 font-bold text-sm">✓</span>
                    <span>Rich Content Pipeline: Stream multi-angle high-definition footage and timestamped data.</span>
                </li>
            </ul>
        </div>

        <div data-aos="fade-left" class="space-y-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-100 dark:border-slate-900 shadow-sm relative overflow-hidden group">
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-secondary-500/10 text-secondary-500 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <h4 class="font-black text-sm text-slate-900 dark:text-white">Multi-Media Processing Array</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-normal font-medium">
                            Bind raw physical verification assets directly onto the structural template block. Video feeds preserve itemized condition markers during walk-through cycles.
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-100 dark:border-slate-900 shadow-sm relative overflow-hidden group">
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-primary-500/10 text-primary-400 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <h4 class="font-black text-sm text-slate-900 dark:text-white">Granular Section Isolation</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-normal font-medium">
                            Isolate structural variables cleanly across distinct fields to minimize tracking bottlenecks inside complex townhouse portfolios.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="subscription-matrix" class="py-20 px-6 sm:px-12 lg:px-24 xl:px-32 bg-primary-100 dark:bg-black transition-colors duration-300 w-screen left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] relative overflow-hidden border-t border-primary-200 dark:border-slate-900">
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#00000002_1px,transparent_1px),linear-gradient(to_bottom,#00000002_1px,transparent_1px)] dark:bg-[linear-gradient(to_right,#ffffff01_1px,transparent_1px),linear-gradient(to_bottom,#ffffff01_1px,transparent_1px)] bg-[size:30px_30px] pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[32rem] h-[32rem] bg-secondary-500/[0.03] dark:bg-secondary-500/[0.02] rounded-full blur-[160px] pointer-events-none"></div>

    <div class="max-w-4xl mx-auto text-center relative z-10 space-y-8">
        <div class="flex flex-col items-center space-y-3">
            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-secondary-500 text-slate-950 shadow-sm shadow-secondary-500/10">
                Ecosystem Expansion
            </span>
            <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight sm:text-4xl">
                Unlock the Inspections Engine
            </h2>
            <p class="text-slate-600 dark:text-slate-400 text-xs sm:text-sm font-medium max-w-xl mx-auto leading-relaxed">
                Initialize full digital tracking parameters. Subscribing unlocks automated data fields, custom multi-unit section templates, and uncompressed media processing.
            </p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-primary-200/60 dark:border-slate-800 p-8 sm:p-12 shadow-2xl relative overflow-hidden group max-w-2xl mx-auto">
            <div class="absolute top-0 inset-x-0 h-1.5 bg-gradient-to-r from-primary-500 via-secondary-500 to-primary-700"></div>

            <div class="grid sm:grid-cols-2 gap-8 items-center text-left">
                <div class="space-y-3">
                    <p class="text-xs font-black text-primary-600 dark:text-primary-400 uppercase tracking-widest">Active License Matrix</p>
                    <div class="flex items-baseline gap-1">
                        <span class="text-4xl font-black text-slate-900 dark:text-white font-sans tracking-tight">$0</span>
                        <span class="text-xs font-bold text-slate-400 dark:text-slate-500">/ monthly channel</span>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium leading-normal">
                        Empower field agents to deploy structural audit protocols across real-time Simcoe deployment pipelines cleanly.
                    </p>
                </div>

                <div class="space-y-4">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-300">
                            <span class="text-secondary-500 text-xs">●</span> Unlimited Field Media Uploads
                        </div>
                        <div class="flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-300">
                            <span class="text-secondary-500 text-xs">●</span> 6-Month, Move-In & Move-Out Forms
                        </div>
                        <div class="flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-300">
                            <span class="text-secondary-500 text-xs">●</span> Automated Differential Comparison
                        </div>
                    </div>

                    <button class="w-full text-center py-3 px-4 rounded-xl bg-slate-950 hover:bg-slate-900 dark:bg-secondary-500 dark:hover:bg-secondary-600 text-white dark:text-slate-950 font-black text-xs transition-all duration-300 shadow-md uppercase tracking-wider font-sans group-hover:scale-[1.02]">
                        Confirm Subscription
                    </button>
                </div>
            </div>
        </div>

        <p class="text-[10px] text-slate-400 dark:text-slate-500 font-medium font-mono">
            Secure transactional layer verification node: active inspection initialization parameters apply.
        </p>
    </div>
</section>
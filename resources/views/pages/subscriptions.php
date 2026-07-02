<?php
// /resources/views/pages/subscriptions.php

declare(strict_types=1);

use Src\Config\NavigationConfig;

/** @var string $assetBase */
/** @var string $appName */
/** @var string $baseUrl */
/** @var bool $isLoggedIn */
/** @var array $subscriptions Active subscription records */
/** @var array $properties Selected properties mapped to landlord context */

// Pull dynamic configurations matching global ecosystem state parameters
$moduleLabels       = NavigationConfig::getModuleLabels();
$moduleDescriptions = NavigationConfig::getModuleDescriptions();
?>

<style>
    @keyframes smoothPulse {

        0%,
        100% {
            transform: scale(1);
            opacity: 0.95;
        }

        50% {
            transform: scale(1.02);
            opacity: 1;
        }
    }

    .animate-smooth-pulse {
        animation: smoothPulse 4s ease-in-out infinite;
    }
</style>

<section class="relative overflow-hidden bg-slate-50 dark:bg-slate-950 py-12 px-6 sm:px-12 lg:px-24 xl:px-32 transition-colors duration-300 border-b-4 border-primary-600 relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen max-w-[100vw] font-sans" data-aos="fade-down" data-aos-duration="600">
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#00000005_2px,transparent_2px),linear-gradient(to_bottom,#00000005_2px,transparent_2px)] dark:bg-[linear-gradient(to_right,#ffffff03_2px,transparent_2px),linear-gradient(to_bottom,#ffffff03_2px,transparent_2px)] bg-[size:30px_40px] pointer-events-none"></div>

    <div class="relative z-10 max-w-7xl mx-auto flex flex-col md:flex-row md:items-center justify-between gap-8">
        <div>
            <div class="inline-flex items-center gap-2 bg-slate-100 dark:bg-primary-950/40 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-primary-900/50 mb-3">
                <span class="h-2 w-2 rounded-full bg-primary-600"></span>
                <p class="uppercase tracking-wider text-[10px] font-black text-slate-500 dark:text-primary-400 flex items-center gap-1.5">
                    <!-- Dynamic Services Link Mapping Vector -->
                    <a href="<?= $baseUrl ?>services"
                        data-partial
                        data-title="<?= htmlspecialchars($moduleLabels['services'] ?? 'Service Suite Modules') ?>"
                        data-summary="<?= htmlspecialchars($moduleDescriptions['services'] ?? 'Browse core real-time ecosystem applications') ?>"
                        class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors duration-200">
                        Services
                    </a>

                    <span class="text-slate-400 font-normal">/</span>

                    <!-- Dynamic Rental Applications Link Mapping Vector -->
                    <a href="<?= $baseUrl ?>rental-applications"
                        data-partial
                        data-title="<?= htmlspecialchars($moduleLabels['rental-applications'] ?? 'Available Infrastructure Services') ?>"
                        data-summary="<?= htmlspecialchars($moduleDescriptions['rental-applications'] ?? 'Browse core real-time ecosystem applications') ?>"
                        class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors duration-200">
                        Rental Applications
                    </a>

                    <span class="text-slate-400 font-normal">/</span>
                    <span class="text-primary-700 dark:text-primary-300">Landlord Subscriptions</span>
                </p>
            </div>
            <h1 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight uppercase">
                Landlord <span class="text-primary-600 dark:text-primary-400">Subscriptions</span>
            </h1>
            <p class="text-sm sm:text-base text-slate-600 dark:text-slate-400 font-bold max-w-xl mt-1">
                Manage ecosystem parameters, toggle access code pools, and review pipeline status channels.
            </p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 xl:gap-6">
            <div class="bg-white dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-800 p-4 rounded-2xl min-w-[130px]">
                <span class="text-xs font-black text-slate-400 uppercase tracking-wider block">Active Tiers</span>
                <span class="text-2xl font-black text-slate-900 dark:text-white block tracking-tight mt-1">02</span>
            </div>
            <div class="bg-white dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-800 p-4 rounded-2xl min-w-[130px]">
                <span class="text-xs font-black text-slate-400 uppercase tracking-wider block">Tokens Pooled</span>
                <span class="text-2xl font-black text-primary-600 dark:text-primary-400 block tracking-tight mt-1">45 / 50</span>
            </div>
            <div class="bg-white dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-800 p-4 rounded-2xl min-w-[130px] col-span-2 sm:col-span-1">
                <span class="text-xs font-black text-slate-400 uppercase tracking-wider block">Total Inflow</span>
                <span class="text-2xl font-black text-slate-900 dark:text-white block tracking-tight mt-1">128</span>
            </div>
        </div>
    </div>
</section>

<section class="py-20 px-6 sm:px-12 lg:px-24 xl:px-32 bg-slate-100 dark:bg-slate-900 transition-colors duration-300 relative overflow-hidden relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen max-w-[100vw] font-sans">
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#00000004_2px,transparent_2px),linear-gradient(to_bottom,#00000004_2px,transparent_2px)] dark:bg-[linear-gradient(to_right,#ffffff02_2px,transparent_2px),linear-gradient(to_bottom,#ffffff02_2px,transparent_2px)] bg-[size:30px_40px] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto space-y-12 relative z-10">
        <div class="flex flex-col space-y-2 border-b-2 border-slate-200 dark:border-white/10 pb-6" data-aos="fade-up" data-aos-duration="600">
            <h2 class="text-sm font-black uppercase tracking-widest text-primary-600 dark:text-primary-400 px-1">Allocation Levels</h2>
            <p class="text-2xl sm:text-3xl font-black text-slate-800 dark:text-slate-100 tracking-tight px-1 uppercase">Select Channel Tier</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="group bg-white dark:bg-black rounded-3xl p-8 border-2 border-slate-200 dark:border-slate-800 shadow-xl transition-all duration-300 hover:-translate-y-1 hover:border-gray-500 flex flex-col justify-between" data-aos="fade-up" data-aos-delay="100" data-aos-duration="700">
                <div class="space-y-6">
                    <div>
                        <h3 class="text-xl font-black uppercase tracking-tight text-slate-900 dark:text-white">Basic Core</h3>
                        <p class="text-xs font-bold text-slate-500 mt-1">Single asset allocation node</p>
                    </div>
                    <div class="flex items-baseline gap-1 py-2 border-y-2 border-slate-100 dark:border-slate-900">
                        <span class="text-4xl font-black text-slate-900 dark:text-white tracking-tight">$0</span>
                        <span class="text-xs font-black text-slate-400">/ forever context</span>
                    </div>
                    <ul class="space-y-3 text-sm font-bold text-slate-700 dark:text-slate-300 list-none pl-0">
                        <li class="flex items-center gap-2"><span class="text-primary-600">✓</span> 1 Dedicated Asset Key</li>
                        <li class="flex items-center gap-2"><span class="text-primary-600">✓</span> Standard Verification Checks</li>
                        <li class="flex items-center gap-2 text-slate-400 line-through"><span>×</span> Automated Registry Scans</li>
                    </ul>
                </div>
                <div class="pt-8">
                    <button class="w-full text-center py-3.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-900 dark:text-white font-black text-xs uppercase tracking-wider transition-colors border border-transparent dark:border-slate-800">
                        Active Base Configuration
                    </button>
                </div>
            </div>

            <div class="group bg-white dark:bg-black rounded-3xl p-8 border-2 border-primary-500 dark:border-primary-500 shadow-2xl transition-all duration-300 hover:-translate-y-1 relative flex flex-col justify-between" data-aos="fade-up" data-aos-delay="200" data-aos-duration="700">
                <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full bg-primary-600 text-white font-black text-[10px] tracking-widest uppercase shadow-md animate-smooth-pulse">
                    Recommended Vector
                </div>
                <div class="space-y-6 mt-2">
                    <div>
                        <h3 class="text-xl font-black uppercase tracking-tight text-slate-900 dark:text-white">Portfolio Scaling</h3>
                        <p class="text-xs font-bold text-primary-600 dark:text-primary-400 mt-1">Multi-unit portfolio streaming</p>
                    </div>
                    <div class="flex items-baseline gap-1 py-2 border-y-2 border-slate-100 dark:border-slate-900">
                        <span class="text-4xl font-black text-slate-900 dark:text-white tracking-tight">$49</span>
                        <span class="text-xs font-black text-slate-400">/ monthly channel</span>
                    </div>
                    <ul class="space-y-3 text-sm font-bold text-slate-700 dark:text-slate-300 list-none pl-0">
                        <li class="flex items-center gap-2"><span class="text-primary-600">✓</span> Up to 15 Active Asset Keys</li>
                        <li class="flex items-center gap-2"><span class="text-primary-600">✓</span> Priority Background Routing</li>
                        <li class="flex items-center gap-2"><span class="text-primary-600">✓</span> Automated Registry Scans</li>
                    </ul>
                </div>
                <div class="pt-8">
                    <form method="POST" action="<?= $baseUrl ?>landlord-subscriptions/upgrade">
                        <input type="hidden" name="tier" value="portfolio">
                        <button type="submit" class="w-full text-center py-3.5 px-4 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-black text-xs uppercase tracking-wider transition-all shadow-md shadow-primary-500/20 transform hover:scale-[1.01]">
                            Provision Portfolio Tier
                        </button>
                    </form>
                </div>
            </div>

            <div class="group bg-white dark:bg-black rounded-3xl p-8 border-2 border-slate-200 dark:border-slate-800 shadow-xl transition-all duration-300 hover:-translate-y-1 hover:border-gray-500 flex flex-col justify-between" data-aos="fade-up" data-aos-delay="300" data-aos-duration="700">
                <div class="space-y-6">
                    <div>
                        <h3 class="text-xl font-black uppercase tracking-tight text-slate-900 dark:text-white">Enterprise Grid</h3>
                        <p class="text-xs font-bold text-slate-500 mt-1">Unlimited brokerage integrations</p>
                    </div>
                    <div class="flex items-baseline gap-1 py-2 border-y-2 border-slate-100 dark:border-slate-900">
                        <span class="text-4xl font-black text-slate-900 dark:text-white tracking-tight">$129</span>
                        <span class="text-xs font-black text-slate-400">/ monthly channel</span>
                    </div>
                    <ul class="space-y-3 text-sm font-bold text-slate-700 dark:text-slate-300 list-none pl-0">
                        <li class="flex items-center gap-2"><span class="text-primary-600">✓</span> Infinite Asset Keys Mapped</li>
                        <li class="flex items-center gap-2"><span class="text-primary-600">✓</span> Fully Dedicated API Streams</li>
                        <li class="flex items-center gap-2"><span class="text-primary-600">✓</span> Custom White-label Interfaces</li>
                    </ul>
                </div>
                <div class="pt-8">
                    <form method="POST" action="<?= $baseUrl ?>landlord-subscriptions/upgrade">
                        <input type="hidden" name="tier" value="enterprise">
                        <button type="submit" class="w-full text-center py-3.5 px-4 rounded-xl bg-slate-950 hover:bg-slate-900 dark:bg-slate-800 dark:hover:bg-slate-700 text-white font-black text-xs uppercase tracking-wider transition-colors transform hover:scale-[1.01]">
                            Provision Enterprise Grid
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-24 px-6 sm:px-12 lg:px-24 xl:px-32 bg-slate-50 dark:bg-slate-950 border-t-2 border-slate-200 dark:border-slate-900 transition-colors duration-200 overflow-hidden relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen max-w-[100vw] font-sans">
    <div class="max-w-7xl mx-auto grid lg:grid-cols-12 gap-16 items-start relative z-10" x-data="{ allocationModal: false }">

        <div class="lg:col-span-5 space-y-6" data-aos="fade-right" data-aos-duration="800">
            <span class="inline-block bg-slate-100 dark:bg-primary-950 text-primary-700 dark:text-primary-400 px-4 py-2 rounded-xl text-sm font-black tracking-widest uppercase border border-slate-200 dark:border-primary-900">
                Token Provisioning Engine
            </span>
            <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight uppercase leading-tight">Generate Intake Access Keys</h2>
            <p class="text-slate-700 dark:text-slate-300 leading-relaxed text-sm sm:text-base font-bold">
                Spawn individual, one-time application access hooks linked specifically to a designated townhouse or unit block in your portfolio database framework.
            </p>

            <form class="space-y-4 pt-4 bg-white dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-800 p-6 rounded-2xl shadow-md" method="POST" action="<?= $baseUrl ?>landlord-subscriptions/generate-token">
                <div class="space-y-1.5">
                    <label class="text-xs uppercase font-black tracking-wider text-slate-500">Target Property Mapping</label>
                    <select name="property_id" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border-2 border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white rounded-xl text-sm font-bold focus:outline-none focus:border-primary-500">
                        <option value="">Select Portfolio Entity Node</option>
                        <option value="1">Suite 404 - 122 Simcoe Ave</option>
                        <option value="2">Townhouse 12 - Victoria Complex</option>
                        <option value="3">Unit B - 88 Lakeshore Rd</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs uppercase font-black tracking-wider text-slate-500">Applicant Label Reference</label>
                    <input type="text" name="applicant_label" placeholder="e.g., Jane Doe Intake" required class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-950 border-2 border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white rounded-xl text-sm font-bold focus:outline-none focus:border-primary-500">
                </div>
                <button type="submit" class="w-full py-3 px-4 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-black text-xs uppercase tracking-wider transition-all transform hover:scale-[1.01] mt-2 shadow-md">
                    Instantiate Code Channel <i class="fa-solid fa-plus-circle ml-1.5"></i>
                </button>
            </form>
        </div>

        <div class="lg:col-span-7 bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border-2 border-slate-200 dark:border-slate-800 shadow-xl relative overflow-hidden transition-all duration-300 hover:border-gray-500/30" data-aos="fade-left" data-aos-duration="800">
            <div class="flex items-center justify-between border-b-2 border-slate-100 dark:border-slate-800 pb-4 mb-6">
                <h3 class="font-black text-lg text-slate-900 dark:text-white uppercase tracking-tight">Active Token Pipeline Logs</h3>
                <span class="text-[10px] font-mono font-black uppercase tracking-wider px-2.5 py-1 rounded bg-slate-100 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-500">Live Feed</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 text-[11px] font-black uppercase tracking-wider text-slate-400">
                            <th class="pb-3 pl-2">Access Token Code</th>
                            <th class="pb-3">Property Assignment</th>
                            <th class="pb-3">Allocation Node</th>
                            <th class="pb-3 text-right pr-2">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs font-bold text-slate-700 dark:text-slate-300">
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-950/40 transition-colors">
                            <td class="py-3.5 pl-2 font-mono text-primary-600 dark:text-primary-400 tracking-wider">ACC-26-SIMCOE-4041</td>
                            <td class="py-3.5 text-slate-900 dark:text-white">Suite 404 - Simcoe Ave</td>
                            <td class="py-3.5 font-mono">Jane Doe Intake</td>
                            <td class="py-3.5 text-right pr-2">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-yellow-100 dark:bg-yellow-950/40 text-yellow-800 dark:text-yellow-400 text-[10px] uppercase font-black tracking-wide border border-yellow-200 dark:border-yellow-900/50">Pending</span>
                            </td>
                        </tr>
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-950/40 transition-colors">
                            <td class="py-3.5 pl-2 font-mono text-primary-600 dark:text-primary-400 tracking-wider">ACC-26-VIC-TWN12</td>
                            <td class="py-3.5 text-slate-900 dark:text-white">Townhouse 12 - Victoria</td>
                            <td class="py-3.5 font-mono">John Smith App</td>
                            <td class="py-3.5 text-right pr-2">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-100 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-400 text-[10px] uppercase font-black tracking-wide border border-emerald-200 dark:border-emerald-900/50">Bound</span>
                            </td>
                        </tr>
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-950/40 transition-colors">
                            <td class="py-3.5 pl-2 font-mono text-primary-600 dark:text-primary-400 tracking-wider">ACC-26-LAKE-UNITB</td>
                            <td class="py-3.5 text-slate-900 dark:text-white">Unit B - Lakeshore Rd</td>
                            <td class="py-3.5 font-mono">Expired Channel Fallback</td>
                            <td class="py-3.5 text-right pr-2">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 text-[10px] uppercase font-black tracking-wide border border-slate-200 dark:border-slate-700">Stale</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
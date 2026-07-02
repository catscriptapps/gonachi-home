<?php
// /resources/views/components/general/operational-matrix.php
declare(strict_types=1);

/** @var string $assetBase */
/** @var string $appName */
/** @var string $baseUrl */
/** @var array $servicesLinks */
/** @var array $moduleLabels */
/** @var array $moduleDescriptions */
/** @var array $icons */

?>

<section id="operational-matrix" class="py-20 px-6 sm:px-12 lg:px-24 xl:px-32 bg-white dark:bg-slate-900 transition-colors duration-300 relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen max-w-[100vw] overflow-hidden">
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#00000002_1px,transparent_1px),linear-gradient(to_bottom,#00000002_1px,transparent_1px)] dark:bg-[linear-gradient(to_right,#ffffff01_1px,transparent_1px),linear-gradient(to_bottom,#ffffff01_1px,transparent_1px)] bg-[size:32px_48px] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto space-y-12 relative z-10">
        <div class="flex flex-col space-y-2 border-b border-slate-100 dark:border-slate-800 pb-6" data-aos="fade-up" data-aos-duration="700">
            <h2 class="text-xs font-black uppercase tracking-[0.3em] text-primary-600 dark:text-primary-400 px-1">
                Available Service Modules
            </h2>
            <p class="text-2xl font-black text-slate-900 dark:text-white px-1 tracking-tight">
                Integrated Subscription Vectors
            </p>
            <p class="text-slate-500 dark:text-slate-400 font-medium max-w-xl px-1">
                Deploy critical infrastructure instantly. Landlords can subscribe directly to specialized operational pipelines designed to secure tenants and shield assets.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <?php
            $index = 0;
            foreach ($servicesLinks as $name => $url):
                // Increment animation delays cleanly per loop iteration
                $delay = $index * 150;

                // Centralize data routing context safely matching module lookup structures
                $dynamicTitle   = $moduleLabels[$name] ?? $name;
                $dynamicSummary = $moduleDescriptions[$name] ?? 'Browse core real-time ecosystem applications';
            ?>
                <div class="group relative overflow-hidden rounded-[2rem] bg-primary-50/40 dark:bg-black p-8 sm:p-10 border border-primary-100/80 dark:border-secondary-900/30 transition-all duration-300 hover:shadow-[0_25px_60px_-15px_rgba(var(--color-primary-500),0.08)] hover:-translate-y-1.5 hover:border-primary-500/40 dark:hover:border-primary-500/30 cursor-pointer"
                    data-aos="fade-up"
                    data-aos-duration="800"
                    data-aos-delay="<?= $delay ?>">
                    <div class="absolute inset-0 bg-gradient-to-br from-white/80 via-transparent to-primary-500/[0.01] dark:from-transparent dark:to-primary-500/[0.02] opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>

                    <div class="flex items-center justify-between relative z-10">

                        <div class="relative w-16 h-16 flex items-center justify-center transition-all duration-500 ease-out transform group-hover:scale-110 group-hover:rotate-3 filter drop-shadow-sm group-hover:drop-shadow-md">
                            <div class="absolute inset-0 bg-primary-600 dark:bg-primary-500 border border-primary-600 dark:border-primary-500 group-hover:bg-gray-600 dark:group-hover:bg-gray-500 group-hover:border-gray-600 dark:group-hover:border-gray-500 transition-all duration-500"
                                style="clip-path: polygon(50% 0%, 100% 40%, 100% 100%, 0% 100%, 0% 40%);"></div>

                            <div class="w-9 h-9 flex items-center justify-center relative z-10 text-white dark:text-slate-950 group-hover:text-white dark:group-hover:text-slate-950 pt-2 transition-colors duration-500">
                                <?= $icons[$name] ?? '' ?>
                            </div>
                        </div>

                        <span class="px-2.5 py-1 rounded-full text-[9px] font-black tracking-widest uppercase bg-white dark:bg-slate-950 text-primary-700 dark:text-primary-400 border border-primary-100 dark:border-secondary-900 shadow-sm">
                            Available Sub
                        </span>
                    </div>

                    <div class="mt-10 relative z-10 space-y-2.5">
                        <h3 class="text-xl font-black tracking-tight text-slate-900 dark:text-white font-sans transition-colors duration-300 group-hover:text-primary-700 dark:group-hover:text-primary-400">
                            <a href="<?= $url ?>"
                                data-partial
                                data-title="<?= htmlspecialchars($dynamicTitle) ?>"
                                data-summary="<?= htmlspecialchars($dynamicSummary) ?>"
                                class="after:absolute after:inset-0">
                                <?= htmlspecialchars($name) ?>
                            </a>
                        </h3>
                        <p class="text-slate-600 dark:text-slate-400 leading-relaxed font-medium max-w-sm">
                            <?= htmlspecialchars($dynamicSummary) ?>
                        </p>
                        <div class="flex items-center gap-2 pt-3 border-t border-slate-200/60 dark:border-slate-800/60 transition-colors duration-300 group-hover:border-primary-500/20">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-secondary-500 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-secondary-600 dark:bg-secondary-500"></span>
                            </span>
                            <p class="text-[9px] text-slate-400 dark:text-slate-500 uppercase tracking-widest font-black font-sans">
                                <?= htmlspecialchars($dynamicTitle) ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php
                $index++;
            endforeach;
            ?>
        </div>

        <div class="p-6 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200/60 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm" data-aos="fade-up" data-aos-duration="600">
            <div class="space-y-1">
                <p class="font-bold text-slate-900 dark:text-white tracking-tight">Future-Proof Module Scalability</p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 max-w-xl leading-normal font-medium">
                    The platform architecture is optimized for module expansions. Landlords maintain a singular authentication session to activate additional operational components as they go active online.
                </p>
            </div>
            <a href="javascript:void(0);" class="register-btn sm:shrink-0 inline-flex items-center justify-center px-4 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 text-white font-bold transition-colors duration-300 font-sans">
                Create Landlord Account
            </a>
        </div>
    </div>
</section>
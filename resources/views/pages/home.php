<?php
// /resources/views/pages/home.php

declare(strict_types=1);

/** @var string $baseUrl  */

use Src\Config\NavigationConfig;

// Pull entirely centralized infrastructure matrices
$icons              = NavigationConfig::getIcons();
$servicesLinks      = NavigationConfig::getModuleLinks();
$moduleLabels       = NavigationConfig::getModuleLabels();
$moduleDescriptions = NavigationConfig::getModuleDescriptions();

// Target matching metadata matching the rental application routing key
$targetMatchKey = null;
foreach ($servicesLinks as $name => $url) {
    if (trim($url, '/') === 'rental-applications') {
        $targetMatchKey = $name;
        break;
    }
}

// Extract dynamic titles or fallback gracefully to structural defaults
$pipelineTitle   = $targetMatchKey ? ($moduleLabels[$targetMatchKey] ?? 'Initialize Application Framework') : 'Initialize Application Framework';
$pipelineSummary = $targetMatchKey ? ($moduleDescriptions[$targetMatchKey] ?? 'Launch critical cloud lease infrastructure pipeline.') : 'Launch critical cloud lease infrastructure pipeline.';
?>

<section class="relative overflow-hidden bg-gradient-to-b from-primary-300 via-white to-primary-200 text-slate-800 dark:from-slate-950 dark:via-black dark:to-slate-950 py-20 lg:py-28 px-6 sm:px-12 lg:px-24 xl:px-32 transition-colors duration-300 border-b border-slate-200 dark:border-slate-800 relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen max-w-[100vw]">
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#00000003_1px,transparent_1px),linear-gradient(to_bottom,#00000003_1px,transparent_1px)] dark:bg-[linear-gradient(to_right,#ffffff03_1px,transparent_1px),linear-gradient(to_bottom,#ffffff03_1px,transparent_1px)] bg-[size:32px_32px] pointer-events-none"></div>

    <div class="absolute -top-40 -right-20 w-[500px] h-[500px] bg-primary-500/[0.04] dark:bg-primary-500/[0.07] rounded-full blur-[140px] pointer-events-none"></div>
    <div class="absolute -bottom-40 -left-20 w-[500px] h-[500px] bg-secondary-500/[0.02] dark:bg-secondary-500/[0.04] rounded-full blur-[140px] pointer-events-none"></div>

    <div class="relative z-10 max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">

        <div class="flex flex-col space-y-5 lg:col-span-7" data-aos="fade-right" data-aos-duration="800">
            <div class="inline-flex items-center gap-2">
                <span class="h-1.5 w-1.5 rounded-full bg-primary-500 dark:bg-primary-400 animate-pulse"></span>
                <p class="uppercase tracking-[0.25em] text-[10px] font-black text-primary-600 dark:text-primary-400">
                    Platform Infrastructure
                </p>
            </div>

            <h1 class="text-3xl sm:text-4xl xl:text-5xl font-black text-slate-900 dark:text-white tracking-tight leading-tight uppercase font-sans">
                Engineered for High-Yield <br />
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-600 via-primary-500 to-indigo-600 dark:from-primary-400 dark:via-primary-300 dark:to-secondary-400">
                    Property Oversight
                </span>
            </h1>

            <p class="text-slate-600 dark:text-slate-400 max-w-xl font-medium leading-relaxed">
                Discover tools curated specifically to relieve structural strain from portfolio operations. This platform operates as a centralized asset management utility designed to instantly spin up deployment service modules on demand.
            </p>

            <div class="pt-4 flex flex-wrap gap-4">
                <?php
                // Safely extract the public route definition for the Services section
                $publicLinks = NavigationConfig::getNavLinks(false);
                $servicesUrl = $publicLinks['Services']['url'] ?? '#operational-matrix';
                ?>
                <a href="<?= $servicesUrl ?>"
                    data-partial
                    data-title="Service Suite Modules"
                    data-summary="Deploy critical cloud micro-infrastructure optimized to secure rental tenants and shield field inventory objects."
                    class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white dark:bg-primary-500 dark:hover:bg-primary-600 dark:text-slate-950 font-bold shadow-md shadow-primary-500/10 transition-all duration-300 font-sans tracking-wide uppercase text-xs">
                    Explore Core Modules
                </a>
            </div>
        </div>

        <div class="lg:col-span-5 bg-white/90 dark:bg-secondary-950/40 border-2 border-slate-200/80 dark:border-secondary-900 shadow-2xl rounded-3xl p-6 sm:p-8 backdrop-blur-md relative" data-aos="fade-left" data-aos-duration="800" data-aos-delay="150">
            <div class="space-y-6">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-secondary-900">
                    <div class="flex flex-col">
                        <span class="text-[10px] font-black text-primary-600 dark:text-primary-400 uppercase tracking-widest">Verification Node</span>
                        <h2 class="text-base font-black text-slate-900 dark:text-white uppercase font-sans tracking-tight mt-0.5">Tenant Portal Gateway</h2>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 dark:bg-primary-950/40 dark:text-primary-400 border border-slate-200 dark:border-primary-900/50">Secure Sync</span>
                </div>

                <form id="tenant-token-form" class="space-y-3" autocomplete="off">
                    <label for="access_token" class="block text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-secondary-500">
                        Enter Secured Access Token or Link Key
                    </label>
                    <div class="relative flex items-center">
                        <div class="absolute left-4 text-slate-400 dark:text-secondary-600 pointer-events-none">
                            <i class="fa-solid fa-key text-xs"></i>
                        </div>
                        <input
                            type="text"
                            id="access_token"
                            name="access_token"
                            placeholder="TK-2026-XXXX"
                            required
                            class="w-full pl-10 pr-24 py-3 text-xs font-bold font-mono uppercase tracking-widest rounded-xl bg-slate-50 dark:bg-primary-950 border-2 border-slate-200 dark:border-primary-900 text-slate-900 dark:text-white focus:outline-none focus:border-primary-500 dark:focus:border-primary-400 transition-all placeholder:text-slate-300 dark:placeholder:text-primary-900" />
                        <button
                            type="submit"
                            id="tenant-token-submit"
                            class="absolute right-2 px-3 py-1.5 rounded-lg bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 text-white dark:text-slate-950 text-[10px] font-black uppercase tracking-wider transition-all duration-200 shadow-sm shadow-primary-500/10">
                            Track Node
                        </button>
                    </div>
                    <div id="tenant-token-message" class="hidden text-[10px] font-bold uppercase tracking-wide"></div>
                </form>

                <div class="relative flex py-2 items-center">
                    <div class="flex-grow border-t border-slate-100 dark:border-secondary-900"></div>
                    <span class="flex-shrink mx-4 text-[9px] font-black uppercase tracking-[0.2em] text-slate-300 dark:text-secondary-700">OR</span>
                    <div class="flex-grow border-t border-slate-100 dark:border-secondary-900"></div>
                </div>

                <div class="space-y-3">
                    <div class="rounded-xl bg-slate-50/50 dark:bg-primary-950/20 border border-slate-100 dark:border-primary-950 p-4 text-center">
                        <p class="text-xs text-slate-500 dark:text-secondary-400 font-medium leading-normal mb-3">
                            No access vector assigned yet? Spin up a verified, cloud-isolated residential lease processing log directly into your landlord's asset portfolio ecosystem.
                        </p>
                        <a href="<?php echo $baseUrl; ?>rental-applications"
                            data-partial
                            data-title="<?= htmlspecialchars($pipelineTitle) ?>"
                            data-summary="<?= htmlspecialchars($pipelineSummary) ?>"
                            class="inline-flex w-full items-center justify-center gap-2 px-4 py-2.5 rounded-xl border-2 border-secondary-500/30 dark:border-secondary-500/20 bg-secondary-500/5 hover:bg-secondary-500/10 text-secondary-700 dark:text-secondary-400 text-xs font-black uppercase tracking-wider transition-all duration-200">
                            <i class="fa-solid fa-file-signature text-xs"></i>
                            Launch Rental Pipeline
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-1 border-t border-slate-100 dark:border-secondary-900 text-left">
                    <div class="p-3 rounded-xl bg-slate-50/50 dark:bg-primary-950/10 border border-slate-100 dark:border-primary-950">
                        <span class="text-[9px] text-slate-400 dark:text-primary-600 block uppercase font-bold tracking-wide">Sync State</span>
                        <span class="text-xs font-black text-slate-700 dark:text-white font-sans flex items-center gap-1.5 mt-0.5">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Active Node
                        </span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50/50 dark:bg-primary-950/10 border border-slate-100 dark:border-primary-950">
                        <span class="text-[9px] text-slate-400 dark:text-primary-600 block uppercase font-bold tracking-wide">Latency Matrix</span>
                        <span class="text-xs font-black text-primary-600 dark:text-primary-400 font-sans block mt-0.5">0.02ms Response</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<?php include __DIR__ . "/../components/general/operational-matrix.php"; ?>
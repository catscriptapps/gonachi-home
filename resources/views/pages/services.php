<?php
// /resources/views/pages/services.php

declare(strict_types=1);

/** @var string $baseUrl */

use Src\Service\AuthService;
use Src\Config\NavigationConfig;

// --- CONTEXT-AWARE ROUTING ---
$landlord = AuthService::currentLandlord();

if ($landlord) {
    // If a landlord is logged in, show their subscribed services.
    $allServices = \App\Models\Service::where('status_id', 1)->orderBy('name')->get();
    $subscribedServiceIds = $landlord->services()->pluck('services.id')->toArray();
?>
    <div class="space-y-10 font-sans py-10">
        <?php
        $breadcrumbs = ['Dashboard' => '/dashboard', 'Services' => '/services'];
        include __DIR__ . '/../components/ui/breadcrumbs.php';
        ?>

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Manage Service Subscriptions</h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    A list of all available service modules. Subscribed services are highlighted.
                </p>
            </div>
        </div>

        <div id="services-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            <?php if ($allServices->isEmpty()) : ?>
                <div class="md:col-span-2 p-12 text-center bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800">
                    <p class="text-gray-500 font-medium">No services are available at this time.</p>
                </div>
            <?php else : ?>
                <?php foreach ($allServices as $service) :
                    $isSubscribed = in_array($service->id, $subscribedServiceIds);
                ?>
                    <div class="group relative flex flex-col justify-between overflow-hidden rounded-2xl p-6 transition-all duration-300
                        <?= $isSubscribed
                            ? 'bg-white dark:bg-gray-900 shadow-lg border-2 border-primary-500/50'
                            : 'bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-800 grayscale opacity-70 hover:grayscale-0 hover:opacity-100 hover:shadow-xl hover:border-primary-500/30'
                        ?>">
                        <div>
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-black text-gray-900 dark:text-white"><?= htmlspecialchars($service->name) ?></h3>
                                <?php if ($isSubscribed) : ?>
                                    <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400">
                                        Subscribed
                                    </span>
                                <?php endif; ?>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 font-medium leading-relaxed">
                                <?= htmlspecialchars($service->short_description) ?>
                            </p>
                        </div>

                        <?php if (!$isSubscribed) : ?>
                            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-800">
                                <button type="button"
                                    data-action="subscribe-service"
                                    data-service-id="<?= $service->id ?>"
                                    data-service-name="<?= htmlspecialchars($service->name) ?>"
                                    data-price="0"
                                    class="w-full text-center py-3 px-4 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-black text-xs uppercase tracking-wider transition-all shadow-md shadow-primary-500/20 transform hover:scale-[1.02]">
                                    Subscribe Now
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
<?php
    return; // Stop execution to prevent rendering the public part.
}

// --- PUBLIC-FACING VIEW (for guests) ---
$icons = NavigationConfig::getIcons();
$servicesLinks = NavigationConfig::getModuleLinks();
$moduleLabels = NavigationConfig::getModuleLabels();
$moduleDescriptions = NavigationConfig::getModuleDescriptions();
?>

<section class="relative overflow-hidden bg-gradient-to-b from-slate-50 via-white to-slate-100 text-slate-800 dark:from-slate-950 dark:via-black dark:to-slate-950 py-16 lg:py-24 px-6 sm:px-12 lg:px-24 xl:px-32 transition-colors duration-300 border-b border-slate-200 dark:border-slate-800 relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen max-w-[100vw]">
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#00000002_1px,transparent_1px),linear-gradient(to_bottom,#00000002_1px,transparent_1px)] dark:bg-[linear-gradient(to_right,#ffffff02_1px,transparent_1px),linear-gradient(to_bottom,#ffffff02_1px,transparent_1px)] bg-[size:32px_32px] pointer-events-none"></div>

    <div class="absolute -top-40 -right-20 w-[500px] h-[500px] bg-primary-500/[0.03] dark:bg-primary-500/[0.06] rounded-full blur-[140px] pointer-events-none"></div>
    <div class="absolute -bottom-40 -left-20 w-[500px] h-[500px] bg-secondary-500/[0.02] dark:bg-secondary-500/[0.04] rounded-full blur-[140px] pointer-events-none"></div>

    <div class="max-w-6xl mx-auto relative z-10">

        <nav class="flex items-center gap-2 mb-8 text-sm font-black uppercase tracking-wider text-slate-400 dark:text-slate-500" data-aos="fade-down" data-aos-duration="800">
            <a href="<?= $baseUrl ?>" data-partial data-title="Home" data-summary="Centralized Landlord Infrastructure" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Home</a>
            <i class="fa-solid fa-angle-right text-[10px] text-slate-300 dark:text-slate-700 stroke-[3]"></i>
            <span class="text-slate-900 dark:text-white font-black">Services</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">

            <div class="flex flex-col space-y-4 lg:col-span-7" data-aos="fade-right" data-aos-duration="800">
                <div class="inline-flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-primary-500 dark:bg-primary-400 animate-pulse"></span>
                    <p class="uppercase tracking-[0.25em] text-xs font-black text-primary-600 dark:text-primary-400">
                        Available Architecture
                    </p>
                </div>

                <h1 class="text-3xl sm:text-4xl xl:text-5xl font-black text-slate-900 dark:text-white tracking-tight uppercase font-sans leading-none">
                    Ecosystem <br />
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-600 to-secondary-600 dark:from-primary-400 dark:to-secondary-400">
                        Service Modules
                    </span>
                </h1>

                <p class="text-base text-slate-500 dark:text-slate-400 max-w-xl font-bold leading-relaxed">
                    Instantly deploy high-yield utility components designed to automate compliance pipelines, process validation layers, and stabilize core real estate workflows.
                </p>
            </div>

            <div class="lg:col-span-5 bg-slate-100/40 dark:bg-black border-2 border-slate-200 dark:border-slate-800/80 shadow-2xl rounded-[2rem] p-6 backdrop-blur-sm" data-aos="fade-left" data-aos-duration="800" data-aos-delay="100">
                <div class="space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b-2 border-slate-200/60 dark:border-slate-800/60">
                        <span class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Global Node State</span>
                        <span class="px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-primary-500/10 text-primary-600 dark:text-primary-400 border border-primary-500/20">Operational</span>
                    </div>

                    <p class="text-xs text-slate-400 dark:text-slate-500 leading-relaxed font-bold">
                        Select a provisioning pipeline below to immediately scale application processing capabilities or isolate multi-point structural assessment profiles.
                    </p>

                    <div class="grid grid-cols-2 gap-3 pt-1">
                        <div class="p-3 rounded-xl bg-white dark:bg-slate-900/40 border border-slate-200 dark:border-slate-800">
                            <span class="text-[9px] text-slate-400 dark:text-slate-500 block uppercase font-black tracking-wider">Response Cache</span>
                            <span class="text-base font-black text-slate-900 dark:text-white font-sans tracking-wide">0.02ms</span>
                        </div>
                        <div class="p-3 rounded-xl bg-white border border-slate-200 dark:bg-slate-900/40 dark:border-slate-800">
                            <span class="text-[9px] text-slate-400 dark:text-slate-500 block uppercase font-black tracking-wider">Sync Integrity</span>
                            <span class="text-base font-black text-secondary-600 dark:text-secondary-400 font-sans tracking-wide">100%</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<?php include __DIR__ . "/../components/general/operational-matrix.php"; ?>
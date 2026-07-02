<?php
// /resources/views/pages/properties.php

declare(strict_types=1);

// 1. Declare layout arrays first so they are bound to the global rendering scope early
$breadcrumbs = [
    'Dashboard' => '/dashboard',
    'Properties' => '/properties'
];

use Src\Config\NavigationConfig;

// 2. Initialize the routing controller framework
$controller = new \Src\Controller\PropertiesController();
$controller->index();
$propertyCards = $GLOBALS['propertyCards'] ?? '';

// Fetch the icon from the navigation config to ensure consistency
$workspaceLinks = NavigationConfig::getLandlordWorkspaceLinks();
$propertiesIcon = $workspaceLinks['Properties']['icon'] ?? '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M12 21V4.5m0 16.5h-3.75m-7.5 0h7.5" /></svg>';
?>

<?php include __DIR__ . '/../components/properties/view-property-modal.php'; ?>

<div class="space-y-6 max-w-full overflow-x-hidden py-10">
    <?php
    // 3. Render out the prepared breadcrumb array context
    include __DIR__ . '/../components/ui/breadcrumbs.php';
    ?>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white font-sans">Properties</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                A list of all registered corporate property assets, unit divisions, portfolio assignments, and regional markets.
            </p>
        </div>

        <div class="mt-4 md:mt-0 flex flex-row gap-3 items-center">
            <div class="relative flex-1 md:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" id="properties-search"
                    class="block w-full rounded-xl border-2 border-gray-400 dark:border-gray-600 bg-white dark:bg-gray-900 py-2 pl-10 pr-3 text-sm placeholder-gray-400 focus:border-primary-500 focus:ring-primary-500 text-gray-900 dark:text-white transition-all font-sans"
                    placeholder="Search properties...">
            </div>

            <button type="button" id="add-property-btn" data-tooltip="Add Property"
                class="shrink-0 flex items-center justify-center rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-bold text-white shadow-md hover:bg-primary-700 transition-all active:scale-95 focus:ring-2 focus:ring-primary-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                <svg class="w-5 h-5 md:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="hidden xs:inline md:inline">Add Property</span>
            </button>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden">

        <?php if (empty($propertyCards)): ?>
            <div id="empty-properties-state" class="px-6 py-20 text-center">
                <div class="flex flex-col items-center">
                    <div class="h-12 w-12 text-gray-300 mb-4 flex items-center justify-center text-current [&>svg]:h-12 [&>svg]:w-12">
                        <?= $propertiesIcon ?>
                    </div>
                    <p class="font-medium font-sans text-gray-500 dark:text-gray-400">No corporate property assets found</p>
                </div>
            </div>
        <?php endif; ?>

        <div id="properties-tbody" class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-6 <?= empty($propertyCards) ? 'hidden' : '' ?>">
            <?= $propertyCards ?>
        </div>

        <?php
        $footerCountName = 'properties';
        include __DIR__ . '/../components/ui/footer-count.php';
        ?>
    </div>
</div>
<?php
// /resources/views/pages/landlords.php

declare(strict_types=1);

$controller = new \Src\Controller\LandlordsController();
$controller->index();

$landlordRows = $GLOBALS['landlordRows'] ?? '';
?>

<div class="space-y-6 max-w-full overflow-x-hidden py-10">
    <?php
    $breadcrumbs = ['Landlords' => '/landlords'];
    include __DIR__ . '/../components/ui/breadcrumbs.php';
    ?>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white font-sans">Landlords</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                A list of all registered corporate landlord entities, tax identities, operational centers, and portfolio scopes.
            </p>
        </div>

        <div class="mt-4 md:mt-0 flex flex-row gap-3 items-center">
            <div class="relative flex-1 md:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" id="landlords-search"
                    class="block w-full rounded-xl border-2 border-gray-400 dark:border-gray-600 bg-white dark:bg-gray-900 py-2 pl-10 pr-3 text-sm placeholder-gray-400 focus:border-primary-500 focus:ring-primary-500 text-gray-900 dark:text-white transition-all font-sans"
                    placeholder="Search landlords...">
            </div>

            <button type="button" id="add-landlord-btn" data-tooltip="Add Landlord"
                class="shrink-0 flex items-center justify-center rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-bold text-white shadow-md hover:bg-primary-700 transition-all active:scale-95 focus:ring-2 focus:ring-primary-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                <svg class="w-5 h-5 md:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="hidden xs:inline md:inline">Add Landlord</span>
            </button>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden">
        <div class="w-full overflow-hidden">
            <table class="w-full divide-y divide-gray-200 dark:divide-gray-800 table-fixed">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-full md:w-[30%]">
                            <span class="lg:hidden">Landlord Details</span>
                            <span class="hidden lg:inline">Company Name</span>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell w-[20%]">Location</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell w-[20%]">Tax ID / Business ID</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell w-[15%]">Date Registered</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell w-[100px]">Status</th>
                        <th class="relative px-6 py-4 text-right w-24 hidden lg:table-cell">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody id="landlords-tbody" class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                    <?php if (empty($landlordRows)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center">
                                    <svg class="h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <p class="font-medium font-sans">No corporate landlords found</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?= $landlordRows ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php
        $footerCountName = 'landlords';
        include __DIR__ . '/../components/ui/footer-count.php';
        ?>
    </div>
</div>

<?php include __DIR__ . '/../components/landlords/view-landlord-modal.php'; ?>
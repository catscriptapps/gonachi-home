<?php
// /resources/views/pages/access-tokens.php

declare(strict_types=1);

use Src\Service\AuthService;

$landlord = AuthService::currentLandlord();

if (!$landlord) {
    include __DIR__ . '/auth-required.php';
    return;
}

$breadcrumbs = ['Dashboard' => '/dashboard', 'Access Tokens' => '/access-tokens'];

$controller = new \Src\Controller\AccessTokensController();
$controller->index();
$accessTokenRows = $GLOBALS['accessTokenRows'] ?? '';
?>

<div class="space-y-6 max-w-full overflow-x-hidden py-10">
    <?php include __DIR__ . '/../components/ui/breadcrumbs.php'; ?>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white font-sans">Access Tokens</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Intake keys generated for your properties against your subscribed service modules.
            </p>
        </div>

        <div class="mt-4 md:mt-0 flex flex-row gap-3 items-center">
            <div class="relative flex-1 md:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" id="access-tokens-search"
                    class="block w-full rounded-xl border-2 border-gray-400 dark:border-gray-600 bg-white dark:bg-gray-900 py-2 pl-10 pr-3 text-sm placeholder-gray-400 focus:border-primary-500 focus:ring-primary-500 text-gray-900 dark:text-white transition-all font-sans"
                    placeholder="Search access tokens...">
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800 lg:table-fixed">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider font-sans">Token Code</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell font-sans">Property</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell font-sans">Service</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider font-sans">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden md:table-cell font-sans">Created</th>
                        <th class="relative px-6 py-4 text-right w-24">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody id="access-tokens-tbody" class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                    <?php if (empty($accessTokenRows)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400 font-sans">
                                <div class="flex flex-col items-center">
                                    <svg class="h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 11-12 0 6 6 0 0112 0zM2.25 21a8.966 8.966 0 015.06-8.006" />
                                    </svg>
                                    <p class="font-medium">No access tokens found</p>
                                    <p class="text-xs text-gray-400 mt-1">Create one from a property's Subscribed Services panel.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?= $accessTokenRows ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php
        $footerCountName = 'access-tokens';
        include __DIR__ . '/../components/ui/footer-count.php';
        ?>
    </div>
</div>

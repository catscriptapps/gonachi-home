<?php
// /resources/views/pages/adverts-admin.php

declare(strict_types=1);

/**
 * Real Estate World - Adverts Admin
 *
 * Admin-only moderation table: approve/deactivate/reject adverts. Route is
 * gated in public/index.php via NavigationConfig::getProtectedPaths() +
 * getAdminOnlyPaths() (login AND admin required). Ported from the legacy
 * gonachi/ platform's adverts-admin.php.
 *
 * @var string $baseUrl
 * @var string $assetBase
 */

use Src\Controller\AdvertsController;

$breadcrumbs = [['label' => 'Adverts', 'href' => $baseUrl . 'adverts'], ['label' => 'Admin']];
$breadcrumbAccent = 'teal';

$tabs = [
    'all' => 'All',
    'pending' => 'Pending Review',
    'active' => 'Active',
    'inactive' => 'Inactive',
    'rejected' => 'Rejected',
];

$currentTab = $_GET['tab'] ?? 'all';
if (!array_key_exists($currentTab, $tabs)) {
    $currentTab = 'all';
}

$search = trim((string) ($_GET['q'] ?? '')) ?: null;

$adverts = AdvertsController::adminList($search, $currentTab);

$rowsHtml = '';
foreach ($adverts as $advert) {
    $rowsHtml .= AdvertsController::renderAdminRow($advert);
}
?>
<div id="adverts-administration" class="space-y-6">
    <?php include __DIR__ . '/../components/breadcrumbs.php'; ?>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white font-sans tracking-tight">Adverts Administration</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 font-medium">Review, approve, deactivate, or reject submitted adverts.</p>
        </div>
        <div class="relative w-full md:w-64">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input type="text" id="admin-ad-search-input" data-current-tab="<?= htmlspecialchars($currentTab) ?>" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Search title, description, owner..." class="w-full pl-9 pr-3 py-2 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none text-gray-900 dark:text-white" />
        </div>
    </div>

    <div class="flex flex-wrap gap-2" id="admin-ad-tabs">
        <?php foreach ($tabs as $slug => $label): ?>
            <a href="<?= $baseUrl ?>adverts-admin?tab=<?= $slug ?><?= $search ? '&q=' . urlencode($search) : '' ?>" data-partial data-tab="<?= $slug ?>"
                class="admin-ad-tab-link px-4 py-2 rounded-lg text-xs font-bold transition-colors <?= $currentTab === $slug ? 'bg-teal-600 text-white' : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800' ?>">
                <?= htmlspecialchars($label) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 dark:bg-gray-800/60 border-b border-gray-200 dark:border-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Advert Details</th>
                        <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Owner</th>
                        <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Package</th>
                        <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Created</th>
                        <th class="px-4 py-3 text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Status</th>
                    </tr>
                </thead>
                <tbody id="adverts-tbody" class="divide-y divide-gray-100 dark:divide-gray-800">
                    <?= $rowsHtml ?>
                </tbody>
            </table>
        </div>

        <?php if ($rowsHtml === ''): ?>
            <div id="empty-ads-state" class="p-10 text-center">
                <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300">No adverts in this view</h4>
            </div>
        <?php endif; ?>
    </div>

    <div id="ads-infinite-sentinel" class="h-4"></div>

    <?php include __DIR__ . '/../components/adverts/view-advert-modal.php'; ?>
</div>

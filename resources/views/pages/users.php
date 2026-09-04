<?php
// /resources/views/pages/users.php

declare(strict_types=1);

$controller = new \Src\Controller\UsersController();
$controller->index();

$userRows = $GLOBALS['userRows'] ?? '';
?>

<?php
/**
 * overflow-x-clip (not overflow-x-hidden) — "hidden" is a scrollable-overflow
 * value, which makes this div a scroll container and traps the table's
 * sticky thead below to ITS bounds instead of the page's. "clip" still
 * clips horizontal overflow but isn't a scroll container, so sticky
 * correctly resolves to the real page scroll instead.
 */
?>
<div class="space-y-6 max-w-full overflow-x-clip">
    <?php
    $breadcrumbs = ['Admin Dashboard' => $baseUrl . 'admin', 'Users' => '/users'];
    include __DIR__ . '/../components/ui/breadcrumbs.php';
    ?>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white font-sans">Users</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                A list of all Gonachi users including their location, account status, and professional roles.
            </p>
        </div>

        <div class="mt-4 md:mt-0 flex flex-row gap-3 items-center">
            <div class="relative flex-1 md:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" id="users-search"
                    class="block w-full rounded-xl border-2 border-gray-400 dark:border-gray-600 bg-white dark:bg-gray-900 py-2 pl-10 pr-3 text-sm placeholder-gray-400 focus:border-primary-500 focus:ring-primary-500 text-gray-900 dark:text-white transition-all font-sans"
                    placeholder="Search users...">
            </div>

            <button type="button" id="add-user-btn" data-tooltip="Add User"
                class="shrink-0 flex items-center justify-center rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-bold text-white shadow-md hover:bg-primary-600 transition-all active:scale-95 focus:ring-2 focus:ring-primary-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                <svg class="w-5 h-5 md:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="hidden xs:inline md:inline">Add User</span>
            </button>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-800 rounded-2xl">
        <?php /* No overflow-hidden here — an overflow-anything ancestor traps
                 position:sticky descendants to ITS OWN (non-scrolling) box
                 instead of the page, which is exactly what breaks the sticky
                 header below. Corner rounding is done directly on the thead/
                 tbody instead of via ancestor clipping. */ ?>
        <div class="w-full overflow-x-auto lg:overflow-visible">
            <table class="w-full divide-y divide-gray-200 dark:divide-gray-800 table-fixed">
                <?php
                // Sticky under the topbar (layout-header.php is h-20 = 80px,
                // sticky top-0 z-40) — both header rows below stick together
                // as a 2-row unit at top-20, just under it, z-30 so the
                // topbar stays on top when they touch.
                $sortIcon = '<svg class="sort-arrow h-3 w-3 opacity-0 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" /></svg>';
                $filterInputClasses = 'w-full text-xs font-normal normal-case px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-1 focus:ring-primary-400 focus:border-primary-400 focus:outline-none text-gray-700 dark:text-gray-300 placeholder-gray-400 dark:placeholder-gray-500';
                ?>
                <thead class="sticky top-20 z-30 bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-full md:w-[30%] rounded-tl-2xl">
                            <button type="button" data-sort-key="name" class="sort-header-btn inline-flex items-center gap-1 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                <span class="lg:hidden">User Details</span>
                                <span class="hidden lg:inline">User</span>
                                <?= $sortIcon ?>
                            </button>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell w-[20%]">
                            <button type="button" data-sort-key="location" class="sort-header-btn inline-flex items-center gap-1 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                Location <?= $sortIcon ?>
                            </button>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell w-[20%]">Roles / Types</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell w-[15%]">
                            <button type="button" data-sort-key="joined" class="sort-header-btn inline-flex items-center gap-1 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                Activity <?= $sortIcon ?>
                            </button>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell w-[100px]">
                            <button type="button" data-sort-key="status" class="sort-header-btn inline-flex items-center gap-1 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                Status <?= $sortIcon ?>
                            </button>
                        </th>
                        <th class="relative px-6 py-4 text-right w-24 hidden lg:table-cell rounded-tr-2xl">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                    <tr class="hidden lg:table-row border-t border-gray-100 dark:border-gray-800">
                        <th class="px-6 py-2.5">
                            <input type="text" data-filter-key="name" placeholder="Filter name or email&hellip;" class="<?= $filterInputClasses ?>" />
                        </th>
                        <th class="px-6 py-2.5 hidden lg:table-cell">
                            <input type="text" data-filter-key="location" placeholder="Filter location&hellip;" class="<?= $filterInputClasses ?>" />
                        </th>
                        <th class="px-6 py-2.5 hidden lg:table-cell">
                            <input type="text" data-filter-key="roles" placeholder="Filter roles&hellip;" class="<?= $filterInputClasses ?>" />
                        </th>
                        <th class="px-6 py-2.5 hidden lg:table-cell"></th>
                        <th class="px-6 py-2.5 hidden lg:table-cell">
                            <input type="text" data-filter-key="status" placeholder="Current / Archived" class="<?= $filterInputClasses ?>" />
                        </th>
                        <th class="px-6 py-2.5 hidden lg:table-cell"></th>
                    </tr>
                </thead>
                <tbody id="users-tbody" class="divide-y divide-gray-200 dark:divide-gray-800 bg-white dark:bg-gray-900">
                    <?php if (empty($userRows)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center">
                                    <svg class="h-12 w-12 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <p class="font-medium font-sans">No users found</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?= $userRows ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="rounded-b-2xl overflow-hidden">
            <?php $footerCountName = 'users';
            include __DIR__ . '/../components/ui/footer-count.php'; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../components/users/view-user-modal.php'; ?>
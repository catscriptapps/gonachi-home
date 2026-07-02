<?php
// /resources/views/pages/users.php

declare(strict_types=1);

$controller = new \Src\Controller\UsersController();
$controller->index();

$userRows = $GLOBALS['userRows'] ?? '';
?>

<div class="space-y-6 max-w-full overflow-x-hidden">
    <?php
    $breadcrumbs = ['Users' => '/users'];
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

    <div class="bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-800 rounded-2xl overflow-hidden">
        <div class="w-full overflow-hidden">
            <table class="w-full divide-y divide-gray-200 dark:divide-gray-800 table-fixed">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-full md:w-[30%]">
                            <span class="lg:hidden">User Details</span>
                            <span class="hidden lg:inline">User</span>
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell w-[20%]">Location</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell w-[20%]">Roles / Types</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell w-[15%]">Activity</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden lg:table-cell w-[100px]">Status</th>
                        <th class="relative px-6 py-4 text-right w-24 hidden lg:table-cell">
                            <span class="sr-only">Actions</span>
                        </th>
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

        <?php $footerCountName = 'users';
        include __DIR__ . '/../components/ui/footer-count.php'; ?>
    </div>
</div>

<?php include __DIR__ . '/../components/users/view-user-modal.php'; ?>
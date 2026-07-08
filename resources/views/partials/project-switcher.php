<?php
// /resources/views/partials/project-switcher.php

declare(strict_types=1);

/**
 * "Switch Project" card — dropped into each project's sidebar nav, a good
 * margin below that project's own nav items. Lets a visitor jump straight
 * to another project without going back through the portal hub first.
 *
 * @var string $currentProjectSlug Set by the including sidebar before this include.
 */

use Src\Config\ProjectsConfig;

$switcherIconClasses = [
    'primary' => 'bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400',
    'secondary' => 'bg-secondary-50 dark:bg-secondary-950/40 text-secondary-600 dark:text-secondary-400',
    'indigo' => 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400',
];
?>
<div class="mt-10" x-show="$store.sidebar.expanded || mobileMenuOpen">
    <p class="px-3 mb-2 text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-600">&nbsp;</p>
    <div class="bg-gray-50 dark:bg-gray-800/40 border border-gray-100 dark:border-gray-800 rounded-2xl p-2 space-y-1">
        <?php foreach (ProjectsConfig::all() as $project): ?>
            <?php $isCurrent = $project['slug'] === $currentProjectSlug; ?>
            <a href="<?= $baseUrl . $project['slug'] ?>"
                class="flex items-center gap-3 px-2.5 py-2 rounded-xl text-sm transition-colors <?= $isCurrent ? 'bg-white dark:bg-gray-900 shadow-sm font-semibold text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400 hover:bg-white dark:hover:bg-gray-900' ?>">
                <span class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 <?= $switcherIconClasses[$project['accent']] ?>">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><?= $project['icon'] ?></svg>
                </span>
                <span class="leading-tight"><?= htmlspecialchars($project['name']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>

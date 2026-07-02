<?php
// /resources/views/components/ui/breadcrumbs.php

declare(strict_types=1);

/**
 * @var array $breadcrumbs An array of ['label' => 'url'] pairs. The last item is the current page.
 * @var string $baseUrl
 */

if (!isset($breadcrumbs) || empty($breadcrumbs)) {
    return;
}

$homeUrl = $baseUrl;
$lastItemLabel = array_key_last($breadcrumbs);
?>

<nav class="flex items-center gap-2 mb-10 text-sm font-black uppercase tracking-wider text-slate-400 dark:text-slate-500" aria-label="Breadcrumb" data-aos="fade-down">
    <a href="<?= $homeUrl ?>" data-partial data-title="Home" data-summary="Centralized Landlord Infrastructure" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Home</a>

    <?php foreach ($breadcrumbs as $label => $url) : ?>
        <i class="fa-solid fa-angle-right text-[10px] text-slate-300 dark:text-slate-700 stroke-[3]"></i>
        <?php if ($label === $lastItemLabel) : ?>
            <span class="text-slate-900 dark:text-white font-black" aria-current="page"><?= htmlspecialchars($label) ?></span>
        <?php else : ?>
            <a href="<?= htmlspecialchars($url) ?>" data-partial class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors"><?= htmlspecialchars($label) ?></a>
        <?php endif; ?>
    <?php endforeach; ?>
</nav>
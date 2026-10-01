<?php
// /resources/views/components/country-switcher.php
//
// Small flag+name pill switcher for the 3 country-scoped project landing
// pages (real-estate-leads, contractor-discovery, landlord-tenant-validation
// — see CountryScope). Included with:
//   $countrySwitcherProject = 'contractor-discovery';
//   $countrySwitcherCurrent = $countryCode;
//   include __DIR__ . '/../components/country-switcher.php';
//
// @var string $baseUrl
// @var string $countrySwitcherProject
// @var string $countrySwitcherCurrent

use Src\Utils\CountryScope;
?>
<div class="inline-flex items-center gap-1 bg-gray-100 dark:bg-gray-800 rounded-full p-1">
    <?php foreach (CountryScope::IDS as $cc => $id): ?>
        <?php $isActive = $cc === $countrySwitcherCurrent; ?>
        <a href="<?= $baseUrl . $countrySwitcherProject . '/' . $cc ?>" data-partial
            class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold transition-colors <?= $isActive
                ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm'
                : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' ?>">
            <span><?= CountryScope::FLAGS[$cc] ?></span>
            <span><?= CountryScope::NAMES[$cc] ?></span>
        </a>
    <?php endforeach; ?>
</div>

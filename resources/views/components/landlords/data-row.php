<?php
// /resources/views/components/landlords/data-row.php

/** @var array $rowItem */
/** @var string $assetBase */

$hasAvatar = !empty($rowItem['logo_url']);
$LOGO_DIR_PREFIX = $assetBase . 'images/uploads/logos/';
$logoUrl = $hasAvatar ? htmlspecialchars($LOGO_DIR_PREFIX . $rowItem['logo_url']) : '';

$companyName = $rowItem['company_name'] ?? 'Unknown Entity';

// Prepare data attributes for the Landlords View / Edit Modals
$landlordDataAttrs = [
    'encoded-id'   => $rowItem['encoded_id'] ?? '',
    'company-name' => $companyName,
    'tax-id'       => $rowItem['tax_id'] ?? 'N/A',
    'email'        => $rowItem['email'] ?? '',
    'phone'        => $rowItem['phone'] ?? 'N/A',
    'address-line1' => $rowItem['address_line1'] ?? '',
    'address-line2' => $rowItem['address_line2'] ?? '',
    'city'         => $rowItem['city'] ?? '',
    'country-id'   => $rowItem['country_id'] ?? 0,
    'region-id'    => $rowItem['region_id'] ?? 0,
    'region-name'  => $rowItem['region_name'] ?? 'N/A',
    'country-name' => $rowItem['country_name'] ?? 'N/A',
    'logo-url'     => $logoUrl,
    'joined'       => $rowItem['created_at_formatted'] ?? 'N/A',
    'is-active'    => (int)($rowItem['status_id'] ?? 0) === 1 ? '1' : '0'
];

$editClass = 'edit-landlord-btn';
$deleteClass = 'delete-landlord-btn';

$statusBadge = (int)($rowItem['status_id'] ?? 0) === 1
    ? '<span class="inline-flex items-center rounded-full bg-primary-50 dark:bg-primary-900/20 px-2.5 py-0.5 text-xs font-bold text-primary-600 dark:text-primary-400 border border-primary-100 dark:border-primary-800/30">Active</span>'
    : '<span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-800 px-2.5 py-0.5 text-xs font-bold text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700">Suspended</span>';
?>

<tr id="landlord-row-<?= $rowItem['id'] ?? '0' ?>"
    data-encoded-id="<?= $rowItem['encoded_id'] ?? '' ?>"
    class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group border-b border-gray-100 dark:border-gray-800 font-sans">

    <td class="px-6 py-4 min-w-0">
        <div class="flex items-start lg:items-center min-w-0">
            <?php if ($hasAvatar): ?>
                <img class="h-10 w-10 flex-shrink-0 rounded-full object-cover border border-gray-200 dark:border-gray-700"
                    src="<?= $logoUrl ?>" alt="<?= htmlspecialchars($companyName) ?>">
            <?php else: ?>
                <div class="h-10 w-10 flex-shrink-0 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 dark:text-primary-400 font-bold text-lg">
                    <?= strtoupper(substr($companyName, 0, 1)) ?>
                </div>
            <?php endif; ?>

            <div class="ml-4 flex-1 min-w-0">
                <div class="view-landlord-trigger cursor-pointer block min-w-0"
                    <?php foreach ($landlordDataAttrs as $key => $val): ?>
                    data-<?= $key ?>='<?= htmlspecialchars((string)$val, ENT_QUOTES) ?>'
                    <?php endforeach; ?>>

                    <div class="flex items-center justify-between lg:block">
                        <div class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-primary-600 transition-colors truncate">
                            <?= htmlspecialchars($companyName) ?>
                        </div>
                        <div class="lg:hidden flex-shrink-0 ml-2">
                            <?= $statusBadge ?>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                        <?= htmlspecialchars($rowItem['email'] ?? '') ?>
                    </div>
                </div>

                <div class="mt-3 lg:hidden flex items-center gap-2">
                    <?php
                    $isMobile = true;
                    $dataAttrs = $landlordDataAttrs;
                    include __DIR__ . '/../ui/action-buttons.php';
                    ?>
                </div>
            </div>
        </div>
    </td>

    <td class="px-6 py-4 hidden lg:table-cell">
        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
            <?= htmlspecialchars($rowItem['city'] ?? 'N/A') ?>
        </div>
        <div class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-tight mt-0.5">
            <?= htmlspecialchars($rowItem['region_name'] ?? 'N/A') ?>, <?= htmlspecialchars($rowItem['country_name'] ?? 'N/A') ?>
        </div>
    </td>

    <td class="px-6 py-4 hidden lg:table-cell">
        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-mono font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
            <?= htmlspecialchars($rowItem['tax_id'] ?? 'N/A') ?>
        </span>
    </td>

    <td class="px-6 py-4 hidden lg:table-cell">
        <div class="text-[11px] text-gray-600 dark:text-gray-400">
            <span class="block font-bold text-gray-400 uppercase text-[9px] tracking-widest">Registered</span>
            <?= $rowItem['created_at_formatted'] ?? 'N/A' ?>
        </div>
    </td>

    <td class="px-6 py-4 hidden lg:table-cell">
        <?= $statusBadge ?>
    </td>

    <td class="px-6 py-4 text-right hidden lg:table-cell">
        <?php
        $isMobile = false;
        $dataAttrs = $landlordDataAttrs;
        include __DIR__ . '/../ui/action-buttons.php';
        ?>
    </td>
</tr>
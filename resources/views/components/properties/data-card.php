<?php
// /resources/views/components/properties/data-card.php

/** @var array $item */
/** @var string $assetBase */

$currentLandlord = \Src\Service\AuthService::currentLandlord();
$isOwner = $currentLandlord && (int)$currentLandlord->id === (int)($item['landlord_id'] ?? 0);

// Avatar Logic
$hasAvatar = !empty($item['landlord_avatar']);
$AVATAR_DIR_PREFIX = $assetBase . 'images/uploads/avatars/';
$avatarUrl = $hasAvatar ? htmlspecialchars($AVATAR_DIR_PREFIX . $item['landlord_avatar']) : '';
$ownerFullName = $item['landlord_name'] ?? 'Unknown Landlord';
$initial = strtoupper(substr($ownerFullName, 0, 1));
$ownerLocation = $item['owner_location'] ?? 'N/A';

// Data attributes for JS modals
$propertyDataAttrs = [
    'encoded-id'      => $item['encoded_id'] ?? '',
    'property-name'   => $item['property_name'] ?? '',
    'unit-number'     => $item['unit_number'] ?? '',
    'landlord-id'     => $item['landlord_id'] ?? 0,
    'landlord-name'   => $item['landlord_name'] ?? 'N/A',
    'address-line1'   => $item['address_line1'] ?? '',
    'city'            => $item['city'] ?? '',
    'postal-code'     => $item['postal_code'] ?? '',
    'country-id'      => $item['country_id'] ?? 0,
    'region-id'       => $item['region_id'] ?? 0,
    'region-name'     => $item['region_name'] ?? 'N/A',
    'country-name'    => $item['country_name'] ?? 'N/A',
    'created'         => $item['created_at_formatted'] ?? 'N/A',
    'is-active'       => (int)($item['is_active'] ?? 1) === 1 ? '1' : '0',
    'views-count'     => $item['views'] ?? 0,
    // Owner data for modal-detail-owner component
    'owner-avatar'    => !empty($item['landlord_avatar'])
        ? ($assetBase . 'images/uploads/avatars/' . $item['landlord_avatar'])
        : '',
    'owner-location'  => $item['owner_location'] ?? 'N/A',
    'user-types-json' => $item['user_types_json'] ?? '["Landlord"]',
    'active-tokens-by-service' => json_encode($item['active_tokens_by_service'] ?? []),
];

$editClass   = 'edit-property-btn';
$deleteClass = 'delete-property-btn';

$isActive = (int)($item['is_active'] ?? 1) === 1;

$statusBadge = $isActive
    ? '<span class="inline-flex items-center rounded-full bg-emerald-50 dark:bg-emerald-900/20 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/30 shadow-sm">Active</span>'
    : '<span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-800 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700 shadow-sm">Inactive</span>';

$propertyName  = $item['property_name'] ?? 'Unnamed Property';
$fullAddress   = implode(', ', array_filter([
    $item['address_line1'] ?? null,
    $item['city'] ?? null,
    $item['region_name'] ?? null,
]));
$unitLabel     = !empty($item['unit_number']) ? 'Unit ' . $item['unit_number'] : 'Whole Building';
$thumbnailUrl  = !empty($item['thumbnail']) ? $assetBase . 'images/uploads/properties/' . $item['thumbnail'] : null;
$viewsCountId  = 'property-views-count-' . ($item['id'] ?? '0');
?>

<div id="property-card-<?= $item['id'] ?? '0' ?>"
    data-encoded-id="<?= $item['encoded_id'] ?? '' ?>"
    class="property-card-wrapper bg-white dark:bg-gray-950 rounded-2xl shadow-md hover:shadow-xl border border-gray-100 dark:border-gray-800 transition-all duration-300 group flex flex-col h-full font-sans relative overflow-hidden">

    <div class="relative h-36 flex-shrink-0 bg-gray-50 dark:bg-gray-900/40">
        <?php if ($thumbnailUrl): ?>
            <img src="<?= $thumbnailUrl ?>"
                alt="<?= htmlspecialchars($propertyName) ?>"
                class="w-full h-full object-cover grayscale-[0.3] group-hover:grayscale-0 transition-all duration-500 scale-100 group-hover:scale-105">
        <?php else: ?>
            <div class="w-full h-full flex items-center justify-center border-b border-dashed border-gray-200 dark:border-gray-800">
                <span class="text-[10px] font-black text-gray-300 dark:text-gray-700 uppercase tracking-widest">No Photos Yet</span>
            </div>
        <?php endif; ?>

        <div class="absolute top-3 left-3"><?= $statusBadge ?></div>

        <?php if ($isOwner): ?>
            <div class="absolute top-2 right-2 z-10 hidden lg:flex items-center gap-1 bg-white/95 dark:bg-gray-950/90 rounded-lg shadow-sm p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                <?php
                $isMobile = false;
                $dataAttrs = $propertyDataAttrs;
                include __DIR__ . '/../ui/action-buttons.php';
                ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="p-5 flex flex-col flex-grow view-property-trigger cursor-pointer"
        <?php foreach ($propertyDataAttrs as $key => $val): ?>
        data-<?= $key ?>="<?= htmlspecialchars((string)$val, ENT_QUOTES) ?>"
        <?php endforeach; ?>>

        <?php include __DIR__ . '/../ui/card-owner.php'; ?>

        <div class="flex items-center justify-between gap-2 mb-1">
            <span class="text-[10px] font-black text-primary-500 uppercase tracking-widest"><?= htmlspecialchars($unitLabel) ?></span>

            <div class="flex items-center gap-1 flex-shrink-0">
                <svg class="w-3 h-3 text-orange-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span id="<?= $viewsCountId ?>" class="text-[10px] font-black text-gray-400 uppercase tracking-wider">
                    <?= number_format((int)($item['views'] ?? 0)) ?>
                </span>
            </div>
        </div>

        <h3 class="text-lg font-black text-gray-900 dark:text-white mb-1 group-hover:text-primary-500 transition-colors line-clamp-1">
            <?= htmlspecialchars($propertyName) ?>
        </h3>

        <div class="flex items-center gap-1.5 text-[11px] font-bold text-gray-400 mb-3 min-w-0">
            <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span class="truncate"><?= htmlspecialchars($fullAddress ?: 'No address provided.') ?></span>
        </div>

        <div class="mt-auto flex items-center gap-1.5 text-[10px] font-bold text-gray-400 uppercase tracking-tight border-t border-gray-100 dark:border-gray-800 pt-3">
            <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>Registered <?= htmlspecialchars($item['created_at_formatted'] ?? 'N/A') ?></span>
        </div>

        <?php if ($isOwner): ?>
            <div class="mt-3 lg:hidden border-t border-gray-100 dark:border-gray-800 pt-3">
                <?php
                $isMobile = true;
                $dataAttrs = $propertyDataAttrs;
                include __DIR__ . '/../ui/action-buttons.php';
                ?>
            </div>
        <?php endif; ?>
    </div>
</div>

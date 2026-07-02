<?php
// /resources/views/components/listings/data-card.php

/** @var array $item */
/** @var string $assetBase */

$owner = $item['user'] ?? $item['owner'] ?? null;

// 💎 Ownership Logic
$currentUserId = \Src\Service\AuthService::userId();
$isOwner = $owner && (int)($owner['id'] ?? 0) === (int)$currentUserId;

// Avatar Logic (Exact copy from users data-row)
$hasAvatar = !empty($owner['avatar_url']);
$AVATAR_DIR_PREFIX = $assetBase . 'images/uploads/avatars/';
$avatarUrl = $hasAvatar ? htmlspecialchars($AVATAR_DIR_PREFIX . $owner['avatar_url']) : '';

$ownerFullName = $owner ? trim(($owner['first_name'] ?? '') . ' ' . ($owner['last_name'] ?? '')) : 'Unknown Swapper';
$initial = strtoupper(substr($owner['first_name'] ?? 'U', 0, 1));

// 💎 Picture Logic
$hasThumbnail = !empty($item['thumbnail']);
$thumbnailUrl = $hasThumbnail ? $assetBase . 'images/uploads/listings/' . $item['thumbnail'] : null;

// Prepare data attributes for JS (Gonachi Swap Style)
$listingDataAttrs = [
    'encoded-id'          => $item['encoded_id'] ?? '',
    'listing-title'       => $item['listing_title'] ?? '',
    'listing-description' => $item['listing_description'] ?? '',
    'city'                => $item['city'] ?? '',

    // Geography
    'country-id'          => $item['country_id'] ?? '',
    'country-name'        => $item['country_name'] ?? '',
    'region-id'           => $item['region_id'] ?? '',
    'region-name'         => $item['region_name'] ?? '',

    // Classification
    'category-id'         => $item['category_id'] ?? '',
    'category-name'       => $item['category_label'] ?? '',
    'type-id'             => $item['type_id'] ?? '',
    'type-name'           => $item['type_label'] ?? 'Swap',
    'condition-id'        => $item['condition_id'] ?? '',
    'condition-name'      => $item['condition_label'] ?? 'Used',

    // Logistics & Contact
    'price'               => $item['price'] ?? 'Trade',
    'trade-pref'          => $item['trade_pref'] ?? 'None',
    'contact-phone'       => $item['contact_phone'] ?? '',
    'youtube-url'         => $item['youtube_url'] ?? '',

    // Owner Data 
    'owner-name'          => $ownerFullName,
    'owner-avatar'        => $avatarUrl,
    'owner-initial'       => $initial,
    'owner-region'        => $item['owner_region'] ?? 'Unknown Region',
    'owner-country'       => $item['owner_country'] ?? 'Unknown Country',
    'owner-id'            => (int)$item['orig_user_id'],
    'user-types'          => $item['user_types_json'] ?? '["Swapper"]',

    // Meta
    'views-count'         => $item['views'] ?? 0,
    'status-id'           => (int)($item['status_id'] ?? 1),
    'created-at'          => $item['created_at_formatted'] ?? 'N/A',
    'updated-at'          => $item['updated_at_formatted'] ?? ($item['created_at_formatted'] ?? 'N/A')
];

$editClass = 'edit-listing-btn';
$deleteClass = 'delete-listing-btn';

// Status Badge Logic 💎
$statusBadge = match ((int)($item['status_id'] ?? 0)) {
    1       => '<span class="inline-flex items-center rounded-full bg-green-50 dark:bg-green-900/20 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-green-600 dark:text-green-400 border border-green-100 dark:border-green-800/30">Active</span>',
    2       => '<span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-800 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700">Archived</span>',
    default => '<span class="inline-flex items-center rounded-full bg-primary-50 dark:bg-primary-900/20 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-primary-400 dark:text-primary-300 border border-primary-100 dark:border-primary-800/30">Draft</span>'
};

$categoryLabel = $item['category_label'] ?? 'General';
$locationLabel = (!empty($item['city']) ? $item['city'] . ', ' : '') . ($item['region_name'] ?? '');
$ownerLocation = $item['owner_location'] ?? 'Unknown';
$statusId = (int)($item['status_id'] ?? 0);
?>

<div id="listing-card-<?= $item['listing_id'] ?? '0' ?>"
    data-encoded-id="<?= $item['encoded_id'] ?? '' ?>"
    class="listing-card-wrapper bg-white dark:bg-gray-950 rounded-[2rem] shadow-md hover:shadow-xl border border-gray-100 dark:border-secondary-900 transition-all duration-300 group flex flex-col h-full font-sans relative"
    data-aos="fade-up">

    <div class="px-6 pt-6 flex justify-between items-start">
        <?php
        $viewsCountId = 'listing-views-count-' . ($item['listing_id'] ?? '0');
        include __DIR__ . '/../ui/status-badge-and-views-count.php';
        ?>

        <?php if ($isOwner): ?>
            <div class="absolute top-6 right-4 hidden lg:block z-10">
                <?php
                $isMobile = false;
                $dataAttrs = $listingDataAttrs;
                include __DIR__ . '/../ui/action-buttons.php';
                ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="p-6 flex-grow view-listing-trigger cursor-pointer"
        <?php foreach ($listingDataAttrs as $key => $val): ?>
        data-<?= $key ?>="<?= htmlspecialchars((string)$val, ENT_QUOTES) ?>"
        <?php endforeach; ?>>

        <?php include __DIR__ . '/../ui/card-owner.php'; ?>

        <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
            <span class="text-[10px] font-black text-primary-500 uppercase tracking-widest"><?= htmlspecialchars($categoryLabel) ?></span>

            <div class="flex items-center gap-1.5">
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 border border-primary-100 dark:border-primary-900/30 shadow-sm">
                    <?= htmlspecialchars($item['type_label'] ?? 'Standard') ?>
                </span>

                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-secondary-50 dark:bg-secondary-900/40 text-secondary-600 dark:text-secondary-400 border border-secondary-100 dark:border-secondary-800/30 shadow-sm">
                    <?= htmlspecialchars($item['condition_label'] ?? 'Used') ?>
                </span>
            </div>
        </div>

        <h3 class="text-xl font-black text-secondary-900 dark:text-white mb-2 group-hover:text-primary-500 transition-colors line-clamp-1">
            <?= htmlspecialchars($item['listing_title'] ?? 'Untitled Listing') ?>
        </h3>

        <?php if ($hasThumbnail): ?>
            <div class="mb-4 overflow-hidden rounded-2xl border border-gray-100 dark:border-secondary-800/50">
                <img src="<?= $thumbnailUrl ?>" class="w-full h-44 object-cover group-hover:scale-105 transition-transform duration-500" alt="<?= htmlspecialchars($item['listing_title']) ?>">
            </div>
        <?php endif; ?>

        <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed line-clamp-3 mb-6">
            <?= htmlspecialchars($item['listing_description'] ?? 'No description provided.') ?>
        </p>

        <?php if (!$hasThumbnail): ?>
            <div class="grid grid-cols-2 gap-y-3 gap-x-2 border-t border-gray-50 dark:border-secondary-800/50 pt-4">
                <div class="flex items-center gap-2 text-[11px] font-bold text-gray-400 uppercase tracking-tight">
                    <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="truncate"><?= htmlspecialchars($locationLabel ?: 'Remote / TBD') ?></span>
                </div>

                <div class="flex items-center gap-2 text-[11px] font-bold text-gray-400 uppercase tracking-tight">
                    <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    <span class="truncate"><?= htmlspecialchars($item['condition_label'] ?? 'Used') ?></span>
                </div>

                <div class="flex items-center gap-2 text-[11px] font-bold text-gray-400 uppercase tracking-tight">
                    <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zM17 11a5 5 0 11-10 0 5 5 0 0110 0z" />
                    </svg>
                    <span>Value: <?= !empty($item['price']) && $item['price'] !== 'Trade' ? '$' . htmlspecialchars((string)$item['price']) : 'Trade' ?></span>
                </div>

                <div class="flex items-center gap-2 text-[11px] font-bold text-gray-400 uppercase tracking-tight">
                    <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span class="truncate"><?= htmlspecialchars($item['type_label'] ?? 'Standard') ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($isOwner): ?>
            <div class="mt-6 lg:hidden border-t border-gray-100 dark:border-secondary-800 pt-4">
                <?php
                $isMobile = true;
                $dataAttrs = $listingDataAttrs;
                include __DIR__ . '/../ui/action-buttons.php';
                ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="p-6 mt-auto border-t border-gray-50 dark:border-secondary-800 bg-gray-50/30 dark:bg-secondary-900/20 rounded-b-[2rem]">
        <?php if ($isOwner): ?>
            <?php
            $isArchived = ((int)$statusId === 2);
            $triggerClass = $isArchived ? 'reactivate-listing-trigger' : 'deactivate-listing-trigger';
            $btnStyles = $isArchived
                ? 'bg-green-50 dark:bg-green-900/10 text-green-600 dark:text-green-400 border-green-100 dark:border-green-900/30 hover:bg-green-600 hover:text-white'
                : 'bg-red-50 dark:bg-red-900/10 text-red-600 dark:text-red-400 border-red-100 dark:border-red-900/30 hover:bg-red-600 hover:text-white';
            ?>
            <button type="button"
                data-encoded-id="<?= $item['encoded_id'] ?? '' ?>"
                class="<?= $triggerClass ?> w-full inline-flex justify-center items-center gap-2 px-4 py-3 <?= $btnStyles ?> border transition-all font-black text-sm rounded-xl active:scale-95"
                <?php foreach ($listingDataAttrs ?? [] as $key => $val): ?> data-<?= $key ?>="<?= htmlspecialchars((string)$val, ENT_QUOTES) ?>" <?php endforeach; ?>>

                <?= $isArchived ? 'Reactivate Swap' : 'End Listing' ?>

                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <?php if ($isArchived): ?>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    <?php else: ?>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    <?php endif; ?>
                </svg>
            </button>
        <?php else: ?>
            <button type="button"
                class="connect-listing-trigger w-full inline-flex justify-center items-center gap-2 px-4 py-3 bg-primary-500 hover:bg-secondary-950 text-secondary-950 hover:text-white font-black text-sm rounded-xl transition-all shadow-lg shadow-primary-500/20 active:scale-95"
                <?php foreach ($listingDataAttrs as $key => $val): ?> data-<?= $key ?>="<?= htmlspecialchars((string)$val, ENT_QUOTES) ?>" <?php endforeach; ?>>
                Contact Swapper
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </button>
        <?php endif; ?>
    </div>
</div>
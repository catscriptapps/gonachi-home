<?php
// /resources/views/components/listings/data-card.php
//
// One listing card for the public "Browse Listings" feed and "My Listings".
// Carries a data-* attribute payload (read by
// resources/js/utils/listings/view-listing-modal.js's JS counterpart) so
// clicking the card opens the shared view modal without a fetch. Category
// 2 ("Real Estate Services") and 3 ("Other") render as "service" listings —
// a single-column layout with no house/bedroom/bathroom/price row, matching
// legacy's isService split exactly.

/** @var array $data Built by ListingsController::buildItemArray() */
/** @var string $assetBase */

$title = $data['listing_title'];
$description = $data['listing_description'];
$thumbnail = $data['thumbnail'] ? $assetBase . 'images/uploads/listings/' . htmlspecialchars($data['thumbnail']) : null;
$ownerAvatarUrl = $data['owner_avatar'] ? $assetBase . 'images/uploads/avatars/' . htmlspecialchars($data['owner_avatar']) : null;

$categoryId = (int) $data['category_id'];
$isService = in_array($categoryId, [2, 3], true);

$isArchived = (int) $data['status_id'] === 2;
$statusBadge = $isArchived
    ? '<span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-800 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700">Archived</span>'
    : '<span class="inline-flex items-center rounded-full bg-green-50 dark:bg-green-900/20 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-green-600 dark:text-green-400 border border-green-100 dark:border-green-800/30">Active</span>';
$item = ['views' => $data['views']];

$ownerFullName = $data['owner_name'];
$initial = $data['owner_initial'];
$ownerLocation = $data['owner_location'];
$hasAvatar = (bool) $ownerAvatarUrl;
$avatarUrl = $ownerAvatarUrl ?? '';

$locationLabel = trim(($data['city'] ? $data['city'] . ', ' : '') . $data['region_name']);
$priceDisplay = $data['price'] ? $data['price'] : 'Contact for Price';
?>
<div class="listing-card-wrapper group relative flex flex-col bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm overflow-hidden hover:shadow-lg transition-all cursor-pointer view-listing-trigger"
    data-encoded-id="<?= $data['encoded_id'] ?>"
    data-listing-title="<?= htmlspecialchars($title) ?>"
    data-listing-description="<?= htmlspecialchars((string) $description) ?>"
    data-city="<?= htmlspecialchars((string) $data['city']) ?>"
    data-address="<?= htmlspecialchars((string) $data['address']) ?>"
    data-country-id="<?= (int) $data['country_id'] ?>"
    data-country-name="<?= htmlspecialchars($data['country_name']) ?>"
    data-region-id="<?= (int) $data['region_id'] ?>"
    data-region-name="<?= htmlspecialchars($data['region_name']) ?>"
    data-category-id="<?= $categoryId ?>"
    data-category-name="<?= htmlspecialchars($data['category_name']) ?>"
    data-category-type-id="<?= (int) $data['category_type_id'] ?>"
    data-category-type-name="<?= htmlspecialchars($data['category_type_name']) ?>"
    data-unit-type-id="<?= (int) $data['unit_type_id'] ?>"
    data-unit-type-name="<?= htmlspecialchars($data['unit_type_name']) ?>"
    data-house-type-id="<?= (int) $data['house_type_id'] ?>"
    data-house-type-name="<?= htmlspecialchars($data['house_type_name']) ?>"
    data-bedroom-id="<?= (int) $data['bedroom_id'] ?>"
    data-bedroom-label="<?= htmlspecialchars($data['bedroom_label']) ?>"
    data-bathroom-id="<?= (int) $data['bathroom_id'] ?>"
    data-bathroom-label="<?= htmlspecialchars($data['bathroom_label']) ?>"
    data-property-size="<?= htmlspecialchars((string) $data['property_size']) ?>"
    data-is-ac="<?= (int) $data['is_ac'] ?>"
    data-is-furnished="<?= (int) $data['is_furnished'] ?>"
    data-parking="<?= (int) $data['parking'] ?>"
    data-pets-allowed="<?= (int) $data['pets_allowed'] ?>"
    data-price="<?= htmlspecialchars((string) $data['price']) ?>"
    data-agreement-type-id="<?= (int) $data['agreement_type_id'] ?>"
    data-agreement-type-name="<?= htmlspecialchars($data['agreement_type_name']) ?>"
    data-move-in-date="<?= htmlspecialchars((string) $data['move_in_date']) ?>"
    data-amenities='<?= htmlspecialchars(json_encode($data['amenities']), ENT_QUOTES) ?>'
    data-amenities-collection='<?= htmlspecialchars(json_encode($data['amenities_data']), ENT_QUOTES) ?>'
    data-contact-phone="<?= htmlspecialchars((string) $data['contact_phone']) ?>"
    data-youtube-url="<?= htmlspecialchars((string) $data['youtube_url']) ?>"
    data-status-id="<?= (int) $data['status_id'] ?>"
    data-views-count="<?= (int) $data['views'] ?>"
    data-created="<?= $data['created_at']?->format('M j, Y') ?>"
    data-updated="<?= $data['updated_at']?->format('M j, Y') ?>"
    data-owner-id="<?= (int) $data['owner_id'] ?>"
    data-owner-name="<?= htmlspecialchars($data['owner_name']) ?>"
    data-owner-avatar="<?= htmlspecialchars($ownerAvatarUrl ?? '') ?>"
    data-owner-initial="<?= htmlspecialchars($data['owner_initial']) ?>"
    data-owner-location="<?= htmlspecialchars($data['owner_location']) ?>"
    data-is-card-owner="<?= $data['is_card_owner'] ? '1' : '0' ?>">

    <div class="p-5 pb-0">
        <?php include __DIR__ . '/../ui/card-owner.php'; ?>
    </div>

    <?php if ($data['is_card_owner']): ?>
        <div class="absolute top-4 right-4 flex items-center gap-1.5 z-10" onclick="event.stopPropagation()">
            <button type="button" class="edit-listing-btn p-1.5 rounded-lg bg-white/90 dark:bg-gray-800/90 text-gray-500 hover:text-teal-600 shadow-sm transition-colors" data-encoded-id="<?= $data['encoded_id'] ?>" title="Edit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
            </button>
            <button type="button" class="delete-listing-btn p-1.5 rounded-lg bg-white/90 dark:bg-gray-800/90 text-gray-500 hover:text-red-600 shadow-sm transition-colors" data-encoded-id="<?= $data['encoded_id'] ?>" title="Delete">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
            </button>
        </div>
    <?php endif; ?>

    <div class="px-5 pt-3">
        <div class="mb-2">
            <?php $viewsCountId = 'listing-views-count-' . $data['listing_id']; include __DIR__ . '/../ui/status-badge-and-views-count.php'; ?>
        </div>
        <span class="text-[10px] font-black text-teal-600 dark:text-teal-400 uppercase tracking-widest"><?= htmlspecialchars($data['category_name']) ?></span>
        <h3 class="text-base font-bold text-gray-900 dark:text-white leading-snug mt-0.5 mb-1.5"><?= htmlspecialchars($title) ?></h3>
    </div>

    <?php if ($thumbnail): ?>
        <div class="mx-5 mb-3 rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-800 h-40">
            <img src="<?= $thumbnail ?>" alt="<?= htmlspecialchars($title) ?>" class="w-full h-full object-cover">
        </div>
        <div class="px-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 mb-3"><?= htmlspecialchars((string) $description) ?></p>
        </div>
    <?php else: ?>
        <div class="px-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-3 mb-3"><?= htmlspecialchars((string) $description ?: 'No description provided.') ?></p>

            <?php if ($isService): ?>
                <div class="border-t border-gray-50 dark:border-gray-800 pt-3">
                    <div class="h-24 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-dashed border-gray-200 dark:border-gray-700 flex items-center justify-center">
                        <span class="text-[10px] font-black text-gray-300 dark:text-gray-600 uppercase tracking-widest">No Preview Available</span>
                    </div>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-2 gap-y-2 gap-x-2 border-t border-gray-50 dark:border-gray-800 pt-3">
                    <div class="flex items-center gap-2 text-[11px] font-bold text-gray-400 uppercase tracking-tight">
                        <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                        <span class="truncate"><?= htmlspecialchars($data['house_type_name'] ?: 'Residential') ?></span>
                    </div>
                    <div class="flex items-center gap-2 text-[11px] font-bold text-gray-400 uppercase tracking-tight">
                        <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                        <span class="truncate"><?= htmlspecialchars($priceDisplay) ?></span>
                    </div>
                    <div class="flex items-center gap-2 text-[11px] font-bold text-gray-400 uppercase tracking-tight col-span-2">
                        <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        <span class="truncate"><?= htmlspecialchars($locationLabel ?: 'TBD') ?></span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="mt-auto p-5 pt-3" onclick="event.stopPropagation()">
        <?php if ($data['is_card_owner']): ?>
            <?php
            $triggerClass = $isArchived ? 'reactivate-listing-trigger' : 'deactivate-listing-trigger';
            $btnStyles = $isArchived
                ? 'bg-green-50 dark:bg-green-900/10 text-green-600 dark:text-green-400 border-green-100 dark:border-green-900/30 hover:bg-green-600 hover:text-white'
                : 'bg-red-50 dark:bg-red-900/10 text-red-600 dark:text-red-400 border-red-100 dark:border-red-900/30 hover:bg-red-600 hover:text-white';
            ?>
            <button type="button" data-encoded-id="<?= $data['encoded_id'] ?>" class="<?= $triggerClass ?> w-full inline-flex justify-center items-center gap-2 px-4 py-2.5 <?= $btnStyles ?> border transition-all font-bold text-xs rounded-lg active:scale-95">
                <?= $isArchived ? 'Reactivate Listing' : 'End Listing' ?>
            </button>
        <?php else: ?>
            <button type="button" class="connect-listing-trigger w-full inline-flex justify-center items-center gap-2 px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs rounded-lg transition-colors shadow-sm active:scale-95"
                data-encoded-id="<?= $data['encoded_id'] ?>" data-owner-id="<?= (int) $data['owner_id'] ?>" data-listing-title="<?= htmlspecialchars($title) ?>">
                Contact Owner
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
            </button>
        <?php endif; ?>
    </div>
</div>

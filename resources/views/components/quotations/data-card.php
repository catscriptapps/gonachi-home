<?php
// /resources/views/components/quotations/data-card.php
//
// One quotation card for the public "Browse Quotations" feed and "My
// Quotations". Carries a data-* attribute payload (read by
// resources/js/utils/quotations/view-quotation-modal.js's JS counterpart)
// so clicking the card opens the shared view modal without a fetch.

/** @var array $data Built by QuotationsController::buildItemArray() */
/** @var string $assetBase */

$title = $data['title'];
$description = $data['description'];
$thumbnail = $data['thumbnail'] ? $assetBase . 'images/uploads/quotations/' . htmlspecialchars($data['thumbnail']) : null;
$ownerAvatarUrl = $data['owner_avatar'] ? $assetBase . 'images/uploads/avatars/' . htmlspecialchars($data['owner_avatar']) : null;

$isArchived = (int) $data['status_id'] === 2;
$statusBadge = $isArchived
    ? '<span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-800 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700">Archived</span>'
    : '<span class="inline-flex items-center rounded-full bg-green-50 dark:bg-green-900/20 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-green-600 dark:text-green-400 border border-green-100 dark:border-green-800/30">Active</span>';
$viewsCountId = 'view-quote-' . $data['encoded_id'] . '-views-count';
$item = ['views' => $data['views']];

$hasAvatar = (bool) $ownerAvatarUrl;
$avatarUrl = $ownerAvatarUrl ?? '';
$ownerFullName = $data['owner_name'];
$initial = $data['owner_initial'];
$ownerLocation = $data['owner_location'];

$locationLabel = trim(($data['city'] ? $data['city'] . ', ' : '') . $data['region_name']);
$sTime = $data['start_time'] ? date('g:i A', strtotime($data['start_time'])) : 'N/A';
$fTime = $data['finish_time'] ? date('g:i A', strtotime($data['finish_time'])) : 'N/A';
?>
<div class="quote-card-wrapper group relative flex flex-col bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm overflow-hidden hover:shadow-lg transition-all cursor-pointer view-quote-trigger"
    data-encoded-id="<?= $data['encoded_id'] ?>"
    data-title="<?= htmlspecialchars($title) ?>"
    data-description="<?= htmlspecialchars($description) ?>"
    data-city="<?= htmlspecialchars((string) $data['city']) ?>"
    data-country-id="<?= (int) $data['country_id'] ?>"
    data-country-name="<?= htmlspecialchars($data['country_name']) ?>"
    data-region-id="<?= (int) $data['region_id'] ?>"
    data-region-name="<?= htmlspecialchars($data['region_name']) ?>"
    data-contractor-type-id="<?= (int) $data['contractor_type_id'] ?>"
    data-contractor-type-name="<?= htmlspecialchars($data['contractor_type_name']) ?>"
    data-skilled-trade-id="<?= (int) $data['skilled_trade_id'] ?>"
    data-skilled-trade-name="<?= htmlspecialchars($data['skilled_trade_name']) ?>"
    data-unit-type-id="<?= (int) $data['unit_type_id'] ?>"
    data-unit-type-name="<?= htmlspecialchars($data['unit_type_name']) ?>"
    data-house-type-id="<?= (int) $data['house_type_id'] ?>"
    data-house-type-name="<?= htmlspecialchars($data['house_type_name']) ?>"
    data-quotation-type-id="<?= (int) $data['quotation_type_id'] ?>"
    data-quotation-type-name="<?= htmlspecialchars($data['quotation_type_name']) ?>"
    data-quotation-dest-id="<?= (int) $data['quotation_dest_id'] ?>"
    data-quotation-dest-name="<?= htmlspecialchars($data['quotation_dest_name']) ?>"
    data-budget="<?= htmlspecialchars((string) $data['budget']) ?>"
    data-start-date="<?= htmlspecialchars((string) $data['start_date']) ?>"
    data-finish-date="<?= htmlspecialchars((string) $data['finish_date']) ?>"
    data-start-time="<?= htmlspecialchars((string) $data['start_time']) ?>"
    data-finish-time="<?= htmlspecialchars((string) $data['finish_time']) ?>"
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
            <button type="button" class="edit-quote-btn p-1.5 rounded-lg bg-white/90 dark:bg-gray-800/90 text-gray-500 hover:text-teal-600 shadow-sm transition-colors" data-encoded-id="<?= $data['encoded_id'] ?>" title="Edit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
            </button>
            <button type="button" class="delete-quote-btn p-1.5 rounded-lg bg-white/90 dark:bg-gray-800/90 text-gray-500 hover:text-red-600 shadow-sm transition-colors" data-encoded-id="<?= $data['encoded_id'] ?>" title="Delete">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
            </button>
        </div>
    <?php endif; ?>

    <div class="px-5 pt-3">
        <div class="mb-2">
            <?php include __DIR__ . '/../ui/status-badge-and-views-count.php'; ?>
        </div>
        <span class="text-[10px] font-black text-teal-600 dark:text-teal-400 uppercase tracking-widest"><?= htmlspecialchars($data['skilled_trade_name']) ?></span>
        <h3 class="text-base font-bold text-gray-900 dark:text-white leading-snug mt-0.5 mb-1.5"><?= htmlspecialchars($title) ?></h3>
    </div>

    <?php if ($thumbnail): ?>
        <div class="mx-5 mb-3 rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-800 h-40">
            <img src="<?= $thumbnail ?>" alt="<?= htmlspecialchars($title) ?>" class="w-full h-full object-cover">
        </div>
        <div class="px-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 mb-3"><?= htmlspecialchars($description) ?></p>
        </div>
    <?php else: ?>
        <div class="px-5">
            <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-3 mb-3"><?= htmlspecialchars($description) ?></p>

            <div class="grid grid-cols-2 gap-y-2 gap-x-2 border-t border-gray-50 dark:border-gray-800 pt-3">
                <div class="flex items-center gap-2 text-[11px] font-bold text-gray-400 uppercase tracking-tight">
                    <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    <span class="truncate"><?= htmlspecialchars($locationLabel ?: 'Remote / TBD') ?></span>
                </div>
                <div class="flex items-center gap-2 text-[11px] font-bold text-gray-400 uppercase tracking-tight">
                    <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zM17 11a5 5 0 11-10 0 5 5 0 0110 0z" /></svg>
                    <span>Budget: <?= $data['budget'] ? htmlspecialchars((string) $data['budget']) : 'Open' ?></span>
                </div>
                <div class="flex items-center gap-2 text-[11px] font-bold text-gray-400 uppercase tracking-tight col-span-2">
                    <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v12a2 2 0 002 2z" /></svg>
                    <span class="truncate"><?= htmlspecialchars($data['start_date'] ?: 'TBD') ?> &ndash; <?= htmlspecialchars($data['finish_date'] ?: 'TBD') ?> &middot; <?= $sTime ?> &ndash; <?= $fTime ?></span>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="mt-auto p-5 pt-3" onclick="event.stopPropagation()">
        <?php if ($data['is_card_owner']): ?>
            <?php
            $triggerClass = $isArchived ? 'reactivate-quote-trigger' : 'deactivate-quote-trigger';
            $btnStyles = $isArchived
                ? 'bg-green-50 dark:bg-green-900/10 text-green-600 dark:text-green-400 border-green-100 dark:border-green-900/30 hover:bg-green-600 hover:text-white'
                : 'bg-red-50 dark:bg-red-900/10 text-red-600 dark:text-red-400 border-red-100 dark:border-red-900/30 hover:bg-red-600 hover:text-white';
            ?>
            <button type="button" data-encoded-id="<?= $data['encoded_id'] ?>" class="<?= $triggerClass ?> w-full inline-flex justify-center items-center gap-2 px-4 py-2.5 <?= $btnStyles ?> border transition-all font-bold text-xs rounded-lg active:scale-95">
                <?= $isArchived ? 'Reactivate Quotation' : 'Deactivate Quotation' ?>
            </button>
        <?php else: ?>
            <button type="button" class="connect-quote-trigger w-full inline-flex justify-center items-center gap-2 px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs rounded-lg transition-colors shadow-sm active:scale-95"
                data-encoded-id="<?= $data['encoded_id'] ?>" data-owner-id="<?= (int) $data['owner_id'] ?>" data-title="<?= htmlspecialchars($title) ?>">
                Connect with Owner
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
            </button>
        <?php endif; ?>
    </div>
</div>

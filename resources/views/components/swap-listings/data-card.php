<?php
// /resources/views/components/swap-listings/data-card.php
//
// Shared listing card — included both directly by pages (swap.php,
// my-swap-listings.php, saved-swap-listings.php) and by
// SwapListingsController::renderCard() (ob_start()'d into a string for the
// API's create/update/status-toggle JSON responses, so a save/edit updates
// the grid in place without a full page reload). Every field this expects
// comes from $data (see SwapListingsController::cardData()) plus
// $assetBase in scope.
//
// @var array $data
// @var string $assetBase

declare(strict_types=1);
?>
<div class="swap-listing-card-wrapper bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 hover:border-purple-500/50 transition-all shadow-sm relative"
    data-listing-wrapper
    data-encoded-id="<?= htmlspecialchars($data['encoded_id']) ?>"
    data-title="<?= htmlspecialchars($data['title']) ?>"
    data-description="<?= htmlspecialchars($data['description'] ?? '') ?>"
    data-category-id="<?= htmlspecialchars((string) ($data['category_id'] ?? '')) ?>"
    data-listing-type="<?= htmlspecialchars($data['listing_type']) ?>"
    data-condition="<?= htmlspecialchars($data['condition']) ?>"
    data-price="<?= htmlspecialchars((string) ($data['price'] ?? '')) ?>"
    data-trade-pref="<?= htmlspecialchars($data['trade_pref'] ?? '') ?>"
    data-city="<?= htmlspecialchars($data['city'] ?? '') ?>"
    data-status="<?= htmlspecialchars($data['status']) ?>"
    data-photos='<?= htmlspecialchars(json_encode($data['photos']), ENT_QUOTES) ?>'
    data-is-card-owner="<?= $data['is_card_owner'] ? '1' : '0' ?>"
    data-is-saved="<?= $data['is_saved'] ? '1' : '0' ?>">

    <?php if ($data['viewer_id']): ?>
        <div class="absolute top-4 right-4 flex items-center gap-1.5 z-10">
            <?php if ($data['is_card_owner']): ?>
                <!-- Owner-only Edit/Delete -->
                <button type="button" class="edit-swap-listing-btn p-1.5 rounded-lg bg-white/90 dark:bg-gray-800/90 text-gray-500 hover:text-purple-600 shadow-sm transition-colors" data-encoded-id="<?= htmlspecialchars($data['encoded_id']) ?>" title="Edit">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                </button>
                <button type="button" class="delete-swap-listing-btn p-1.5 rounded-lg bg-white/90 dark:bg-gray-800/90 text-gray-500 hover:text-red-600 shadow-sm transition-colors" data-encoded-id="<?= htmlspecialchars($data['encoded_id']) ?>" title="Delete">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
            <?php endif; ?>

            <!-- Save/bookmark toggle — every listing, owned or not, for any
                 signed-in viewer (owners can bookmark their own listing too). -->
            <button type="button" class="save-swap-listing-btn p-1.5 rounded-lg bg-white/90 dark:bg-gray-800/90 shadow-sm transition-colors <?= $data['is_saved'] ? 'text-amber-500' : 'text-gray-400 hover:text-amber-500' ?>" data-encoded-id="<?= htmlspecialchars($data['encoded_id']) ?>" title="<?= $data['is_saved'] ? 'Remove from Saved' : 'Save this listing' ?>">
                <svg class="h-4 w-4" fill="<?= $data['is_saved'] ? 'currentColor' : 'none' ?>" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" /></svg>
            </button>
        </div>
    <?php endif; ?>

    <div class="flex items-start justify-between gap-4 mb-3 pr-8">
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-400">
                    <?= htmlspecialchars($data['type_label']) ?>
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                    <?= htmlspecialchars($data['condition_label']) ?>
                </span>
                <?php if ($data['status'] === 'completed'): ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-500 dark:bg-gray-800 dark:text-gray-500">
                        Completed
                    </span>
                <?php endif; ?>
                <?php if ($data['category_name']): ?>
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider"><?= htmlspecialchars($data['category_name']) ?></span>
                <?php endif; ?>
            </div>
            <h4 class="text-base font-bold text-gray-900 dark:text-white mt-2">
                <?= htmlspecialchars($data['title']) ?>
            </h4>
        </div>
    </div>

    <?php if ($data['thumbnail']): ?>
        <img src="<?= htmlspecialchars($data['thumbnail']) ?>" alt="<?= htmlspecialchars($data['title']) ?>" class="w-full h-44 object-cover rounded-lg mb-3" />
    <?php endif; ?>

    <?php if ($data['description']): ?>
        <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 mb-3"><?= htmlspecialchars($data['description']) ?></p>
    <?php endif; ?>

    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm border-t border-gray-100 dark:border-gray-800/80 pt-3 mb-3">
        <div>
            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Location</span>
            <span class="font-medium text-gray-700 dark:text-gray-300"><?= htmlspecialchars($data['city'] ?? 'Remote / TBD') ?></span>
        </div>
        <?php if ($data['listing_type'] === 'sale' && $data['price'] !== null): ?>
            <div>
                <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Price</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">&#8358;<?= number_format((float) $data['price']) ?></span>
            </div>
        <?php elseif ($data['listing_type'] === 'swap' && $data['trade_pref']): ?>
            <div class="col-span-2 sm:col-span-2">
                <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Looking For</span>
                <span class="font-medium text-gray-700 dark:text-gray-300"><?= htmlspecialchars($data['trade_pref']) ?></span>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($data['is_card_owner']): ?>
        <div class="pt-2 border-t border-gray-100 dark:border-gray-800/80">
            <?php if ($data['status'] === 'completed'): ?>
                <button type="button" class="reactivate-swap-listing-trigger w-full inline-flex items-center justify-center px-4 py-2 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:hover:bg-emerald-950/70 text-emerald-700 dark:text-emerald-400 font-bold text-xs rounded-lg transition-colors" data-encoded-id="<?= htmlspecialchars($data['encoded_id']) ?>">
                    Reactivate Listing
                </button>
            <?php else: ?>
                <button type="button" class="complete-swap-listing-trigger w-full inline-flex items-center justify-center px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-300 font-bold text-xs rounded-lg transition-colors" data-encoded-id="<?= htmlspecialchars($data['encoded_id']) ?>">
                    Mark As Completed
                </button>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

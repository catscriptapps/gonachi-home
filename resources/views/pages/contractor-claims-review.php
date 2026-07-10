<?php
// /resources/views/pages/contractor-claims-review.php

declare(strict_types=1);

/**
 * Admin-only moderation queue for submitted contractor claims (status =
 * pending). Approving here sets the contractor's claim_status = claimed
 * (drives the "Verified" badge). Mirrors landlord-report-review.php's
 * structure.
 *
 * @var bool $isLoggedIn
 * @var string $baseUrl
 * @var string $assetBase
 */

use Src\Controller\ContractorClaimController;
use Src\Service\AuthService;

// Defense in depth: the real gate is index.php's admin-only route check
// (which runs before the layout starts emitting HTML). A header() redirect
// here can't work — by the time this file is included, the layout has
// already echoed the sidebar/header markup.
if (!AuthService::isAdmin()) {
?>
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-8 text-center">
        <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300">Access Denied</h4>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">This area is restricted to administrators.</p>
    </div>
<?php
    return;
}

$pendingClaims = ContractorClaimController::pending(15);
?>

<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Contractor Claim Review Queue</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Approve claims to mark a profile Verified, or reject noise and leave it unclaimed.
            <?= $pendingClaims->total() ?> pending.
        </p>
    </div>

    <?php if ($pendingClaims->isEmpty()): ?>
        <div class="bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-800 rounded-xl p-8 text-center">
            <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300">Queue Is Empty</h4>
            <p class="text-xs text-gray-400 dark:text-gray-500 max-w-sm mx-auto mt-1">
                Nothing awaiting review right now. New claims land here as status = pending.
            </p>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($pendingClaims as $claim): ?>
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4 mb-3">
                        <div>
                            <h4 class="text-base font-bold text-gray-900 dark:text-white">
                                <?= htmlspecialchars($claim->contractor->business_name ?? 'Unknown Contractor') ?>
                            </h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($claim->contractor->location ?? '') ?></p>
                        </div>
                        <span class="text-xs font-medium text-gray-400 dark:text-gray-500 whitespace-nowrap"><?= htmlspecialchars($claim->created_at->diffForHumans()) ?></span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 my-4 text-sm border-t border-b border-gray-100 dark:border-gray-800/80 py-3">
                        <div>
                            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Claimant</span>
                            <span class="font-medium text-gray-700 dark:text-gray-300"><?= htmlspecialchars($claim->user->full_name ?? 'User #' . $claim->user_id) ?></span>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Contact Phone</span>
                            <span class="font-medium text-gray-700 dark:text-gray-300"><?= htmlspecialchars($claim->contact_phone) ?></span>
                        </div>
                    </div>

                    <?php if ($claim->message): ?>
                        <div class="bg-gray-50 dark:bg-gray-950 border border-gray-100 dark:border-gray-800 rounded-lg p-3 mb-4">
                            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Message</span>
                            <p class="text-sm text-gray-700 dark:text-gray-300 break-words"><?= htmlspecialchars($claim->message) ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="flex items-center justify-end gap-3">
                        <form method="POST" action="<?= $baseUrl ?>api/contractor-claim-review">
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="id" value="<?= $claim->id ?>">
                            <input type="hidden" name="page" value="<?= $pendingClaims->currentPage() ?>">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-xs rounded-lg transition-colors">
                                Reject
                            </button>
                        </form>
                        <form method="POST" action="<?= $baseUrl ?>api/contractor-claim-review">
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="id" value="<?= $claim->id ?>">
                            <input type="hidden" name="page" value="<?= $pendingClaims->currentPage() ?>">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-900 hover:bg-gray-800 dark:bg-secondary-600 dark:hover:bg-secondary-500 text-white font-bold text-xs rounded-lg transition-colors shadow-sm">
                                Approve & Verify
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($pendingClaims->lastPage() > 1): ?>
            <div class="flex items-center justify-between pt-2">
                <?php if ($pendingClaims->previousPageUrl()): ?>
                    <a href="<?= htmlspecialchars($pendingClaims->previousPageUrl()) ?>" data-partial class="text-sm font-medium text-secondary-600 hover:underline">&larr; Previous</a>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>

                <span class="text-xs text-gray-400">Page <?= $pendingClaims->currentPage() ?> of <?= $pendingClaims->lastPage() ?></span>

                <?php if ($pendingClaims->nextPageUrl()): ?>
                    <a href="<?= htmlspecialchars($pendingClaims->nextPageUrl()) ?>" data-partial class="text-sm font-medium text-secondary-600 hover:underline">Next &rarr;</a>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

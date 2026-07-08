<?php
// /resources/views/pages/lead-review.php

declare(strict_types=1);

/**
 * Admin-only moderation queue for extracted leads (status = pending_review).
 * Approving here is what makes a lead publicly visible on the homepage feed.
 *
 * @var bool $isLoggedIn
 * @var string $baseUrl
 */

use Src\Controller\LeadReviewController;
use Src\Controller\LeadsController;
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

$pendingLeads = LeadReviewController::pending(15);
?>

<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Lead Review Queue</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Approve extracted requests to publish them on the public feed, or reject noise before it goes live.
            <?= $pendingLeads->total() ?> pending.
        </p>
    </div>

    <?php if ($pendingLeads->isEmpty()): ?>
        <div class="bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-800 rounded-xl p-8 text-center">
            <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300">Queue Is Empty</h4>
            <p class="text-xs text-gray-400 dark:text-gray-500 max-w-sm mx-auto mt-1">
                Nothing awaiting review right now. New extractions land here as status = pending_review.
            </p>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($pendingLeads as $lead): ?>
                <?php
                $badge = LeadsController::requestTypeBadge($lead);
                $intent = LeadsController::intentBadge($lead);
                ?>
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4 mb-3">
                        <div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $badge['classes'] ?>">
                                <?= htmlspecialchars($badge['label']) ?>
                            </span>
                            <h4 class="text-base font-bold text-gray-900 dark:text-white mt-2">
                                <?= htmlspecialchars(LeadsController::headline($lead)) ?>
                            </h4>
                        </div>
                        <span class="text-xs font-medium text-gray-400 dark:text-gray-500 whitespace-nowrap"><?= htmlspecialchars($lead->scraped_at->diffForHumans()) ?></span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 my-4 text-sm border-t border-b border-gray-100 dark:border-gray-800/80 py-3">
                        <div>
                            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Location</span>
                            <span class="font-medium text-gray-700 dark:text-gray-300"><?= htmlspecialchars(LeadsController::locationLabel($lead)) ?></span>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Intent Score</span>
                            <span class="font-medium <?= $intent['classes'] ?>"><?= htmlspecialchars($intent['label']) ?></span>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Category</span>
                            <span class="font-medium text-gray-500 dark:text-gray-400"><?= htmlspecialchars($lead->category->name ?? 'Uncategorized') ?></span>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Source</span>
                            <span class="font-medium text-gray-500 dark:text-gray-400"><?= htmlspecialchars($lead->source->name ?? 'Unknown') ?></span>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-950 border border-gray-100 dark:border-gray-800 rounded-lg p-3 mb-4">
                        <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Raw Scraped Text</span>
                        <p class="text-sm text-gray-700 dark:text-gray-300 break-words"><?= htmlspecialchars($lead->raw_text) ?></p>
                        <?php if ($lead->source_url): ?>
                            <a href="<?= htmlspecialchars($lead->source_url) ?>" target="_blank" rel="noopener noreferrer" class="text-xs text-primary-600 hover:underline mt-2 inline-block break-all">
                                <?= htmlspecialchars($lead->source_url) ?>
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <form method="POST" action="<?= $baseUrl ?>api/lead-review">
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="id" value="<?= $lead->id ?>">
                            <input type="hidden" name="page" value="<?= $pendingLeads->currentPage() ?>">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-xs rounded-lg transition-colors">
                                Reject
                            </button>
                        </form>
                        <form method="POST" action="<?= $baseUrl ?>api/lead-review">
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="id" value="<?= $lead->id ?>">
                            <input type="hidden" name="page" value="<?= $pendingLeads->currentPage() ?>">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-900 hover:bg-gray-800 dark:bg-primary-600 dark:hover:bg-primary-500 text-white font-bold text-xs rounded-lg transition-colors shadow-sm">
                                Approve &amp; Publish
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($pendingLeads->lastPage() > 1): ?>
            <div class="flex items-center justify-between pt-2">
                <?php if ($pendingLeads->previousPageUrl()): ?>
                    <a href="<?= htmlspecialchars($pendingLeads->previousPageUrl()) ?>" data-partial class="text-sm font-medium text-primary-600 hover:underline">&larr; Previous</a>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>

                <span class="text-xs text-gray-400">Page <?= $pendingLeads->currentPage() ?> of <?= $pendingLeads->lastPage() ?></span>

                <?php if ($pendingLeads->nextPageUrl()): ?>
                    <a href="<?= htmlspecialchars($pendingLeads->nextPageUrl()) ?>" data-partial class="text-sm font-medium text-primary-600 hover:underline">Next &rarr;</a>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

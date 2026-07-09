<?php
// /resources/views/pages/landlord-report-review.php

declare(strict_types=1);

/**
 * Admin-only moderation queue for submitted landlord reports (status =
 * pending_review). Approving here is what makes a report count toward its
 * property's public confidence score and search results. Mirrors
 * lead-review.php's structure for the real-estate-leads project.
 *
 * @var bool $isLoggedIn
 * @var string $baseUrl
 * @var string $assetBase
 */

use Src\Controller\LandlordReportReviewController;
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

$pendingReports = LandlordReportReviewController::pending(15);

$issueLabels = [
    'deposit' => 'Withheld Deposit',
    'harassment' => 'Harassment',
    'unsafe' => 'Unsafe / Uninhabitable Conditions',
    'eviction' => 'Illegal Eviction',
    'other' => 'Other',
];
?>

<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Landlord Report Review Queue</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Approve submitted reports to count them toward a property's public confidence score, or reject noise before it goes live.
            <?= $pendingReports->total() ?> pending.
        </p>
    </div>

    <?php if ($pendingReports->isEmpty()): ?>
        <div class="bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-800 rounded-xl p-8 text-center">
            <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300">Queue Is Empty</h4>
            <p class="text-xs text-gray-400 dark:text-gray-500 max-w-sm mx-auto mt-1">
                Nothing awaiting review right now. New reports land here as status = pending_review.
            </p>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($pendingReports as $report): ?>
                <?php $issue = $issueLabels[$report->issue_type] ?? ucfirst($report->issue_type); ?>
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4 mb-3">
                        <div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-400">
                                <?= htmlspecialchars($issue) ?>
                            </span>
                            <h4 class="text-base font-bold text-gray-900 dark:text-white mt-2">
                                <?= htmlspecialchars($report->landlord->name ?? 'Unknown Landlord') ?>
                            </h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400"><?= htmlspecialchars($report->property->address ?? 'No address on file') ?></p>
                        </div>
                        <span class="text-xs font-medium text-gray-400 dark:text-gray-500 whitespace-nowrap"><?= htmlspecialchars($report->created_at->diffForHumans()) ?></span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 my-4 text-sm border-t border-b border-gray-100 dark:border-gray-800/80 py-3">
                        <div>
                            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Reported By</span>
                            <span class="font-medium text-gray-700 dark:text-gray-300"><?= htmlspecialchars($report->user->full_name ?? 'User #' . $report->user_id) ?></span>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Property Type</span>
                            <span class="font-medium text-gray-700 dark:text-gray-300"><?= htmlspecialchars($report->property->property_type ?? '—') ?></span>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Duration Of Tenancy</span>
                            <span class="font-medium text-gray-700 dark:text-gray-300"><?= htmlspecialchars($report->duration_of_tenancy ?? '—') ?></span>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Photos</span>
                            <span class="font-medium text-gray-500 dark:text-gray-400"><?= $report->photos->count() ?> attached</span>
                        </div>
                    </div>

                    <?php if ($report->notes): ?>
                        <div class="bg-gray-50 dark:bg-gray-950 border border-gray-100 dark:border-gray-800 rounded-lg p-3 mb-4">
                            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Additional Notes</span>
                            <p class="text-sm text-gray-700 dark:text-gray-300 break-words"><?= htmlspecialchars($report->notes) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($report->photos->isNotEmpty()): ?>
                        <div class="flex flex-wrap gap-2 mb-4">
                            <?php foreach ($report->photos as $photo): ?>
                                <a href="<?= $assetBase . htmlspecialchars($photo->file_path) ?>" target="_blank" rel="noopener noreferrer" class="block h-16 w-16 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-800">
                                    <img src="<?= $assetBase . htmlspecialchars($photo->file_path) ?>" alt="<?= htmlspecialchars($photo->kind) ?>" class="h-full w-full object-cover" />
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="flex items-center justify-end gap-3">
                        <form method="POST" action="<?= $baseUrl ?>api/landlord-report-review">
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="id" value="<?= $report->id ?>">
                            <input type="hidden" name="page" value="<?= $pendingReports->currentPage() ?>">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-xs rounded-lg transition-colors">
                                Reject
                            </button>
                        </form>
                        <form method="POST" action="<?= $baseUrl ?>api/landlord-report-review">
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="id" value="<?= $report->id ?>">
                            <input type="hidden" name="page" value="<?= $pendingReports->currentPage() ?>">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-900 hover:bg-gray-800 dark:bg-indigo-600 dark:hover:bg-indigo-500 text-white font-bold text-xs rounded-lg transition-colors shadow-sm">
                                Approve &amp; Publish
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($pendingReports->lastPage() > 1): ?>
            <div class="flex items-center justify-between pt-2">
                <?php if ($pendingReports->previousPageUrl()): ?>
                    <a href="<?= htmlspecialchars($pendingReports->previousPageUrl()) ?>" data-partial class="text-sm font-medium text-indigo-600 hover:underline">&larr; Previous</a>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>

                <span class="text-xs text-gray-400">Page <?= $pendingReports->currentPage() ?> of <?= $pendingReports->lastPage() ?></span>

                <?php if ($pendingReports->nextPageUrl()): ?>
                    <a href="<?= htmlspecialchars($pendingReports->nextPageUrl()) ?>" data-partial class="text-sm font-medium text-indigo-600 hover:underline">Next &rarr;</a>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

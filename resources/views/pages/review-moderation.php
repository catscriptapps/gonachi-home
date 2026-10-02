<?php
// /resources/views/pages/review-moderation.php

declare(strict_types=1);

/**
 * Gonachi Landlord & Tenant Validation Engine - Review Disputes Queue
 *
 * Admin-only (gated pre-layout in public/index.php via
 * NavigationConfig::getAdminOnlyPaths() — the check here is defense in
 * depth, same as every other moderation queue in this app). Replaces
 * landlord-report-review.php + tenant-report-review.php: reviews now
 * publish automatically via the double-blind release mechanic
 * (Src\Service\ReviewService), so the only thing left to moderate is a
 * REPORTED review (landlord_and_tenant_validation.pdf §9) — a dispute
 * never auto-hides, it sits here until an admin upholds or dismisses it.
 * Uses the same shared AJAX handler as every other moderation queue
 * (resources/js/utils/review-queue.js, already globally wired in app.js).
 *
 * @var string $baseUrl
 */

use Src\Controller\ReviewModerationController;
use Src\Service\AuthService;
use Src\Utils\ReviewReportReasons;

if (!AuthService::isAdmin()) {
?>
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-8 text-center">
        <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300">Access Denied</h4>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">This area is restricted to administrators.</p>
    </div>
<?php
    return;
}

$page = max(1, (int) ($_GET['page'] ?? 1));
$reports = ReviewModerationController::pending(15);
?>
<div class="space-y-6">

    <?php
    $breadcrumbs = [
        ['label' => 'Landlord & Tenant Validation', 'href' => $baseUrl . 'landlord-tenant-validation'],
        ['label' => 'Review Disputes Queue'],
    ];
    $breadcrumbAccent = 'indigo';
    include __DIR__ . '/../components/breadcrumbs.php';
    ?>

    <div>
        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Review Disputes Queue</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Reported reviews stay visible until resolved — upholding a report removes the review from public view.</p>
    </div>

    <?php if ($reports->isEmpty()): ?>
        <div class="bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-800 rounded-xl p-10 text-center">
            <svg class="h-8 w-8 text-gray-300 dark:text-gray-700 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <p class="text-sm text-gray-400 dark:text-gray-500">No pending disputes. All caught up.</p>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($reports as $report): ?>
                <?php $review = $report->review; ?>
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 space-y-4">
                    <div class="flex items-start justify-between gap-4 flex-wrap">
                        <div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-950/40 dark:text-red-400">
                                <?= htmlspecialchars(ReviewReportReasons::labelFor($report->reason) ?? $report->reason) ?>
                            </span>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1.5">
                                Reported by <?= htmlspecialchars($report->reporter->full_name ?? 'Unknown user') ?> on <?= $report->created_at->format('M j, Y') ?>
                                — review for a <?= htmlspecialchars($review->reviewee_type ?? 'unknown') ?> submitted by <?= htmlspecialchars($review->reviewer->full_name ?? 'Unknown user') ?> on <?= $review->created_at?->format('M j, Y') ?>
                            </p>
                        </div>
                    </div>

                    <?php if ($report->details): ?>
                        <p class="text-xs text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-950 rounded-lg p-3"><?= htmlspecialchars($report->details) ?></p>
                    <?php endif; ?>

                    <?php if ($review->comment): ?>
                        <div class="border-l-2 border-gray-200 dark:border-gray-800 pl-3">
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Reported review comment</p>
                            <p class="text-sm text-gray-700 dark:text-gray-300"><?= nl2br(htmlspecialchars($review->comment)) ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-4 gap-y-2 text-xs border-t border-gray-100 dark:border-gray-800 pt-3">
                        <?php foreach ($review->criterionScores as $score): ?>
                            <div>
                                <span class="block text-gray-400"><?= htmlspecialchars($score->criterion->label ?? '') ?></span>
                                <span class="font-semibold text-gray-700 dark:text-gray-300"><?= $score->is_na ? 'N/A' : str_repeat('★', $score->stars) . str_repeat('☆', 5 - $score->stars) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <form data-review-form action="<?= $baseUrl ?>api/review-moderation" class="inline">
                            <input type="hidden" name="id" value="<?= $report->id ?>" />
                            <input type="hidden" name="action" value="uphold" />
                            <button type="submit" data-confirm-message="Uphold this report? The review will be removed from public view." data-confirm-action-label="Uphold Report" data-confirm-color="bg-red-600 hover:bg-red-700" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-lg transition-colors shadow-sm">
                                Uphold (Remove Review)
                            </button>
                        </form>
                        <form data-review-form action="<?= $baseUrl ?>api/review-moderation" class="inline">
                            <input type="hidden" name="id" value="<?= $report->id ?>" />
                            <input type="hidden" name="action" value="dismiss" />
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-xs rounded-lg transition-colors">
                                Dismiss Report
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($reports->lastPage() > 1): ?>
            <div class="flex items-center justify-between pt-2">
                <?php if ($reports->previousPageUrl()): ?>
                    <a href="<?= htmlspecialchars($reports->previousPageUrl()) ?>" data-partial class="text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-indigo-600">&larr; Previous</a>
                <?php else: ?><span></span><?php endif; ?>
                <span class="text-xs text-gray-400">Page <?= $reports->currentPage() ?> of <?= $reports->lastPage() ?></span>
                <?php if ($reports->nextPageUrl()): ?>
                    <a href="<?= htmlspecialchars($reports->nextPageUrl()) ?>" data-partial class="text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-indigo-600">Next &rarr;</a>
                <?php else: ?><span></span><?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

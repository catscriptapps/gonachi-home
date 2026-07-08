<?php
// /resources/views/pages/lead-category-location.php
//
// SEO landing page for a category+location combination (e.g.
// /home-buyers-lagos). Resolved dynamically by resolvePageRoute() via
// LeadCategoryController::matchSlug() — there's no literal file per
// combination, so this single view serves every one of them.

declare(strict_types=1);

/** @var bool $isLoggedIn */

use Src\Controller\LeadCategoryController;
use Src\Controller\LeadsController;

/** @var array{category: \App\Models\LeadCategory, location: \App\Models\Location} $match */
$match = $GLOBALS['leadCategoryMatch'];
$category = $match['category'];
$location = $match['location'];

$leads = LeadCategoryController::leadsFor($category, $location);
?>
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                <?= htmlspecialchars($category->name) ?> in <?= htmlspecialchars($location->name) ?>
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Browse active <?= htmlspecialchars($category->name) ?> requests in <?= htmlspecialchars($location->name) ?> and nearby areas.
            </p>
        </div>

        <div class="bg-white dark:bg-gray-900 px-5 py-3 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm text-center flex-shrink-0">
            <span class="block text-2xl font-bold text-primary-600"><?= $leads->total() ?></span>
            <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Active Requests</span>
        </div>
    </div>

    <?php if ($leads->isEmpty()): ?>
        <div class="bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-800 rounded-xl p-8 text-center">
            <svg class="h-8 w-8 text-gray-300 dark:text-gray-700 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300">No Active Requests Yet</h4>
            <p class="text-xs text-gray-400 dark:text-gray-500 max-w-sm mx-auto mt-1">
                New <?= htmlspecialchars($category->name) ?> requests in <?= htmlspecialchars($location->name) ?> appear here as soon as they're reviewed and marked active.
            </p>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($leads as $lead): ?>
                <?php
                $badge = LeadsController::requestTypeBadge($lead);
                $intent = LeadsController::intentBadge($lead);
                $postedAt = $lead->posted_at ?? $lead->scraped_at;
                ?>
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 hover:border-primary-500/50 transition-all shadow-sm group">
                    <div class="flex items-start justify-between gap-4 mb-3">
                        <div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $badge['classes'] ?>">
                                <?= htmlspecialchars($badge['label']) ?>
                            </span>
                            <h4 class="text-base font-bold text-gray-900 dark:text-white mt-2 group-hover:text-primary-600 transition-colors">
                                <?= htmlspecialchars(LeadsController::headline($lead)) ?>
                            </h4>
                        </div>
                        <span class="text-xs font-medium text-gray-400 dark:text-gray-500 whitespace-nowrap"><?= htmlspecialchars($postedAt->diffForHumans()) ?></span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 my-4 text-sm border-t border-b border-gray-100 dark:border-gray-800/80 py-3">
                        <div>
                            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Target Location</span>
                            <span class="font-medium text-gray-700 dark:text-gray-300"><?= htmlspecialchars(LeadsController::locationLabel($lead)) ?></span>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Intent Score</span>
                            <span class="font-medium <?= $intent['classes'] ?>"><?= htmlspecialchars($intent['label']) ?></span>
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Origin Source</span>
                            <span class="font-medium text-gray-500 dark:text-gray-400"><?= htmlspecialchars($lead->source->name ?? 'Public Request Board') ?></span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <span class="text-xs text-gray-400">
                            <?= $lead->contact_info_raw ? 'Public contact details available.' : 'Contact details unlock with a full record view.' ?>
                        </span>
                        <?php if ($isLoggedIn): ?>
                            <a href="<?= $baseUrl ?>leads/<?= $lead->id ?>" data-partial class="inline-flex items-center px-4 py-2 bg-gray-900 hover:bg-gray-800 dark:bg-primary-600 dark:hover:bg-primary-500 text-white font-bold text-xs rounded-lg transition-colors shadow-sm tracking-wide">
                                View Full Details
                            </a>
                        <?php else: ?>
                            <a href="<?= $baseUrl ?>login" data-login-button class="inline-flex items-center px-4 py-2 bg-gray-900 hover:bg-gray-800 dark:bg-primary-600 dark:hover:bg-primary-500 text-white font-bold text-xs rounded-lg transition-colors shadow-sm tracking-wide">
                                View Full Details
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($leads->lastPage() > 1): ?>
            <div class="flex items-center justify-between pt-2">
                <?php if ($leads->previousPageUrl()): ?>
                    <a href="<?= htmlspecialchars($leads->previousPageUrl()) ?>" data-partial class="text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">&larr; Previous</a>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>

                <span class="text-xs text-gray-400">Page <?= $leads->currentPage() ?> of <?= $leads->lastPage() ?></span>

                <?php if ($leads->nextPageUrl()): ?>
                    <a href="<?= htmlspecialchars($leads->nextPageUrl()) ?>" data-partial class="text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">Next &rarr;</a>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

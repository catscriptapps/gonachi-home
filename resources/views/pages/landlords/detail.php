<?php
// /resources/views/pages/landlords/detail.php
//
// Full landlord profile (landlord_and_tenant_validation.pdf §13) — weighted
// score + per-category breakdown, frequently-mentioned tags, Trust Profile
// badges, and recent verified reviews with report/respond actions.
// Resolved dynamically via resolvePageRoute()'s /{resource}/{id} handling
// (same mechanism as leads/detail.php, contractor/detail.php).

declare(strict_types=1);

/**
 * @var bool $isLoggedIn
 * @var string $baseUrl
 */

use Src\Controller\ReviewProfileController;
use Src\Service\AuthService;

$landlordId = (int) ($GLOBALS['encodedId'] ?? 0);
$profile = $landlordId > 0 ? ReviewProfileController::landlordProfile($landlordId) : null;

if (!$profile):
?>
    <div class="max-w-lg mx-auto text-center py-20">
        <svg class="h-10 w-10 text-gray-300 dark:text-gray-700 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Landlord Not Found</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">This profile may have been removed or doesn't exist.</p>
        <a href="<?= $baseUrl ?>landlord-tenant-validation" data-partial class="inline-flex items-center mt-6 text-sm font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">&larr; Back to Landlord & Tenant Validation</a>
    </div>
<?php
return;
endif;

$currentUserId = $isLoggedIn ? AuthService::userId() : null;
$landlord = $profile['record'];
$aggregate = $profile['aggregate'];
$isOwnProfile = $currentUserId && $landlord->user_id === $currentUserId;
?>
<div class="max-w-4xl mx-auto space-y-6">

    <?php
    $breadcrumbs = [
        ['label' => 'Landlord & Tenant Validation', 'href' => $baseUrl . 'landlord-tenant-validation'],
        ['label' => $landlord->name],
    ];
    $breadcrumbAccent = 'indigo';
    include __DIR__ . '/../../components/breadcrumbs.php';
    ?>

    <!-- Header -->
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 sm:p-8 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <span class="inline-block text-xs font-semibold tracking-[0.2em] text-indigo-600 dark:text-indigo-400 uppercase mb-2">Landlord</span>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900 dark:text-white"><?= htmlspecialchars(mb_strtoupper($landlord->name)) ?></h1>

                <?php if ($aggregate['tier'] === 'none'): ?>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-2">No reviews yet.</p>
                <?php else: ?>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="text-2xl text-amber-400"><?= ReviewProfileController::starHtml($aggregate['overall']) ?></span>
                        <span class="text-lg font-bold text-gray-900 dark:text-white"><?= number_format((float) $aggregate['overall'], 1) ?> / 5</span>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        <?= $aggregate['reviewCount'] ?> Review<?= $aggregate['reviewCount'] === 1 ? '' : 's' ?>
                        &middot; <?= $profile['verifiedTenancyCount'] ?> Verified Tenanc<?= $profile['verifiedTenancyCount'] === 1 ? 'y' : 'ies' ?>
                        <?php if ($aggregate['tier'] === 'limited'): ?>
                            <span class="ml-1 text-xs font-semibold text-amber-600 dark:text-amber-400">&middot; Limited rating history</span>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
            </div>

            <?php if (!$isOwnProfile): ?>
                <a href="<?= $baseUrl ?>review-landlord" data-partial class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-lg transition-colors shadow-sm whitespace-nowrap">
                    Review This Landlord
                </a>
            <?php endif; ?>
        </div>

        <?php if ($aggregate['tier'] === 'established'): ?>
            <!-- Per-category breakdown -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-x-6 gap-y-4 mt-6 pt-6 border-t border-gray-100 dark:border-gray-800">
                <?php foreach (\App\Models\ReviewCriterion::forSubject('landlord') as $criterion): ?>
                    <?php $score = $aggregate['byCriterion'][$criterion->key] ?? null; ?>
                    <div>
                        <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider"><?= htmlspecialchars($criterion->label) ?></span>
                        <span class="text-sm font-bold text-gray-800 dark:text-gray-200"><?= $score !== null ? number_format($score, 1) : '—' ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($profile['frequentTags'])): ?>
            <div class="flex flex-wrap gap-2 mt-6">
                <?php foreach ($profile['frequentTags'] as $tag): ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-400 text-xs font-medium"><?= htmlspecialchars($tag['label']) ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Trust Profile -->
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm">
        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Gonachi Trust Profile</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <?php
            $emailVerified = $landlord->user?->email_verified ?? false;
            $trustItems = [
                ['label' => 'Email Verified', 'active' => $emailVerified],
                ['label' => 'Tenancy Verified', 'active' => $profile['verifiedTenancyCount'] > 0],
                ['label' => 'Identity Verified', 'active' => false, 'comingSoon' => true],
                ['label' => 'Phone Verified', 'active' => false, 'comingSoon' => true],
            ];
            ?>
            <?php foreach ($trustItems as $item): ?>
                <div class="flex items-center gap-2 px-3 py-2 rounded-lg <?= $item['active'] ? 'bg-emerald-50 dark:bg-emerald-950/30' : 'bg-gray-50 dark:bg-gray-950' ?>">
                    <?php if ($item['active']): ?>
                        <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400"><?= htmlspecialchars($item['label']) ?></span>
                    <?php else: ?>
                        <svg class="h-4 w-4 text-gray-300 dark:text-gray-700 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span class="text-xs font-medium text-gray-400 dark:text-gray-600"><?= htmlspecialchars($item['label']) ?><?= !empty($item['comingSoon']) ? ' (Coming soon)' : '' ?></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Recent Verified Reviews -->
    <div class="space-y-4">
        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Recent Reviews</h3>

        <?php if ($profile['recentReviews']->isEmpty()): ?>
            <div class="bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-800 rounded-xl p-8 text-center">
                <p class="text-sm text-gray-400 dark:text-gray-500">No published reviews yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($profile['recentReviews'] as $review): ?>
                <?php
                $reviewScore = \Src\Service\ReviewAggregationService::reviewOverallScore($review->criterionScores);
                $tenancy = $review->tenancy;
                ?>
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 space-y-3" data-review-id="<?= $review->id ?>">
                    <div class="flex items-center justify-between gap-3 flex-wrap">
                        <div class="flex items-center gap-2">
                            <span class="text-amber-400"><?= ReviewProfileController::starHtml($reviewScore) ?></span>
                            <?php if ($tenancy && $tenancy->is_verified): ?>
                                <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                    <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    Verified Tenancy
                                </span>
                            <?php endif; ?>
                        </div>
                        <span class="text-xs text-gray-400"><?= $review->released_at?->format('M j, Y') ?></span>
                    </div>

                    <?php if ($review->comment): ?>
                        <p class="text-sm text-gray-700 dark:text-gray-300">"<?= nl2br(htmlspecialchars($review->comment)) ?>"</p>
                    <?php endif; ?>

                    <?php if ($review->tags->isNotEmpty()): ?>
                        <div class="flex flex-wrap gap-1.5">
                            <?php foreach ($review->tags as $tag): ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 text-xs"><?= htmlspecialchars(\Src\Utils\ReviewTagCatalog::labelFor('landlord', $tag->tag_key) ?? $tag->tag_key) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($review->response): ?>
                        <div class="border-l-2 border-indigo-200 dark:border-indigo-900 pl-3 ml-1">
                            <p class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">Landlord's Response</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5"><?= nl2br(htmlspecialchars($review->response->response)) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($currentUserId): ?>
                        <div class="flex items-center gap-3 pt-2 border-t border-gray-100 dark:border-gray-800">
                            <?php if ($isOwnProfile && !$review->response): ?>
                                <button type="button" data-respond-review="<?= $review->id ?>" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">Respond</button>
                            <?php endif; ?>
                            <button type="button" data-report-review="<?= $review->id ?>" class="text-xs font-semibold text-gray-400 hover:text-red-500">Report Review</button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../components/review-action-modals.php'; ?>

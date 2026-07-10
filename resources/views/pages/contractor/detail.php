<?php
// /resources/views/pages/contractor/detail.php
//
// Full contractor profile — the "View Profile" destination linked from the
// directory. Resolved dynamically via resolvePageRoute()'s /{resource}/{id}
// handling (same mechanism as leads/detail.php). Contact details are shown
// here (not on the directory card), matching the card's "Contact details
// unlock with a full profile view" copy — no credit-gating on this project.

declare(strict_types=1);

/**
 * @var bool $isLoggedIn
 * @var string $baseUrl
 */

use Src\Controller\ContractorController;
use Src\Service\AuthService;

$contractorId = (int) ($GLOBALS['encodedId'] ?? 0);
$contractor = $contractorId ? ContractorController::find($contractorId) : null;

if (!$contractor):
    http_response_code(404);
?>
    <div class="max-w-lg mx-auto text-center py-20">
        <svg class="h-10 w-10 text-gray-300 dark:text-gray-700 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Contractor Not Found</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">This profile may have been removed or is no longer active.</p>
        <a href="<?= $baseUrl ?>contractor-discovery" data-partial class="inline-flex items-center mt-6 text-sm font-semibold text-secondary-600 hover:text-secondary-700 dark:text-secondary-400">
            &larr; Back to Contractor Directory
        </a>
    </div>
<?php
return;
endif;

$currentUserId = $isLoggedIn ? AuthService::userId() : null;
$isClaimed = $contractor->claim_status === 'claimed';
$categoryLabels = ContractorController::CATEGORY_LABELS;
?>
<div class="max-w-3xl mx-auto space-y-6">
    <a href="<?= $baseUrl ?>contractor-discovery" data-partial class="inline-flex items-center text-sm font-semibold text-gray-500 hover:text-secondary-600 dark:text-gray-400 dark:hover:text-secondary-400">
        &larr; Back to Contractor Directory
    </a>

    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 sm:p-8 shadow-sm">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-secondary-100 text-secondary-800 dark:bg-secondary-950 dark:text-secondary-400">
                        <?= htmlspecialchars($categoryLabels[$contractor->service_category] ?? ucfirst($contractor->service_category)) ?>
                    </span>
                    <?php if ($isClaimed): ?>
                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Verified
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-gray-400">Unclaimed Profile</span>
                    <?php endif; ?>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-3"><?= htmlspecialchars($contractor->business_name) ?></h1>
            </div>
            <?php if ($contractor->rating !== null): ?>
                <span class="text-sm font-medium text-amber-500 whitespace-nowrap">&#9733; <?= number_format((float) $contractor->rating, 1) ?> (<?= $contractor->review_count ?> reviews)</span>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 my-4 text-sm border-t border-b border-gray-100 dark:border-gray-800/80 py-4">
            <div>
                <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Location</span>
                <span class="font-medium text-gray-700 dark:text-gray-300"><?= htmlspecialchars($contractor->location) ?></span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Operating Areas</span>
                <span class="font-medium text-gray-700 dark:text-gray-300"><?= $contractor->operating_areas ? htmlspecialchars($contractor->operating_areas) : '—' ?></span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Phone</span>
                <span class="font-medium text-gray-700 dark:text-gray-300"><?= $contractor->phone ? htmlspecialchars($contractor->phone) : 'Not publicly listed' ?></span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Website</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">
                    <?php if ($contractor->website): ?>
                        <a href="<?= htmlspecialchars($contractor->website) ?>" target="_blank" rel="noopener noreferrer" class="text-secondary-600 dark:text-secondary-400 hover:underline"><?= htmlspecialchars($contractor->website) ?></a>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </span>
            </div>
        </div>

        <?php if ($contractor->description): ?>
            <div class="mb-4">
                <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">About</span>
                <p class="text-sm text-gray-600 dark:text-gray-400"><?= nl2br(htmlspecialchars($contractor->description)) ?></p>
            </div>
        <?php endif; ?>

        <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-800/80 gap-3 flex-wrap">
            <span class="text-xs text-gray-400">Is this your business?</span>

            <?php if ($isClaimed): ?>
                <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">Claimed & Verified</span>
            <?php elseif ($contractor->claim_status === 'pending'): ?>
                <button disabled title="Awaiting admin review" class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-400 cursor-not-allowed font-bold text-xs rounded-lg transition-colors tracking-wide">
                    Claim Pending
                </button>
            <?php elseif (!$currentUserId): ?>
                <a href="<?= $baseUrl ?>login" data-login-button class="inline-flex items-center px-4 py-2 bg-secondary-600 hover:bg-secondary-700 text-white font-bold text-xs rounded-lg transition-colors shadow-sm tracking-wide">
                    Claim This Profile
                </a>
            <?php else: ?>
                <button type="button" data-claim-contractor="<?= $contractor->id ?>" class="inline-flex items-center px-4 py-2 bg-secondary-600 hover:bg-secondary-700 text-white font-bold text-xs rounded-lg transition-colors shadow-sm tracking-wide">
                    Claim This Profile
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div id="contractor-claim-message"></div>
</div>

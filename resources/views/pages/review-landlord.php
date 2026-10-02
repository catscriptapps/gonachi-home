<?php
// /resources/views/pages/review-landlord.php

declare(strict_types=1);

/**
 * Gonachi Landlord & Tenant Validation Engine - "Review A Landlord" Form
 *
 * The tenant-side half of landlord_and_tenant_validation.pdf's weighted
 * review system (§2-6): 8 weighted criteria (star 1-5 or explicit N/A),
 * an optional comment, and optional structured tags. Submission is pure
 * AJAX — see resources/js/pages/review-landlord-page.js — and goes through
 * the double-blind release flow (Src\Service\ReviewService): it does NOT
 * publish immediately, it publishes once the landlord's own review of this
 * tenancy lands too, or once the 30-day window closes.
 *
 * @var bool $isLoggedIn
 * @var string $baseUrl
 */

use App\Models\ReviewCriterion;
use Src\Service\AuthService;
use Src\Utils\ReviewTagCatalog;

$currentUserId = $isLoggedIn ? AuthService::userId() : null;
$countryCode = $_GET['country'] ?? ($GLOBALS['countryCode'] ?? 'ng');
$criteria = ReviewCriterion::forSubject('landlord');
$tags = ReviewTagCatalog::LANDLORD;
?>
<div class="max-w-3xl mx-auto space-y-6">

    <?php
    $breadcrumbs = [
        ['label' => 'Landlord & Tenant Validation', 'href' => $baseUrl . 'landlord-tenant-validation'],
        ['label' => 'Review A Landlord'],
    ];
    $breadcrumbAccent = 'indigo';
    include __DIR__ . '/../components/breadcrumbs.php';
    ?>

    <div>
        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Review A Landlord</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Rate your landlord across 8 specific areas of your tenancy. Your review publishes once your landlord submits their own review of you, or 30 days after submission — whichever comes first.</p>
    </div>

    <div id="review-landlord-message"></div>

    <?php if (!$currentUserId): ?>
        <div class="max-w-lg mx-auto text-center py-20">
            <svg class="h-10 w-10 text-gray-300 dark:text-gray-700 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Sign In To Leave A Review</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Reviews are tied to your account so the landlord can confirm the tenancy and respond.</p>
            <div class="mt-6 flex items-center justify-center gap-3">
                <a href="<?= $baseUrl ?>login" data-login-button class="inline-flex items-center px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-bold rounded-lg transition-all shadow-sm">Sign In</a>
                <button type="button" class="register-btn inline-flex items-center px-5 py-2 border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300 text-sm font-bold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/60 transition-all">Create Account</button>
            </div>
        </div>
    <?php else: ?>

        <form id="review-landlord-form" novalidate class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 sm:p-8 shadow-sm space-y-8">
            <input type="hidden" name="country_code" value="<?= htmlspecialchars($countryCode) ?>" />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="review-landlord-name" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Landlord Name</label>
                    <input type="text" id="review-landlord-name" name="other_party_name" required placeholder="e.g. Mr X" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-900 dark:text-white" />
                </div>
                <div>
                    <label for="review-landlord-address" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Property Address <span class="normal-case font-medium text-gray-400">(optional)</span></label>
                    <input type="text" id="review-landlord-address" name="address" placeholder="e.g. House 14, Lekki" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-900 dark:text-white" />
                </div>
            </div>

            <!-- Weighted criteria — PDF §2: 8 categories, each a 1-5 star picker + explicit N/A toggle -->
            <div class="space-y-5">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Rate your experience</h3>
                <?php foreach ($criteria as $criterion): ?>
                    <div class="border-b border-gray-100 dark:border-gray-800 pb-5 last:border-0 last:pb-0" data-criterion-row data-criterion-id="<?= $criterion->id ?>">
                        <div class="flex items-start justify-between gap-3 flex-wrap">
                            <div>
                                <p class="text-sm font-semibold text-gray-800 dark:text-gray-200"><?= htmlspecialchars($criterion->label) ?></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5"><?= htmlspecialchars($criterion->prompt) ?></p>
                            </div>
                            <div class="flex items-center gap-3 flex-shrink-0">
                                <div class="flex items-center gap-1 criterion-star-picker">
                                    <?php for ($s = 1; $s <= 5; $s++): ?>
                                        <button type="button" class="star-btn text-gray-300 dark:text-gray-600 hover:text-amber-400 transition-colors" data-star="<?= $s ?>">
                                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 00-.363 1.118l1.287 3.959c.3.92-.755 1.688-1.538 1.118l-3.368-2.448a1 1 0 00-1.175 0l-3.368 2.448c-.783.57-1.838-.197-1.538-1.118l1.287-3.959a1 1 0 00-.363-1.118L2.063 9.385c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.958z"></path></svg>
                                        </button>
                                    <?php endfor; ?>
                                </div>
                                <label class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400 cursor-pointer">
                                    <input type="checkbox" class="criterion-na-checkbox rounded border-gray-300 dark:border-gray-700 text-indigo-600 focus:ring-indigo-500" />
                                    N/A
                                </label>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <p id="review-landlord-criteria-error" class="hidden text-xs text-red-600 dark:text-red-400">Please rate every category above, or mark it N/A.</p>
            </div>

            <!-- Comment -->
            <div>
                <label for="review-landlord-comment" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Tell us about your experience <span class="normal-case font-medium text-gray-400">(optional)</span></label>
                <textarea id="review-landlord-comment" name="comment" rows="4" placeholder="Describe your first-hand experience. Focus on specific tenancy-related events and avoid personal information, insults, threats, discriminatory statements or allegations you cannot substantiate." class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none text-gray-900 dark:text-white resize-none"></textarea>
            </div>

            <!-- Structured tags -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Tags <span class="normal-case font-medium text-gray-400">(optional)</span></label>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($tags as $tagKey => $tagLabel): ?>
                        <label class="tag-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-gray-200 dark:border-gray-800 text-xs font-medium text-gray-600 dark:text-gray-300 cursor-pointer hover:border-indigo-400 transition-colors">
                            <input type="checkbox" name="tags[]" value="<?= htmlspecialchars($tagKey) ?>" class="sr-only" />
                            <?= htmlspecialchars($tagLabel) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2">
                <span class="text-xs text-gray-400">Reviews go through a 30-day double-blind period before publishing.</span>
                <button type="submit" id="review-landlord-submit" class="inline-flex items-center px-6 py-2.5 bg-gray-900 hover:bg-gray-800 dark:bg-indigo-600 dark:hover:bg-indigo-500 text-white font-bold text-sm rounded-lg transition-colors shadow-sm whitespace-nowrap">
                    Submit Review
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>

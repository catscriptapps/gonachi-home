<?php
// /resources/views/pages/leads/detail.php
//
// Full lead record — the credit-gated "View Full Details" destination
// linked from the discovery feed and the category/location SEO pages.
// Resolved dynamically via resolvePageRoute()'s /{resource}/{id} handling.

declare(strict_types=1);

/** @var bool $isLoggedIn */

use App\Models\Lead;
use Src\Controller\LeadsController;
use Src\Service\AuthService;
use Src\Service\CreditService;

$leadId = (int) ($GLOBALS['encodedId'] ?? 0);
$lead = $leadId ? Lead::with(['location.parent', 'category', 'source'])->find($leadId) : null;

if (!$lead || $lead->status !== 'active'):
    http_response_code(404);
?>
    <div class="max-w-lg mx-auto text-center py-20">
        <svg class="h-10 w-10 text-gray-300 dark:text-gray-700 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Lead Not Found</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">This lead may have been removed, expired, or is still pending review.</p>
        <a href="<?= $baseUrl ?>real-estate-leads" data-partial class="inline-flex items-center mt-6 text-sm font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400">
            &larr; Back to Active Property Requests
        </a>
    </div>
<?php
return;
endif;

$badge = LeadsController::requestTypeBadge($lead);
$intent = LeadsController::intentBadge($lead);
$postedAt = $lead->posted_at ?? $lead->scraped_at;
$budgetLabel = LeadsController::budgetLabel($lead);

// Credit gating only applies to backend Users (the realtor workspace this
// project is for) — a landlord/tenant session has no user_id and falls
// back to the same sign-in gate as a guest.
$currentUserId = $isLoggedIn ? AuthService::userId() : null;
$unlock = $currentUserId ? CreditService::unlockLead($currentUserId, $lead) : null;
?>
<div class="max-w-3xl mx-auto space-y-6">
    <a href="<?= $baseUrl ?>real-estate-leads" data-partial class="inline-flex items-center text-sm font-semibold text-gray-500 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-400">
        &larr; Back to Active Property Requests
    </a>

    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 sm:p-8 shadow-sm">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $badge['classes'] ?>">
                    <?= htmlspecialchars($badge['label']) ?>
                </span>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-3">
                    <?= htmlspecialchars(LeadsController::headline($lead)) ?>
                </h1>
            </div>
            <span class="text-xs font-medium text-gray-400 dark:text-gray-500 whitespace-nowrap"><?= htmlspecialchars($postedAt->diffForHumans()) ?></span>
        </div>

        <?php if (!$currentUserId): ?>
            <!-- Conversion Gate: matches the PDF's "View Full Details -> Create Account or Start Free Trial" flow -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 my-4 text-sm border-t border-gray-100 dark:border-gray-800/80 pt-4">
                <div>
                    <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Location</span>
                    <span class="font-medium text-gray-700 dark:text-gray-300"><?= htmlspecialchars(LeadsController::locationLabel($lead)) ?></span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Intent Score</span>
                    <span class="font-medium <?= $intent['classes'] ?>"><?= htmlspecialchars($intent['label']) ?></span>
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</span>
                    <span class="font-medium text-emerald-600 dark:text-emerald-400">Active</span>
                </div>
            </div>

            <div class="mt-6 bg-gray-50 dark:bg-gray-950 border border-dashed border-gray-300 dark:border-gray-800 rounded-xl p-6 text-center">
                <svg class="h-8 w-8 text-primary-600 mb-2 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <h5 class="text-sm font-bold text-gray-900 dark:text-white">Full Record Gated</h5>
                <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm mx-auto mt-1 mb-4">
                    Budget, exact contact details, and the original source post unlock with an account.
                </p>
                <div class="flex items-center justify-center space-x-3">
                    <a href="<?= $baseUrl ?>login" data-login-button class="px-5 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-bold rounded-lg transition-all shadow-sm">Start Free Trial</a>
                    <a href="<?= $baseUrl ?>login" data-login-button class="px-5 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-lg transition-all">Sign In</a>
                </div>
            </div>
        <?php elseif (!$unlock['success']): ?>
            <!-- Out of Credits Gate -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 my-4 text-sm border-t border-gray-100 dark:border-gray-800/80 pt-4">
                <div>
                    <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Location</span>
                    <span class="font-medium text-gray-700 dark:text-gray-300"><?= htmlspecialchars(LeadsController::locationLabel($lead)) ?></span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Intent Score</span>
                    <span class="font-medium <?= $intent['classes'] ?>"><?= htmlspecialchars($intent['label']) ?></span>
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</span>
                    <span class="font-medium text-emerald-600 dark:text-emerald-400">Active</span>
                </div>
            </div>

            <div class="mt-6 bg-gray-50 dark:bg-gray-950 border border-dashed border-gray-300 dark:border-gray-800 rounded-xl p-6 text-center">
                <svg class="h-8 w-8 text-amber-500 mb-2 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M5.07 19h13.86a2 2 0 001.75-2.97l-6.93-12a2 2 0 00-3.5 0l-6.93 12A2 2 0 005.07 19z"/></svg>
                <h5 class="text-sm font-bold text-gray-900 dark:text-white">Out Of Credits</h5>
                <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm mx-auto mt-1 mb-4">
                    You have <?= $unlock['balance'] ?> credits left. Top up to unlock this lead's budget, contact details, and original source post.
                </p>
                <a href="<?= $baseUrl ?>transactions" data-partial class="inline-flex items-center px-5 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-bold rounded-lg transition-all shadow-sm">
                    View Billing & Credits
                </a>
            </div>
        <?php else: ?>
            <!-- Full Record -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 my-4 text-sm border-t border-b border-gray-100 dark:border-gray-800/80 py-4">
                <div>
                    <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Location</span>
                    <span class="font-medium text-gray-700 dark:text-gray-300"><?= htmlspecialchars(LeadsController::locationLabel($lead)) ?></span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Property Type</span>
                    <span class="font-medium text-gray-700 dark:text-gray-300"><?= htmlspecialchars(LeadsController::propertyTypeLabel($lead)) ?><?= $lead->bedrooms ? " · {$lead->bedrooms} Bed" : '' ?></span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Budget</span>
                    <span class="font-medium text-gray-700 dark:text-gray-300"><?= $budgetLabel ? htmlspecialchars($budgetLabel) : 'Not specified' ?></span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Intent Score</span>
                    <span class="font-medium <?= $intent['classes'] ?>"><?= htmlspecialchars($intent['label']) ?></span>
                </div>
            </div>

            <div class="mb-4">
                <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Original Post</span>
                <blockquote class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-950 border-l-4 border-primary-500 rounded-r-lg p-4 italic">
                    &ldquo;<?= htmlspecialchars($lead->raw_text) ?>&rdquo;
                </blockquote>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Contact Details</span>
                    <span class="font-medium text-gray-700 dark:text-gray-300">
                        <?= $lead->contact_info_raw ? nl2br(htmlspecialchars($lead->contact_info_raw)) : 'Not publicly available for this lead.' ?>
                    </span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Origin Source</span>
                    <span class="font-medium text-gray-700 dark:text-gray-300">
                        <?= htmlspecialchars($lead->source->name ?? 'Public Request Board') ?>
                        <?php if ($lead->source_url): ?>
                            &middot; <a href="<?= htmlspecialchars($lead->source_url) ?>" target="_blank" rel="noopener noreferrer" class="text-primary-600 dark:text-primary-400 hover:underline">View Original</a>
                        <?php endif; ?>
                    </span>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-800/80">
                <span class="text-xs text-gray-400">Category: <?= htmlspecialchars($lead->category->name ?? 'Uncategorized') ?></span>
                <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Status: Active</span>
            </div>
        <?php endif; ?>
    </div>
</div>

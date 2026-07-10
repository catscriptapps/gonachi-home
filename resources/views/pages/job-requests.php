<?php
// /resources/views/pages/job-requests.php

declare(strict_types=1);

/**
 * Gonachi Contractor Discovery Engine - Job Request Feed
 *
 * Real "Job Request Engine" per contractor_discovery.pdf's Phase 2: a
 * client posts a service need (Src\Controller\JobRequestController), it
 * publishes immediately (no moderation gate — unlike landlord reports,
 * nothing here names a specific person), and contractors browse open
 * requests by category/location.
 *
 * Submission is pure AJAX (no page reload) — see
 * resources/js/pages/job-requests-page.js. Photos upload immediately on
 * selection to their own endpoint (job-request-photo-upload.php); the main
 * submit only sends the resulting URLs.
 *
 * "Submit A Quote" stays a disabled "Coming soon" CTA — Bidding & Quotes is
 * a separate, not-yet-built feature.
 *
 * @var bool $isLoggedIn
 * @var string $baseUrl
 * @var string $assetBase
 */

use Src\Controller\JobRequestController;
use Src\Service\AuthService;

$currentUserId = $isLoggedIn ? AuthService::userId() : null;

$registered = isset($_GET['registered']);
$signupRedirect = ltrim($path ?? '/job-requests', '/');

$category = trim($_GET['category'] ?? '');
$location = trim($_GET['location'] ?? '');

$categoryLabels = [
    'plumbing' => 'Plumbing',
    'electrical' => 'Electrical',
    'painting' => 'Painting',
    'building_construction' => 'Building Construction',
    'interior_design' => 'Interior Design',
    'renovation' => 'Renovation',
    'solar_installation' => 'Solar Installation',
    'other' => 'Other',
];

$openRequests = JobRequestController::openRequests($category ?: null, $location ?: null)
    ->appends(['category' => $category, 'location' => $location]);

$totalOpen = JobRequestController::totalOpenCount();
?>
<div class="space-y-6">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Job Requests</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Homeowners posting service needs, matched to your category and location.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-secondary-50 dark:bg-secondary-950/40 text-secondary-600 dark:text-secondary-400 whitespace-nowrap">
                <?= $totalOpen ?> Open Request<?= $totalOpen === 1 ? '' : 's' ?>
            </span>
            <button type="button" id="toggle-post-job-request-btn" class="inline-flex items-center px-4 py-2 bg-gray-900 hover:bg-gray-800 dark:bg-secondary-600 dark:hover:bg-secondary-500 text-white font-bold text-xs rounded-lg transition-colors shadow-sm whitespace-nowrap">
                + Post A Job Request
            </button>
        </div>
    </div>

    <div id="job-request-message"></div>

    <!-- Post A Job Request (toggled) -->
    <div id="post-job-request-section" class="hidden">
        <?php if (!$currentUserId): ?>
            <div class="max-w-lg mx-auto text-center py-16 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl">
                <?php if ($registered): ?>
                    <div class="flex items-start gap-3 text-left bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-900/30 rounded-xl p-4 mb-8 mx-6">
                        <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <div>
                            <h4 class="text-sm font-bold text-emerald-800 dark:text-emerald-300">Account created</h4>
                            <p class="text-xs text-emerald-700 dark:text-emerald-400 mt-0.5">Please sign in below to continue posting your job request.</p>
                        </div>
                    </div>
                <?php endif; ?>
                <svg class="h-10 w-10 text-gray-300 dark:text-gray-700 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <h4 class="text-lg font-bold text-gray-900 dark:text-white">Sign In To Post A Job Request</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 px-6">Requests are tied to your account so you can mark them filled later.</p>
                <div class="mt-6 flex items-center justify-center gap-3">
                    <a href="<?= $baseUrl ?>login" data-login-button class="inline-flex items-center px-5 py-2 bg-secondary-600 hover:bg-secondary-500 text-white text-sm font-bold rounded-lg transition-all shadow-sm">
                        Sign In
                    </a>
                    <a href="<?= $baseUrl ?>signup?redirect=<?= urlencode($signupRedirect) ?>" data-partial class="inline-flex items-center px-5 py-2 border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300 text-sm font-bold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/60 transition-all">
                        Create Account
                    </a>
                </div>
            </div>
        <?php else: ?>
            <form id="job-request-form" novalidate class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 sm:p-8 shadow-sm space-y-6">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="job-request-category" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Service Required</label>
                        <select id="job-request-category" name="service_category" required class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-secondary-500 focus:outline-none text-gray-700 dark:text-gray-300">
                            <option value="">Select a service&hellip;</option>
                            <?php foreach ($categoryLabels as $value => $label): ?>
                                <option value="<?= $value ?>"><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="job-request-location" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Location</label>
                        <input type="text" id="job-request-location" name="location" required placeholder="e.g. Lekki, Lagos" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-secondary-500 focus:outline-none text-gray-900 dark:text-white" />
                    </div>

                    <div>
                        <label for="job-request-budget" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Budget <span class="normal-case font-medium text-gray-400">(optional, &#8358;)</span></label>
                        <input type="number" id="job-request-budget" name="budget" min="0" step="1000" placeholder="e.g. 150000" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-secondary-500 focus:outline-none text-gray-900 dark:text-white" />
                    </div>

                    <div>
                        <label for="job-request-timeline" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Timeline</label>
                        <select id="job-request-timeline" name="timeline" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-secondary-500 focus:outline-none text-gray-700 dark:text-gray-300">
                            <option value="">Select a timeline&hellip;</option>
                            <option value="ASAP">ASAP</option>
                            <option value="Within a week">Within a week</option>
                            <option value="1-2 weeks">1-2 weeks</option>
                            <option value="Flexible">Flexible</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="job-request-description" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Project Description</label>
                        <textarea id="job-request-description" name="description" required rows="4" placeholder="Describe the job&hellip;" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-secondary-500 focus:outline-none text-gray-900 dark:text-white resize-none"></textarea>
                    </div>

                    <div>
                        <label for="job-request-phone" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Contact Phone</label>
                        <input type="tel" id="job-request-phone" name="contact_phone" required placeholder="e.g. 0801 234 5678" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-secondary-500 focus:outline-none text-gray-900 dark:text-white" />
                        <p class="text-xs text-gray-400 mt-1">Kept private — never shown on the public listing.</p>
                    </div>
                </div>

                <div class="border-t border-gray-100 dark:border-gray-800 pt-6">
                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Photos <span class="normal-case font-medium text-gray-400">(optional, up to 6 pictures)</span></label>
                    <button type="button" id="add-job-request-pictures-btn" class="w-full flex flex-col items-center justify-center gap-2 border border-dashed border-gray-300 dark:border-gray-700 rounded-xl p-5 text-sm text-gray-500 dark:text-gray-400 hover:border-secondary-400 hover:text-secondary-600 dark:hover:text-secondary-400 transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        <span>Add Photos</span>
                    </button>
                    <div id="job-request-pictures-preview" class="grid grid-cols-3 sm:grid-cols-4 gap-3 mt-3 empty:mt-0"></div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <span class="text-xs text-gray-400">Your request goes live immediately once submitted.</span>
                    <button type="submit" id="job-request-submit" class="inline-flex items-center px-6 py-2.5 bg-gray-900 hover:bg-gray-800 dark:bg-secondary-600 dark:hover:bg-secondary-500 text-white font-bold text-sm rounded-lg transition-colors shadow-sm whitespace-nowrap">
                        Post Job Request
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <!-- Filter Bar -->
    <form method="GET" action="<?= $baseUrl ?>job-requests" class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col md:flex-row gap-4 items-center">
        <div class="w-full md:w-56">
            <select name="category" class="w-full px-3 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-secondary-500 focus:outline-none text-gray-700 dark:text-gray-300">
                <option value="">All Categories</option>
                <?php foreach ($categoryLabels as $value => $label): ?>
                    <option value="<?= $value ?>" <?= $category === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="w-full md:flex-1">
            <input type="text" name="location" value="<?= htmlspecialchars($location) ?>" placeholder="Filter by location (e.g. Lekki)" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-secondary-500 focus:outline-none text-gray-900 dark:text-white" />
        </div>
        <button type="submit" class="w-full md:w-auto px-6 py-2.5 bg-secondary-600 hover:bg-secondary-700 text-white font-bold text-sm rounded-lg transition-colors shadow-sm whitespace-nowrap">
            Filter
        </button>
        <?php if ($category || $location): ?>
            <a href="<?= $baseUrl ?>job-requests" data-partial class="text-xs font-semibold text-gray-500 hover:text-secondary-600 whitespace-nowrap">Clear</a>
        <?php endif; ?>
    </form>

    <div class="space-y-4">
        <?php if ($openRequests->isEmpty()): ?>
            <div class="bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-800 rounded-xl p-8 text-center">
                <p class="text-sm text-gray-400 dark:text-gray-500">No open job requests match that filter yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($openRequests as $job): ?>
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 shadow-sm hover:border-secondary-500/50 transition-all">
                    <div class="flex items-start justify-between gap-4 mb-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-secondary-100 text-secondary-800 dark:bg-secondary-950 dark:text-secondary-400">
                                    <?= htmlspecialchars($categoryLabels[$job->service_category] ?? ucfirst($job->service_category)) ?>
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400">
                                    Open
                                </span>
                            </div>
                            <h4 class="text-base font-bold text-gray-900 dark:text-white mt-2"><?= htmlspecialchars($job->location) ?></h4>
                        </div>
                        <span class="text-xs font-medium text-gray-400 whitespace-nowrap"><?= htmlspecialchars($job->created_at->diffForHumans()) ?></span>
                    </div>

                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4"><?= htmlspecialchars($job->description) ?></p>

                    <?php if ($job->photos->isNotEmpty()): ?>
                        <div class="flex flex-wrap gap-2 mb-4">
                            <?php foreach ($job->photos as $photo): ?>
                                <button type="button" data-img-src="<?= $assetBase . htmlspecialchars($photo->file_path) ?>" class="block h-16 w-16 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-800">
                                    <img src="<?= $assetBase . htmlspecialchars($photo->file_path) ?>" alt="Job request photo" class="h-full w-full object-cover" />
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 my-4 text-sm border-t border-b border-gray-100 dark:border-gray-800/80 py-3">
                        <div>
                            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Budget</span>
                            <span class="font-medium text-gray-700 dark:text-gray-300"><?= $job->budget !== null ? '&#8358;' . number_format((float) $job->budget) : 'Not disclosed' ?></span>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Timeline</span>
                            <span class="font-medium text-gray-700 dark:text-gray-300"><?= htmlspecialchars($job->timeline ?? 'Flexible') ?></span>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <?php if ($currentUserId && $job->user_id === $currentUserId): ?>
                            <button type="button" data-mark-filled="<?= $job->id ?>" class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-xs rounded-lg transition-colors">
                                Mark As Filled
                            </button>
                        <?php endif; ?>
                        <button disabled title="Coming soon" class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-400 cursor-not-allowed font-bold text-xs rounded-lg transition-colors tracking-wide">
                            Submit A Quote
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($openRequests->lastPage() > 1): ?>
            <div class="flex items-center justify-between pt-2">
                <?php if ($openRequests->previousPageUrl()): ?>
                    <a href="<?= htmlspecialchars($openRequests->previousPageUrl()) ?>" data-partial class="text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-secondary-600 dark:hover:text-secondary-400">&larr; Previous</a>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>

                <span class="text-xs text-gray-400">Page <?= $openRequests->currentPage() ?> of <?= $openRequests->lastPage() ?></span>

                <?php if ($openRequests->nextPageUrl()): ?>
                    <a href="<?= htmlspecialchars($openRequests->nextPageUrl()) ?>" data-partial class="text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-secondary-600 dark:hover:text-secondary-400">Next &rarr;</a>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="bg-secondary-50 dark:bg-secondary-950/40 rounded-xl p-5 border border-secondary-100 dark:border-secondary-900/30 flex items-center justify-between gap-4 flex-wrap">
        <div>
            <h4 class="text-sm font-bold text-secondary-900 dark:text-secondary-300">Free tier: limited bids per month</h4>
            <p class="text-xs text-secondary-700 dark:text-secondary-400 mt-1">Upgrade for unlimited bids, priority placement, and instant lead notifications.</p>
        </div>
        <button disabled title="Coming soon" class="px-4 py-2 bg-white dark:bg-gray-900 text-secondary-600 dark:text-secondary-400 border border-secondary-200 dark:border-secondary-900/50 font-bold text-xs rounded-lg cursor-not-allowed whitespace-nowrap">
            Go Premium
        </button>
    </div>
</div>

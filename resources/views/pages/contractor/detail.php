<?php
// /resources/views/pages/contractor/detail.php
//
// Full contractor profile — the "View Profile" destination linked from the
// directory. Resolved dynamically via resolvePageRoute()'s /{resource}/{id}
// handling (same mechanism as leads/detail.php). Contact details are
// credit-gated here (not on the directory card) — matches Real Estate
// Leads' contact-reveal mechanic exactly (ContractorCreditService is its
// own separate wallet, same shape as CreditService): viewing this page
// auto-spends 1 credit the first time, unless already unlocked. Even once
// unlocked, non-admin viewers see the phone number partly masked (see
// Src\Utils\ContactMasker) and never see the "website" field at all — that
// field doubles as our own sourcing link, same reasoning as Real Estate
// Leads' Origin Source hiding.

declare(strict_types=1);

/**
 * @var bool $isLoggedIn
 * @var string $baseUrl
 */

use Src\Controller\ContractorController;
use Src\Service\AuthService;
use Src\Service\ContractorCreditService;
use Src\Utils\ContactMasker;

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
$unlock = $currentUserId ? ContractorCreditService::unlockContractor($currentUserId, $contractor) : null;
$isAdmin = AuthService::isAdmin();
$isClaimed = $contractor->claim_status === 'claimed';
$categoryLabels = ContractorController::CATEGORY_LABELS;

// A full, absolute URL — this gets copied and pasted onto other platforms
// (social media, business cards, etc.), so a site-relative path alone
// wouldn't work there. Same protocol/host detection as
// ContractorOutreachService::profileUrl() and AuthController's password
// reset link.
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$profileUrl = $protocol . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $baseUrl . 'contractor/' . $contractor->id;
?>
<div class="max-w-3xl mx-auto space-y-6">
    <?php
    $breadcrumbs = [
        ['label' => 'Contractor Discovery', 'href' => $baseUrl . 'contractor-discovery'],
        ['label' => $contractor->business_name],
    ];
    $breadcrumbAccent = 'secondary';
    include __DIR__ . '/../../components/breadcrumbs.php';
    ?>

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

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 my-4 text-sm border-t border-gray-100 dark:border-gray-800/80 pt-4">
            <div>
                <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Location</span>
                <span class="font-medium text-gray-700 dark:text-gray-300"><?= htmlspecialchars($contractor->location) ?></span>
            </div>
            <div>
                <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Operating Areas</span>
                <span class="font-medium text-gray-700 dark:text-gray-300"><?= $contractor->operating_areas ? htmlspecialchars($contractor->operating_areas) : '—' ?></span>
            </div>
        </div>

        <?php if (!$currentUserId): ?>
            <!-- Conversion Gate: matches leads/detail.php's guest gate exactly -->
            <div class="mt-2 bg-gray-50 dark:bg-gray-950 border border-dashed border-gray-300 dark:border-gray-800 rounded-xl p-6 text-center">
                <svg class="h-8 w-8 text-secondary-600 mb-2 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <h5 class="text-sm font-bold text-gray-900 dark:text-white">Contact Details Gated</h5>
                <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm mx-auto mt-1 mb-4">
                    This contractor's phone number unlocks with an account.
                </p>
                <div class="flex items-center justify-center space-x-3">
                    <button type="button" class="register-btn px-5 py-2 bg-secondary-600 hover:bg-secondary-500 text-white text-sm font-bold rounded-lg transition-all shadow-sm">Start Free Trial</button>
                    <a href="<?= $baseUrl ?>login" data-login-button class="px-5 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-lg transition-all">Sign In</a>
                </div>
            </div>
        <?php elseif (!$unlock['success']): ?>
            <!-- Out of Credits Gate -->
            <div class="mt-2 bg-gray-50 dark:bg-gray-950 border border-dashed border-gray-300 dark:border-gray-800 rounded-xl p-6 text-center">
                <svg class="h-8 w-8 text-amber-500 mb-2 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M5.07 19h13.86a2 2 0 001.75-2.97l-6.93-12a2 2 0 00-3.5 0l-6.93 12A2 2 0 005.07 19z"/></svg>
                <h5 class="text-sm font-bold text-gray-900 dark:text-white">Out Of Credits</h5>
                <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm mx-auto mt-1">
                    You have <?= $unlock['balance'] ?> credits left. Top up to unlock this contractor's phone number.
                </p>
            </div>
        <?php else: ?>
            <!-- Full Contact -->
            <div class="grid grid-cols-1 <?= $isAdmin ? 'sm:grid-cols-2' : '' ?> gap-4 mb-4 text-sm border-t border-b border-gray-100 dark:border-gray-800/80 py-4">
                <div>
                    <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Phone</span>
                    <span class="font-medium text-gray-700 dark:text-gray-300">
                        <?php
                        $phoneDisplay = $isAdmin ? $contractor->phone : ContactMasker::mask($contractor->phone);
                        echo $phoneDisplay ? htmlspecialchars($phoneDisplay) : 'Not publicly listed';
                        ?>
                    </span>
                </div>
                <?php if ($isAdmin): ?>
                    <!-- Website: admin-only — this doubles as our own sourcing link
                         (which page we found this business on). Showing it to
                         regular users would let them bypass us entirely on future
                         contractors from that same source, same reasoning as Real
                         Estate Leads' Origin Source hiding. -->
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
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($contractor->description): ?>
            <div class="mb-4">
                <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">About</span>
                <p class="text-sm text-gray-600 dark:text-gray-400"><?= nl2br(htmlspecialchars($contractor->description)) ?></p>
            </div>
        <?php endif; ?>

        <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-800/80 gap-3 flex-wrap">
            <span class="text-xs text-gray-400">Is this your business?</span>

            <?php if ($isClaimed): ?>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">Claimed & Verified</span>
                    <?php if ($currentUserId && (int) $contractor->claimed_by_user_id === $currentUserId): ?>
                        <!-- Owner-only: lets a verified contractor grab their own
                             profile link to share on social media, business cards,
                             etc. — helps them get discovered without relying on
                             luck/being found organically in the directory. -->
                        <button type="button" id="copy-profile-link-btn" data-profile-url="<?= htmlspecialchars($profileUrl) ?>"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-xs rounded-lg transition-colors">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 010 5.656l-3 3a4 4 0 01-5.656-5.656l1.5-1.5m5.656-5.656l1.5-1.5a4 4 0 115.656 5.656l-3 3a4 4 0 01-5.656 0"/></svg>
                            Copy Profile Link
                        </button>
                    <?php endif; ?>
                </div>
            <?php elseif ($contractor->claim_status === 'pending'): ?>
                <button disabled title="Awaiting admin review" class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-400 cursor-not-allowed font-bold text-xs rounded-lg transition-colors tracking-wide">
                    Claim Pending
                </button>
            <?php elseif (!$currentUserId): ?>
                <button type="button" class="auth-gate-btn inline-flex items-center px-4 py-2 bg-secondary-600 hover:bg-secondary-700 text-white font-bold text-xs rounded-lg transition-colors shadow-sm tracking-wide">
                    Claim This Profile
                </button>
            <?php else: ?>
                <button type="button" data-claim-contractor="<?= $contractor->id ?>" class="inline-flex items-center px-4 py-2 bg-secondary-600 hover:bg-secondary-700 text-white font-bold text-xs rounded-lg transition-colors shadow-sm tracking-wide">
                    Claim This Profile
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div id="contractor-claim-message"></div>
</div>

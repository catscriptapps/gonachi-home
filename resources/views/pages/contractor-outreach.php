<?php
// /resources/views/pages/contractor-outreach.php
//
// Admin-only marketing outreach queue: unclaimed, active contractors that
// SerperContractorConnector found a phone and/or email for. Rather than
// waiting for a business to stumble onto its own profile, an admin can
// text/email them here directly, inviting them to claim it — see
// Src\Service\ContractorOutreachService / SmsService / MailService.
//
// @var bool $isLoggedIn
// @var string $baseUrl
// @var string $assetBase

declare(strict_types=1);

use Src\Service\AuthService;
use Src\Service\ContractorOutreachService;
use Src\Service\SmsService;

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

$contractors = ContractorOutreachService::outreachable(null, 15);
$smsConfigured = SmsService::isConfigured();
?>
<div class="space-y-6">
    <?php
    $breadcrumbs = [
        ['label' => 'Contractor Discovery', 'href' => $baseUrl . 'contractor-discovery'],
        ['label' => 'Outreach'],
    ];
    $breadcrumbAccent = 'secondary';
    include __DIR__ . '/../components/breadcrumbs.php';
    ?>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Contractor Outreach</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Reach unclaimed businesses directly — <?= $contractors->total() ?> found with a phone or email on file.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" id="bulk-send-sms-btn" class="inline-flex items-center px-4 py-2 bg-secondary-600 hover:bg-secondary-700 text-white font-bold text-xs rounded-lg transition-colors shadow-sm">
                Send SMS to All (This Page)
            </button>
            <button type="button" id="bulk-send-email-btn" class="inline-flex items-center px-4 py-2 bg-gray-900 hover:bg-gray-800 dark:bg-secondary-600 dark:hover:bg-secondary-500 text-white font-bold text-xs rounded-lg transition-colors shadow-sm">
                Send Email to All (This Page)
            </button>
        </div>
    </div>

    <?php if (!$smsConfigured): ?>
        <div class="bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/40 rounded-xl p-4 text-sm text-amber-800 dark:text-amber-400">
            SMS outreach isn't configured yet — add <code class="font-mono text-xs">TERMII_API_KEY</code> and <code class="font-mono text-xs">TERMII_SENDER_ID</code> to <code class="font-mono text-xs">.env</code> to enable it. Email outreach works independently of this.
        </div>
    <?php endif; ?>

    <?php if ($contractors->isEmpty()): ?>
        <div class="bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-800 rounded-xl p-8 text-center">
            <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300">Nothing To Reach Out To</h4>
            <p class="text-xs text-gray-400 dark:text-gray-500 max-w-sm mx-auto mt-1">
                Every discovered, unclaimed contractor either has no phone/email on file, or has already been claimed.
            </p>
        </div>
    <?php else: ?>
        <div id="outreach-list" class="space-y-3">
            <?php foreach ($contractors as $contractor): ?>
                <div class="outreach-row bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm"
                    data-contractor-id="<?= $contractor->id ?>"
                    data-business-name="<?= htmlspecialchars($contractor->business_name) ?>"
                    data-phone="<?= htmlspecialchars($contractor->phone ?? '') ?>"
                    data-email="<?= htmlspecialchars($contractor->email ?? '') ?>">
                    <div class="flex items-start justify-between gap-4 flex-wrap">
                        <div>
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($contractor->business_name) ?></h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5"><?= htmlspecialchars($contractor->location) ?></p>
                            <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-xs text-gray-600 dark:text-gray-400">
                                <?php if ($contractor->phone): ?>
                                    <span>📱 <?= htmlspecialchars($contractor->phone) ?></span>
                                <?php endif; ?>
                                <?php if ($contractor->email): ?>
                                    <span>✉️ <?= htmlspecialchars($contractor->email) ?></span>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">
                                <?php if ($contractor->outreach_sms_sent_at || $contractor->outreach_email_sent_at): ?>
                                    Last contacted:
                                    <?= $contractor->outreach_sms_sent_at ? 'SMS ' . htmlspecialchars($contractor->outreach_sms_sent_at->diffForHumans()) : '' ?>
                                    <?= $contractor->outreach_sms_sent_at && $contractor->outreach_email_sent_at ? ' · ' : '' ?>
                                    <?= $contractor->outreach_email_sent_at ? 'Email ' . htmlspecialchars($contractor->outreach_email_sent_at->diffForHumans()) : '' ?>
                                <?php else: ?>
                                    Never contacted
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <button type="button" class="send-sms-btn inline-flex items-center px-3 py-1.5 bg-secondary-50 dark:bg-secondary-950/40 text-secondary-700 dark:text-secondary-400 hover:bg-secondary-100 dark:hover:bg-secondary-950/60 font-bold text-xs rounded-lg transition-colors disabled:opacity-40 disabled:cursor-not-allowed" <?= $contractor->phone ? '' : 'disabled title="No phone on file"' ?>>
                                Send SMS
                            </button>
                            <button type="button" class="send-email-btn inline-flex items-center px-3 py-1.5 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 font-bold text-xs rounded-lg transition-colors disabled:opacity-40 disabled:cursor-not-allowed" <?= $contractor->email ? '' : 'disabled title="No email on file"' ?>>
                                Send Email
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($contractors->lastPage() > 1): ?>
            <div class="flex items-center justify-between pt-2">
                <?php if ($contractors->previousPageUrl()): ?>
                    <a href="<?= htmlspecialchars($contractors->previousPageUrl()) ?>" data-partial class="text-sm font-medium text-secondary-600 hover:underline">&larr; Previous</a>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>

                <span class="text-xs text-gray-400">Page <?= $contractors->currentPage() ?> of <?= $contractors->lastPage() ?></span>

                <?php if ($contractors->nextPageUrl()): ?>
                    <a href="<?= htmlspecialchars($contractors->nextPageUrl()) ?>" data-partial class="text-sm font-medium text-secondary-600 hover:underline">Next &rarr;</a>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

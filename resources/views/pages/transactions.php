<?php
// /resources/views/pages/transactions.php
//
// Billing & Credits page — balance, transaction history, and (placeholder,
// no payment gateway wired in yet) credit packs to purchase.

declare(strict_types=1);

/** @var bool $isLoggedIn */

use Src\Service\AuthService;
use Src\Service\CreditService;

$currentUserId = $isLoggedIn ? AuthService::userId() : null;

if (!$currentUserId):
?>
    <div class="max-w-lg mx-auto text-center py-20">
        <svg class="h-10 w-10 text-gray-300 dark:text-gray-700 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Sign In To View Billing</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Your credit balance and unlock history live on your account.</p>
        <a href="<?= $baseUrl ?>login" data-login-button class="inline-flex items-center mt-6 px-5 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-bold rounded-lg transition-all shadow-sm">
            Sign In
        </a>
    </div>
<?php
return;
endif;

$balance = CreditService::getBalance($currentUserId);
$history = CreditService::history($currentUserId);

$reasonLabels = [
    'trial_grant' => ['label' => 'Free Trial Grant', 'classes' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-400'],
    'lead_unlock' => ['label' => 'Lead Unlock', 'classes' => 'bg-primary-100 text-primary-800 dark:bg-primary-950 dark:text-primary-400'],
    'purchase' => ['label' => 'Purchase', 'classes' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-400'],
    'admin_adjustment' => ['label' => 'Adjustment', 'classes' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'],
];

$packs = [
    ['credits' => 10, 'price' => '₦5,000'],
    ['credits' => 25, 'price' => '₦11,000'],
    ['credits' => 60, 'price' => '₦24,000'],
];
?>
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Billing & Credits</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage your credit balance and see where every credit went.</p>
        </div>

        <div class="bg-white dark:bg-gray-900 px-6 py-3 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm text-center flex-shrink-0">
            <span class="block text-3xl font-bold text-primary-600"><?= $balance ?></span>
            <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Available Credits</span>
        </div>
    </div>

    <!-- Credit Packs (placeholder — no payment gateway wired in yet) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <?php foreach ($packs as $pack): ?>
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 text-center shadow-sm">
                <span class="block text-2xl font-bold text-gray-900 dark:text-white"><?= $pack['credits'] ?></span>
                <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Credits</span>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2"><?= $pack['price'] ?></p>
                <button disabled title="Coming soon" class="mt-3 w-full px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-600 font-bold text-xs rounded-lg cursor-not-allowed">
                    Buy Credits
                </button>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Transaction History -->
    <div class="space-y-3">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Transaction History</h3>

        <?php if ($history->isEmpty()): ?>
            <div class="bg-white dark:bg-gray-900 border border-dashed border-gray-300 dark:border-gray-800 rounded-xl p-8 text-center">
                <p class="text-sm text-gray-400 dark:text-gray-500">No transactions yet.</p>
            </div>
        <?php else: ?>
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl divide-y divide-gray-100 dark:divide-gray-800 overflow-hidden shadow-sm">
                <?php foreach ($history as $transaction): ?>
                    <?php $reason = $reasonLabels[$transaction->reason] ?? ['label' => ucfirst($transaction->reason), 'classes' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300']; ?>
                    <div class="flex items-center justify-between p-4">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $reason['classes'] ?>">
                                <?= htmlspecialchars($reason['label']) ?>
                            </span>
                            <span class="text-xs text-gray-400"><?= htmlspecialchars($transaction->created_at->diffForHumans()) ?></span>
                        </div>
                        <div class="text-right">
                            <span class="font-bold text-sm <?= $transaction->amount >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-700 dark:text-gray-300' ?>">
                                <?= $transaction->amount >= 0 ? '+' : '' ?><?= $transaction->amount ?>
                            </span>
                            <span class="block text-xs text-gray-400">Balance: <?= $transaction->balance_after ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($history->lastPage() > 1): ?>
                <div class="flex items-center justify-between pt-2">
                    <?php if ($history->previousPageUrl()): ?>
                        <a href="<?= htmlspecialchars($history->previousPageUrl()) ?>" data-partial class="text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">&larr; Previous</a>
                    <?php else: ?>
                        <span></span>
                    <?php endif; ?>

                    <span class="text-xs text-gray-400">Page <?= $history->currentPage() ?> of <?= $history->lastPage() ?></span>

                    <?php if ($history->nextPageUrl()): ?>
                        <a href="<?= htmlspecialchars($history->nextPageUrl()) ?>" data-partial class="text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">Next &rarr;</a>
                    <?php else: ?>
                        <span></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

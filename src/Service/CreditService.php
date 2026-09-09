<?php
// /src/Service/CreditService.php

declare(strict_types=1);

namespace Src\Service;

use App\Models\CreditAccount;
use App\Models\CreditPurchase;
use App\Models\CreditTransaction;
use App\Models\Lead;
use App\Models\LeadUnlock;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * CreditService
 * Real-estate-leads credit ledger: every new user is lazily granted a
 * trial balance the first time they're looked up, and unlocking a lead's
 * full record spends 1 credit — but only once per (user, lead), so
 * revisiting an already-unlocked lead is always free.
 */
class CreditService
{
    /** Trial credits granted the first time a user's account is touched. */
    private const TRIAL_GRANT = 12;

    /**
     * Purchasable credit packs. amount_kobo is what actually gets charged
     * (and what confirmPurchase() cross-checks against Paystack's own
     * verified amount) — price_label is only for display.
     */
    private const PACKS = [
        1 => ['id' => 1, 'credits' => 10, 'amount_kobo' => 500_000, 'price_label' => '₦5,000'],
        2 => ['id' => 2, 'credits' => 25, 'amount_kobo' => 1_100_000, 'price_label' => '₦11,000'],
        3 => ['id' => 3, 'credits' => 60, 'amount_kobo' => 2_400_000, 'price_label' => '₦24,000'],
    ];

    public static function getBalance(int $userId): int
    {
        return self::ensureAccount($userId)->balance;
    }

    public static function hasUnlocked(int $userId, int $leadId): bool
    {
        return LeadUnlock::where('user_id', $userId)->where('lead_id', $leadId)->exists();
    }

    /**
     * Unlock a lead's full record for this user, spending 1 credit unless
     * they've already unlocked it (or it's free — e.g. contact info was
     * publicly posted, no gate to unlock at all).
     *
     * @return array{success: bool, message: string, balance: int}
     */
    public static function unlockLead(int $userId, Lead $lead): array
    {
        if (self::hasUnlocked($userId, $lead->id)) {
            return ['success' => true, 'message' => 'Already unlocked.', 'balance' => self::getBalance($userId)];
        }

        $account = self::ensureAccount($userId);

        if ($account->balance < 1) {
            return ['success' => false, 'message' => 'Not enough credits.', 'balance' => $account->balance];
        }

        Capsule::connection()->transaction(function () use ($account, $userId, $lead) {
            $account->decrement('balance');

            LeadUnlock::create(['user_id' => $userId, 'lead_id' => $lead->id]);

            CreditTransaction::create([
                'user_id' => $userId,
                'amount' => -1,
                'balance_after' => $account->balance,
                'reason' => 'lead_unlock',
                'reference_type' => 'lead',
                'reference_id' => $lead->id,
            ]);
        });

        return ['success' => true, 'message' => 'Lead unlocked.', 'balance' => $account->fresh()->balance];
    }

    /**
     * Paginated transaction history for the billing page.
     */
    public static function history(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return CreditTransaction::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /** @return array<int, array{id: int, credits: int, amount_kobo: int, price_label: string}> */
    public static function packs(): array
    {
        return array_values(self::PACKS);
    }

    public static function isPurchasingEnabled(): bool
    {
        return PaystackService::isConfigured();
    }

    /**
     * Starts a purchase: mints a fresh reference tied to a pending
     * CreditPurchase row (so confirmPurchase() has a server-trusted amount
     * to verify against — the client only ever picks a pack id, never an
     * amount) and hands back what the client's Paystack inline popup needs.
     *
     * @return array{success: bool, message: string, reference: ?string, amount_kobo: ?int, public_key: ?string}
     */
    public static function initiatePurchase(int $userId, int $packId): array
    {
        if (!PaystackService::isConfigured()) {
            return ['success' => false, 'message' => 'Credit purchases are not available yet.', 'reference' => null, 'amount_kobo' => null, 'public_key' => null];
        }

        $pack = self::PACKS[$packId] ?? null;
        if (!$pack) {
            return ['success' => false, 'message' => 'Invalid credit pack.', 'reference' => null, 'amount_kobo' => null, 'public_key' => null];
        }

        $reference = 'credits_' . $userId . '_' . bin2hex(random_bytes(10));

        CreditPurchase::create([
            'user_id' => $userId,
            'reference' => $reference,
            'credits' => $pack['credits'],
            'amount_kobo' => $pack['amount_kobo'],
            'status' => CreditPurchase::STATUS_PENDING,
        ]);

        return [
            'success' => true,
            'message' => 'ok',
            'reference' => $reference,
            'amount_kobo' => $pack['amount_kobo'],
            'public_key' => PaystackService::publicKey(),
        ];
    }

    /**
     * Verifies a reference with Paystack and, only once, credits the
     * account for it. Safe to call more than once for the same reference —
     * a purchase that's already 'success' just returns the current balance
     * again instead of double-crediting.
     *
     * @return array{success: bool, message: string, balance: int}
     */
    public static function confirmPurchase(int $userId, string $reference): array
    {
        $purchase = CreditPurchase::where('reference', $reference)->where('user_id', $userId)->first();

        if (!$purchase) {
            return ['success' => false, 'message' => 'Purchase not found.', 'balance' => self::getBalance($userId)];
        }

        if ($purchase->status === CreditPurchase::STATUS_SUCCESS) {
            return ['success' => true, 'message' => 'Already credited.', 'balance' => self::getBalance($userId)];
        }

        $verification = PaystackService::verify($reference);

        // A network/config failure on our end (verification['success'] false)
        // is left 'pending' rather than 'failed' — Paystack may still have
        // taken the money, and the client's retry-on-refresh path (calling
        // this again) should still be able to credit it once we can reach
        // Paystack again. Only a definitive non-'success' status from
        // Paystack itself means the payment genuinely didn't go through.
        if (!$verification['success']) {
            return ['success' => false, 'message' => $verification['message'], 'balance' => self::getBalance($userId)];
        }

        if ($verification['status'] !== 'success') {
            $purchase->status = CreditPurchase::STATUS_FAILED;
            $purchase->save();
            return ['success' => false, 'message' => 'Payment was not successful.', 'balance' => self::getBalance($userId)];
        }

        // Paystack's own recorded amount must match what we minted this
        // reference for — otherwise the popup could have been tampered with
        // client-side to charge less than a pack costs.
        if ((int) $verification['amount'] !== (int) $purchase->amount_kobo) {
            $purchase->status = CreditPurchase::STATUS_FAILED;
            $purchase->save();
            return ['success' => false, 'message' => 'Payment amount mismatch.', 'balance' => self::getBalance($userId)];
        }

        $account = self::ensureAccount($userId);

        Capsule::connection()->transaction(function () use ($account, $userId, $purchase) {
            $account->increment('balance', $purchase->credits);

            CreditTransaction::create([
                'user_id' => $userId,
                'amount' => $purchase->credits,
                'balance_after' => $account->balance,
                'reason' => 'purchase',
                'reference_type' => 'credit_purchase',
                'reference_id' => $purchase->id,
            ]);

            $purchase->status = CreditPurchase::STATUS_SUCCESS;
            $purchase->save();
        });

        return ['success' => true, 'message' => 'Credits added.', 'balance' => $account->fresh()->balance];
    }

    /**
     * Fetch (or lazily create + trial-grant) a user's credit account.
     */
    private static function ensureAccount(int $userId): CreditAccount
    {
        $account = CreditAccount::where('user_id', $userId)->first();

        if ($account) {
            return $account;
        }

        return Capsule::connection()->transaction(function () use ($userId) {
            $account = CreditAccount::create(['user_id' => $userId, 'balance' => self::TRIAL_GRANT]);

            CreditTransaction::create([
                'user_id' => $userId,
                'amount' => self::TRIAL_GRANT,
                'balance_after' => self::TRIAL_GRANT,
                'reason' => 'trial_grant',
            ]);

            return $account;
        });
    }
}

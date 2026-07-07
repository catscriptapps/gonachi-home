<?php
// /src/Service/CreditService.php

declare(strict_types=1);

namespace Src\Service;

use App\Models\CreditAccount;
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

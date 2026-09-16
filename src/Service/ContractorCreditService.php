<?php
// /src/Service/ContractorCreditService.php

declare(strict_types=1);

namespace Src\Service;

use App\Models\Contractor;
use App\Models\ContractorCreditAccount;
use App\Models\ContractorCreditTransaction;
use App\Models\ContractorUnlock;
use App\Traits\RecentActivityLogger;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * ContractorCreditService
 * Contractor Discovery's own credit ledger — a separate wallet from Real
 * Estate Leads' CreditService (same mechanic, different money): every new
 * user is lazily granted a trial balance the first time they're looked up,
 * and unlocking a contractor's full contact details spends 1 credit — but
 * only once per (user, contractor), so revisiting an already-unlocked
 * profile is always free.
 */
class ContractorCreditService
{
    use RecentActivityLogger;

    /** Trial credits granted the first time a user's account is touched. */
    private const TRIAL_GRANT = 12;

    public static function getBalance(int $userId): int
    {
        return self::ensureAccount($userId)->balance;
    }

    public static function hasUnlocked(int $userId, int $contractorId): bool
    {
        return ContractorUnlock::where('user_id', $userId)->where('contractor_id', $contractorId)->exists();
    }

    /**
     * Unlock a contractor's full contact details for this user, spending 1
     * credit unless they've already unlocked it.
     *
     * @return array{success: bool, message: string, balance: int}
     */
    public static function unlockContractor(int $userId, Contractor $contractor): array
    {
        if (self::hasUnlocked($userId, $contractor->id)) {
            return ['success' => true, 'message' => 'Already unlocked.', 'balance' => self::getBalance($userId)];
        }

        $account = self::ensureAccount($userId);

        if ($account->balance < 1) {
            return ['success' => false, 'message' => 'Not enough credits.', 'balance' => $account->balance];
        }

        Capsule::connection()->transaction(function () use ($account, $userId, $contractor) {
            $account->decrement('balance');

            ContractorUnlock::create(['user_id' => $userId, 'contractor_id' => $contractor->id]);

            ContractorCreditTransaction::create([
                'user_id' => $userId,
                'amount' => -1,
                'balance_after' => $account->balance,
                'reason' => 'contractor_unlock',
                'reference_type' => 'contractor',
                'reference_id' => $contractor->id,
            ]);

            // Names the exact contact revealed (not just "a contractor") —
            // the user's own private receipt in their History page for why
            // 1 credit just left their balance. Capped at 80 chars so a
            // long phone/notes string can't blow past the 'action' column's
            // length and silently fail to log at all.
            $contact = $contractor->phone ?: 'no phone number listed for this contractor';
            if (mb_strlen($contact) > 80) {
                $contact = mb_substr($contact, 0, 80) . '…';
            }
            self::logActivity(
                "1 credit charged for revealing contact details on \"{$contractor->business_name}\": {$contact}",
                'Contractor',
                $contractor->id,
                $userId
            );
        });

        return ['success' => true, 'message' => 'Contractor unlocked.', 'balance' => $account->fresh()->balance];
    }

    /**
     * Fetch (or lazily create + trial-grant) a user's credit account.
     */
    private static function ensureAccount(int $userId): ContractorCreditAccount
    {
        $account = ContractorCreditAccount::where('user_id', $userId)->first();

        if ($account) {
            return $account;
        }

        return Capsule::connection()->transaction(function () use ($userId) {
            $account = ContractorCreditAccount::create(['user_id' => $userId, 'balance' => self::TRIAL_GRANT]);

            ContractorCreditTransaction::create([
                'user_id' => $userId,
                'amount' => self::TRIAL_GRANT,
                'balance_after' => self::TRIAL_GRANT,
                'reason' => 'trial_grant',
            ]);

            return $account;
        });
    }
}

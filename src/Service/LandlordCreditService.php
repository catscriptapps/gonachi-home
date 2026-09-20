<?php
// /src/Service/LandlordCreditService.php

declare(strict_types=1);

namespace Src\Service;

use App\Models\LandlordContactUnlock;
use App\Models\LandlordCreditAccount;
use App\Models\LandlordCreditTransaction;
use App\Models\LandlordRecord;
use App\Models\TenantRecord;
use App\Models\TenantUnlock;
use App\Traits\RecentActivityLogger;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * LandlordCreditService
 * Landlord & Tenant Validation's own credit ledger — a separate wallet from
 * Real Estate Leads' CreditService and Contractor Discovery's
 * ContractorCreditService (same mechanic, different money): every new user
 * is lazily granted a trial balance the first time they're looked up, and
 * unlocking a landlord's phone number spends 1 credit — but only once per
 * (user, landlord), so revisiting an already-unlocked contact is always
 * free. TRIAL_GRANT of 8 (not 12 like the other two) matches
 * landlord_and_tenant_validation.pdf's own "Free Plan: 8 Contact Unlocks"
 * spec exactly, rather than this app's usual default.
 *
 * Also covers unlockTenantReport() — the Tenant Validation side reported in
 * the same PDF's Step 8 Paid Plan list ("Credits unlock: ... Landlord
 * Records, Tenant Records ..."). It's the same wallet/balance as
 * unlockContact(), not a separate pool, since the spec never distinguishes
 * one.
 */
class LandlordCreditService
{
    use RecentActivityLogger;

    private const TRIAL_GRANT = 8;

    public static function getBalance(int $userId): int
    {
        return self::ensureAccount($userId)->balance;
    }

    public static function hasUnlocked(int $userId, int $landlordId): bool
    {
        return LandlordContactUnlock::where('user_id', $userId)->where('landlord_id', $landlordId)->exists();
    }

    /**
     * Unlock a landlord's phone number for this user, spending 1 credit
     * unless they've already unlocked it.
     *
     * @return array{success: bool, message: string, balance: int}
     */
    public static function unlockContact(int $userId, LandlordRecord $landlord): array
    {
        if (self::hasUnlocked($userId, $landlord->id)) {
            return ['success' => true, 'message' => 'Already unlocked.', 'balance' => self::getBalance($userId)];
        }

        if (!$landlord->phone) {
            return ['success' => false, 'message' => 'No phone number on file for this landlord yet.', 'balance' => self::getBalance($userId)];
        }

        $account = self::ensureAccount($userId);

        if ($account->balance < 1) {
            return ['success' => false, 'message' => 'Not enough credits.', 'balance' => $account->balance];
        }

        Capsule::connection()->transaction(function () use ($account, $userId, $landlord) {
            $account->decrement('balance');

            LandlordContactUnlock::create(['user_id' => $userId, 'landlord_id' => $landlord->id]);

            LandlordCreditTransaction::create([
                'user_id' => $userId,
                'amount' => -1,
                'balance_after' => $account->balance,
                'reason' => 'contact_unlock',
                'reference_type' => 'landlord',
                'reference_id' => $landlord->id,
            ]);

            // Names the exact contact revealed — the user's own private
            // receipt in their History page for why 1 credit just left
            // their balance, same convention as CreditService/
            // ContractorCreditService's own unlock methods.
            self::logActivity(
                "1 credit charged for revealing contact details on \"{$landlord->name}\": {$landlord->phone}",
                'Landlord',
                $landlord->id,
                $userId
            );
        });

        return ['success' => true, 'message' => 'Contact unlocked.', 'balance' => $account->fresh()->balance];
    }

    public static function hasUnlockedTenant(int $userId, int $tenantId): bool
    {
        return TenantUnlock::where('user_id', $userId)->where('tenant_id', $tenantId)->exists();
    }

    /**
     * Unlock a tenant's reference contact for this user, spending 1 credit
     * from the same wallet as unlockContact() — mirrors it exactly, just
     * against TenantUnlock/TenantRecord instead.
     *
     * @return array{success: bool, message: string, balance: int}
     */
    public static function unlockTenantReport(int $userId, TenantRecord $tenant): array
    {
        if (self::hasUnlockedTenant($userId, $tenant->id)) {
            return ['success' => true, 'message' => 'Already unlocked.', 'balance' => self::getBalance($userId)];
        }

        if (!$tenant->reference_phone) {
            return ['success' => false, 'message' => 'No reference contact on file for this tenant yet.', 'balance' => self::getBalance($userId)];
        }

        $account = self::ensureAccount($userId);

        if ($account->balance < 1) {
            return ['success' => false, 'message' => 'Not enough credits.', 'balance' => $account->balance];
        }

        Capsule::connection()->transaction(function () use ($account, $userId, $tenant) {
            $account->decrement('balance');

            TenantUnlock::create(['user_id' => $userId, 'tenant_id' => $tenant->id]);

            LandlordCreditTransaction::create([
                'user_id' => $userId,
                'amount' => -1,
                'balance_after' => $account->balance,
                'reason' => 'tenant_unlock',
                'reference_type' => 'tenant',
                'reference_id' => $tenant->id,
            ]);

            self::logActivity(
                "1 credit charged for revealing reference contact on \"{$tenant->name}\": {$tenant->reference_phone}",
                'Tenant',
                $tenant->id,
                $userId
            );
        });

        return ['success' => true, 'message' => 'Contact unlocked.', 'balance' => $account->fresh()->balance];
    }

    private static function ensureAccount(int $userId): LandlordCreditAccount
    {
        $account = LandlordCreditAccount::where('user_id', $userId)->first();

        if ($account) {
            return $account;
        }

        return Capsule::connection()->transaction(function () use ($userId) {
            $account = LandlordCreditAccount::create(['user_id' => $userId, 'balance' => self::TRIAL_GRANT]);

            LandlordCreditTransaction::create([
                'user_id' => $userId,
                'amount' => self::TRIAL_GRANT,
                'balance_after' => self::TRIAL_GRANT,
                'reason' => 'trial_grant',
            ]);

            return $account;
        });
    }
}

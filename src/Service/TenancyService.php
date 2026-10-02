<?php
// /src/Service/TenancyService.php

declare(strict_types=1);

namespace Src\Service;

use App\Models\LandlordRecord;
use App\Models\PropertyRecord;
use App\Models\Tenancy;
use App\Models\TenantRecord;
use App\Traits\RecentActivityLogger;
use Carbon\Carbon;

/**
 * TenancyService
 * Owns the Tenancy entity — the landlord+tenant(+property) pairing that
 * makes double-blind review release possible (see ReviewService) and that
 * carries the "✓ Verified Tenancy" signal (landlord_and_tenant_validation.pdf
 * §5). Landlord/tenant directory rows are found-or-created by normalized
 * name exactly like the old LandlordDirectoryController/TenantDirectoryController
 * did; a Tenancy itself is deduped at the app level (not a DB unique
 * constraint — property_id is nullable, so MySQL's NULL != NULL would let
 * duplicates slip past a DB-level unique anyway).
 */
class TenancyService
{
    use RecentActivityLogger;

    /**
     * Find-or-create the landlord + tenant directory rows by normalized
     * name, optionally find-or-create a property under the landlord, then
     * find-or-create the Tenancy itself. The CALLER becomes
     * initiated_by_user_id and gets this Tenancy as "confirmed" on their own
     * side immediately (no one needs to confirm a relationship THEY
     * themselves are asserting) — the counterpart's confirmation is a
     * separate, later action (see confirm()).
     *
     * @param 'landlord'|'tenant' $initiatorRole Which side the current user is reviewing FROM.
     */
    public static function findOrCreate(
        int $initiatorUserId,
        string $initiatorRole,
        string $landlordName,
        string $tenantName,
        ?string $address,
        ?string $propertyType,
        ?int $countryId
    ): Tenancy {
        $landlord = LandlordRecord::firstOrCreate(
            ['normalized_name' => self::normalize($landlordName)],
            ['name' => $landlordName]
        );

        $tenant = TenantRecord::firstOrCreate(
            ['normalized_name' => self::normalize($tenantName)],
            ['name' => $tenantName]
        );

        if ($initiatorRole === 'landlord' && !$landlord->user_id) {
            $landlord->update(['user_id' => $initiatorUserId]);
        }
        if ($initiatorRole === 'tenant' && !$tenant->user_id) {
            $tenant->update(['user_id' => $initiatorUserId]);
        }

        $property = null;
        if ($address !== null && trim($address) !== '') {
            $property = PropertyRecord::firstOrCreate(
                ['landlord_id' => $landlord->id, 'normalized_address' => self::normalize($address)],
                [
                    'address' => trim($address),
                    'property_type' => $propertyType ?: null,
                    'country_id' => $countryId,
                ]
            );
        }

        $tenancy = Tenancy::where('landlord_id', $landlord->id)
            ->where('tenant_id', $tenant->id)
            ->when($property, fn ($q) => $q->where('property_id', $property->id))
            ->when(!$property, fn ($q) => $q->whereNull('property_id'))
            ->first();

        if (!$tenancy) {
            $tenancy = Tenancy::create([
                'landlord_id' => $landlord->id,
                'tenant_id' => $tenant->id,
                'property_id' => $property?->id,
                'initiated_by_user_id' => $initiatorUserId,
                'country_id' => $countryId,
            ]);
        }

        if ($initiatorRole === 'landlord' && !$tenancy->landlord_confirmed_at) {
            $tenancy->update(['landlord_confirmed_at' => Carbon::now()]);
        }
        if ($initiatorRole === 'tenant' && !$tenancy->tenant_confirmed_at) {
            $tenancy->update(['tenant_confirmed_at' => Carbon::now()]);
        }

        self::recomputeVerification($tenancy->fresh());

        return $tenancy->fresh();
    }

    /**
     * The COUNTERPART (not the initiator) confirming a tenancy claim made
     * about them — e.g. a tenant confirming "yes, John D. was my landlord at
     * this property". Logs to RecentActivity so the confirming user has a
     * record of the action, same convention as LandlordCreditService's
     * unlock methods.
     */
    public static function confirm(Tenancy $tenancy, int $confirmingUserId, string $confirmingRole): Tenancy
    {
        if ($confirmingRole === 'landlord') {
            $landlord = LandlordRecord::find($tenancy->landlord_id);
            if ($landlord && !$landlord->user_id) {
                $landlord->update(['user_id' => $confirmingUserId]);
            }
            if (!$tenancy->landlord_confirmed_at) {
                $tenancy->update(['landlord_confirmed_at' => Carbon::now()]);
            }
        } else {
            $tenant = TenantRecord::find($tenancy->tenant_id);
            if ($tenant && !$tenant->user_id) {
                $tenant->update(['user_id' => $confirmingUserId]);
            }
            if (!$tenancy->tenant_confirmed_at) {
                $tenancy->update(['tenant_confirmed_at' => Carbon::now()]);
            }
        }

        self::logActivity('Confirmed a tenancy relationship', 'Tenancy', $tenancy->id, $confirmingUserId);

        self::recomputeVerification($tenancy->fresh());

        return $tenancy->fresh();
    }

    /**
     * Recomputes is_verified/verification_method. Two signals, checked in
     * order:
     *   1. Mutual confirmation (strongest): both landlord and tenant
     *      directory rows are linked to real accounts AND both sides have
     *      independently confirmed this exact tenancy.
     *   2. Property association (fallback): this tenancy's property is
     *      independently corroborated — either another tenancy at the same
     *      property has a review from a different reviewer, or the property
     *      record's own landlord_id matches this tenancy's landlord.
     * Never retroactively swept across OTHER historical tenancies when a
     * new one could qualify them after the fact — low severity since the
     * primary signal is unaffected.
     */
    public static function recomputeVerification(Tenancy $tenancy): void
    {
        $landlord = LandlordRecord::find($tenancy->landlord_id);
        $tenant = TenantRecord::find($tenancy->tenant_id);

        $mutualConfirmation = $landlord?->user_id !== null
            && $tenant?->user_id !== null
            && $tenancy->landlord_confirmed_at !== null
            && $tenancy->tenant_confirmed_at !== null;

        if ($mutualConfirmation) {
            $tenancy->update(['is_verified' => true, 'verification_method' => 'mutual_confirmation']);
            return;
        }

        // Property association: a DIFFERENT tenancy at the same property has
        // a review from a reviewer other than this tenancy's own initiator —
        // i.e. a genuinely independent second party corroborated the same
        // property. Deliberately does NOT check "this property's own
        // landlord_id matches this tenancy's landlord" — TenancyService::
        // findOrCreate() always creates the property under the SAME
        // landlord_id as the tenancy that spawned it, so that check would be
        // tautologically true for every single tenancy and verify everyone
        // on their very first, one-sided submission.
        if ($tenancy->property_id !== null) {
            $otherPartyAtSameProperty = Tenancy::where('property_id', $tenancy->property_id)
                ->where('id', '!=', $tenancy->id)
                ->whereHas('reviews', fn ($q) => $q->where('reviewer_user_id', '!=', $tenancy->initiated_by_user_id))
                ->exists();

            if ($otherPartyAtSameProperty) {
                $tenancy->update(['is_verified' => true, 'verification_method' => 'property_association']);
                return;
            }
        }

        $tenancy->update(['is_verified' => false, 'verification_method' => null]);
    }

    private static function normalize(string $value): string
    {
        return mb_strtolower(preg_replace('/\s+/', ' ', trim($value)));
    }
}

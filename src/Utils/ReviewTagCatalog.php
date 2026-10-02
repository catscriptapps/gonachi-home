<?php
// /src/Utils/ReviewTagCatalog.php

declare(strict_types=1);

namespace Src\Utils;

/**
 * Fixed, cosmetic structured-tag lists for landlord/tenant reviews — exact
 * strings from landlord_and_tenant_validation.pdf. Unlike ltv_review_criteria
 * (which carries weights/prompts/order a product owner might legitimately
 * want to tune), these are a tiny, truly static list, so they live as a PHP
 * constant rather than a DB table — Src\Service\ReviewService::submit()
 * validates every submitted tag_key against this list before insert.
 */
final class ReviewTagCatalog
{
    public const LANDLORD = [
        'responsive_to_repairs' => 'Responsive to repairs',
        'professional_communication' => 'Professional communication',
        'well_maintained_property' => 'Well-maintained property',
        'clear_lease_terms' => 'Clear lease terms',
        'good_move_in_experience' => 'Good move-in experience',
    ];

    public const TENANT = [
        'reliable_payments' => 'Reliable payments',
        'good_communication' => 'Good communication',
        'property_well_maintained' => 'Property well maintained',
        'cooperative_with_maintenance' => 'Cooperative with maintenance',
        'smooth_move_out' => 'Smooth move-out',
    ];

    /**
     * @return array<string, string>
     */
    public static function forSubject(string $subjectType): array
    {
        return $subjectType === 'tenant' ? self::TENANT : self::LANDLORD;
    }

    public static function isValid(string $subjectType, string $tagKey): bool
    {
        return isset(self::forSubject($subjectType)[$tagKey]);
    }

    public static function labelFor(string $subjectType, string $tagKey): ?string
    {
        return self::forSubject($subjectType)[$tagKey] ?? null;
    }
}

<?php
// /src/Utils/ReviewReportReasons.php

declare(strict_types=1);

namespace Src\Utils;

/**
 * Fixed "Report Review" dispute reasons — exact list from
 * landlord_and_tenant_validation.pdf §9. Src\Service\ReviewService::report()
 * validates against this before insert.
 */
final class ReviewReportReasons
{
    public const REASONS = [
        'no_tenancy_relationship' => 'I did not have a tenancy relationship with this person.',
        'false_information' => 'Contains false factual information.',
        'discriminatory_hateful' => 'Contains discriminatory or hateful content.',
        'threats_harassment' => 'Contains threats or harassment.',
        'private_personal_information' => 'Contains private/personal information.',
        'irrelevant_information' => 'Contains irrelevant information.',
        'conflict_of_interest' => 'Conflict of interest.',
        'other' => 'Other.',
    ];

    public static function isValid(string $reason): bool
    {
        return isset(self::REASONS[$reason]);
    }

    public static function labelFor(string $reason): ?string
    {
        return self::REASONS[$reason] ?? null;
    }
}

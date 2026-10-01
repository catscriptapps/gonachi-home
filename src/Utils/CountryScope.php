<?php
// /src/Utils/CountryScope.php

declare(strict_types=1);

namespace Src\Utils;

/**
 * Single source of truth for the handful of country codes Gonachi currently
 * supports (Real Estate Leads, Contractor Discovery, Landlord & Tenant
 * Validation all route on /{project}/{cc}). IDS values are `countries.id`
 * from the live DB — Nigeria is 161 (not 160, which is Niger).
 */
final class CountryScope
{
    public const IDS = ['ng' => 161, 'us' => 233, 'ca' => 39];
    public const NAMES = ['ng' => 'Nigeria', 'us' => 'United States', 'ca' => 'Canada'];
    public const FLAGS = ['ng' => '🇳🇬', 'us' => '🇺🇸', 'ca' => '🇨🇦'];

    public static function isSupported(string $cc): bool
    {
        return isset(self::IDS[$cc]);
    }

    public static function idFor(string $cc): ?int
    {
        return self::IDS[$cc] ?? null;
    }

    public static function nameFor(string $cc): string
    {
        return self::NAMES[$cc] ?? strtoupper($cc);
    }
}

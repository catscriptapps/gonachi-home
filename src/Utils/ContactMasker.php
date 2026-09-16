<?php
// /src/Utils/ContactMasker.php

declare(strict_types=1);

namespace Src\Utils;

/**
 * Partly-obfuscates phone numbers and emails within free-text contact info
 * (e.g. "+2348012345678" -> "+234*****", "buyer@example.com" ->
 * "bu****@example.com") — shared by every project that credit-gates a
 * contact reveal (Real Estate Leads' LeadsController, Contractor
 * Discovery's ContractorController) so the raw scraped contact itself never
 * appears verbatim outside of an admin session, even after a viewer has
 * spent a credit to "unlock" it.
 */
final class ContactMasker
{
    public static function mask(?string $raw): ?string
    {
        if ($raw === null || trim($raw) === '') {
            return $raw;
        }

        $masked = preg_replace_callback(
            '/[\w.+-]+@[\w-]+\.[\w.-]+/',
            fn(array $m) => self::maskEmail($m[0]),
            $raw
        );

        return preg_replace_callback(
            '/\+?\d[\d\s\-]{5,}\d/',
            fn(array $m) => mb_substr($m[0], 0, 4) . '*****',
            $masked
        );
    }

    private static function maskEmail(string $email): string
    {
        [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');
        return mb_substr($local, 0, 2) . '****@' . $domain;
    }
}

<?php
// /src/Service/ContactInfoParser.php

declare(strict_types=1);

namespace Src\Service;

/**
 * Extracts a phone number and/or email address from free-form scraped text
 * — used by LeadIngestionService to populate rel_leads.phone/email so
 * Lead::scopeComplete() can require a real, reachable contact before a lead
 * surfaces publicly. Mirrors the extraction patterns already proven in
 * Contractor Discovery's own pipeline
 * (src/Service/ContractorSources/SerperContractorConnector.php's
 * extractPhone()/extractEmail()) rather than inventing new regex.
 */
final class ContactInfoParser
{
    /**
     * Nigerian-formatted phone number: local (0801234567X) or international
     * (+234801234567X).
     */
    public static function extractPhone(string $text): ?string
    {
        if (preg_match('/(\+?234[\s-]?\d{3}[\s-]?\d{3}[\s-]?\d{4}|0\d{3}[\s-]?\d{3}[\s-]?\d{4})/', $text, $matches)) {
            return preg_replace('/[\s-]/', '', $matches[0]);
        }

        return null;
    }

    public static function extractEmail(string $text): ?string
    {
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text, $matches)) {
            return strtolower($matches[0]);
        }

        return null;
    }
}

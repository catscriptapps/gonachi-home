<?php
// /src/Service/LeadIntentClassifier.php

declare(strict_types=1);

namespace Src\Service;

/**
 * Keyword/regex-rule intent classifier. Deliberately simple (v1): a real
 * NLP/LLM classifier can replace this later without touching connectors or
 * ingestion — it only needs to keep producing ClassifiedLead|null.
 */
final class LeadIntentClassifier
{
    private const BUY_PATTERNS = [
        '/\blooking for\b/i',
        '/\blooking to buy\b/i',
        '/\bwanted\s*:?/i',
        '/\bwant to buy\b/i',
        '/\bseeking\b/i',
        '/\bin need of\b/i',
    ];

    private const SELL_PATTERNS = [
        '/\bfor sale\b/i',
        '/\blooking to sell\b/i',
        '/\bwant(?:s|ed)? to sell\b/i',
        '/\bup for sale\b/i',
    ];

    private const RENT_PATTERNS = [
        '/\bto let\b/i',
        '/\blooking to rent\b/i',
        '/\bfor rent\b/i',
    ];

    private const INVEST_PATTERNS = [
        '/\binvestment\s*(property|opportunit)/i',
        '/\bproperty investors?\b/i',
        '/\blooking to invest\b/i',
    ];

    private const PROPERTY_NOUN_PATTERN = '/\b(house|home|land|plot|property|apartment|flat|duplex|bungalow|office|shop|warehouse|terrace|estate)\b/i';

    /** @var string[] */
    private array $knownLocations;

    /**
     * @param string[] $knownLocations Location names to match against, longest first
     */
    public function __construct(array $knownLocations = [])
    {
        usort($knownLocations, fn (string $a, string $b) => strlen($b) <=> strlen($a));
        $this->knownLocations = $knownLocations;
    }

    public function classify(string $text): ?ClassifiedLead
    {
        $text = trim($text);
        if ($text === '') {
            return null;
        }

        $requestType = $this->detectRequestType($text);
        if ($requestType === null) {
            return null;
        }

        $propertyType = $this->detectPropertyType($text);
        $bedrooms = $this->detectBedrooms($text);
        $locationRaw = $this->detectLocation($text);
        [$budgetMin, $budgetMax] = $this->detectBudget($text);

        return new ClassifiedLead(
            requestType: $requestType,
            propertyType: $propertyType,
            bedrooms: $bedrooms,
            locationRaw: $locationRaw,
            budgetMin: $budgetMin,
            budgetMax: $budgetMax,
            intentLevel: $this->scoreIntent($text, $locationRaw, $budgetMin),
        );
    }

    private function detectRequestType(string $text): ?string
    {
        // Order matters: check more specific phrasing before generic "buy" patterns,
        // since e.g. "looking to rent" would also match a loose buy pattern.
        if ($this->matchesAny($text, self::RENT_PATTERNS)) {
            return 'renter';
        }
        if ($this->matchesAny($text, self::INVEST_PATTERNS)) {
            return 'investor';
        }
        if ($this->matchesAny($text, self::SELL_PATTERNS)) {
            return 'seller';
        }
        if ($this->matchesAny($text, self::BUY_PATTERNS) && preg_match(self::PROPERTY_NOUN_PATTERN, $text)) {
            return 'buyer';
        }

        return null;
    }

    private function detectPropertyType(string $text): ?string
    {
        if (preg_match('/\b(land|plot)\b/i', $text)) {
            return 'land';
        }
        if (preg_match('/\b(office|shop|warehouse|commercial)\b/i', $text)) {
            return 'commercial';
        }
        if (preg_match('/\b(house|home|apartment|flat|duplex|bungalow|terrace|estate)\b/i', $text)) {
            return 'residential';
        }

        return null;
    }

    private function detectBedrooms(string $text): ?int
    {
        if (preg_match('/(\d+)\s*[- ]?bed(?:room)?s?\b/i', $text, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    private function detectLocation(string $text): ?string
    {
        foreach ($this->knownLocations as $location) {
            if (preg_match('/\b' . preg_quote($location, '/') . '\b/i', $text)) {
                return $location;
            }
        }

        return null;
    }

    /**
     * @return array{0: ?float, 1: ?float}
     */
    private function detectBudget(string $text): array
    {
        if (!preg_match('/(?:₦|N|NGN)\s?([\d,]+(?:\.\d+)?)\s*(million|m|k|thousand)?\b/i', $text, $m)) {
            return [null, null];
        }

        $amount = (float) str_replace(',', '', $m[1]);
        $suffix = strtolower($m[2] ?? '');

        $amount = match ($suffix) {
            'million', 'm' => $amount * 1_000_000,
            'k', 'thousand' => $amount * 1_000,
            default => $amount,
        };

        return [$amount, $amount];
    }

    private function scoreIntent(string $text, ?string $locationRaw, ?float $budgetMin): string
    {
        $hasStrongPhrase = (bool) preg_match('/\b(looking for|wanted|seeking|urgently)\b/i', $text);

        if ($hasStrongPhrase && $locationRaw !== null) {
            return 'high';
        }
        if ($hasStrongPhrase || $locationRaw !== null || $budgetMin !== null) {
            return 'medium';
        }

        return 'low';
    }

    private function matchesAny(string $text, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text) === 1) {
                return true;
            }
        }

        return false;
    }
}

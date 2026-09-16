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

    /**
     * Signals that a page is informational content about real estate in
     * general (a blog post, a news article, a market-trends/guide piece)
     * rather than one specific buyer/seller/renter's own request — the kind
     * of thing a broad web-search connector (see RequiresCompleteListingInfo)
     * turns up alongside actual listings/requests. Checked before intent
     * detection, since these often also happen to contain buy/sell/invest
     * phrasing (e.g. "Looking to invest? Here's our guide...").
     */
    private const NON_LISTING_PATTERNS = [
        '/\b(blog|news|editorial|press release)\b/i',
        '/\b(ultimate|complete|beginner\'?s|comprehensive)\s+guide\b/i',
        '/\bguide\s+to\b/i',
        '/\btop\s*\d+\b/i',
        '/\bbest\s+(areas?|places?|neighbo(?:u)?rhoods?|locations?)\b/i',
        '/\bhow\s+to\b/i',
        '/\bwhy\s+you\s+should\b/i',
        '/\bmarket\s+(report|trends?|update|analysis|outlook|forecast)\b/i',
        '/\b(everything|things)\s+you\s+need\s+to\s+know\b/i',
        '/\bfaqs?\b/i',
    ];

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

    /**
     * @param bool $requireBudget When true (web-search connectors — see
     *                            RequiresCompleteListingInfo), a candidate
     *                            with no detectable budget is rejected
     *                            outright rather than merely scored lower —
     *                            a specific individual request almost always
     *                            names a price, so its absence is itself a
     *                            strong signal this is informational content
     *                            that slipped past the NON_LISTING_PATTERNS
     *                            check above.
     */
    public function classify(string $text, bool $requireBudget = false): ?ClassifiedLead
    {
        $text = trim($text);
        if ($text === '') {
            return null;
        }

        if ($this->matchesAny($text, self::NON_LISTING_PATTERNS)) {
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

        if ($requireBudget && $budgetMin === null) {
            return null;
        }

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
        // Currency-symbol-prefixed amount: ₦500,000 / N5m / NGN 2 million.
        if (preg_match('/(?:₦|N|NGN)\s?([\d,]+(?:\.\d+)?)\s*(million|m|k|thousand)?\b/i', $text, $m)) {
            return $this->normalizeAmount($m[1], $m[2] ?? '');
        }

        // "budget" named explicitly with no currency symbol — common in
        // forum-style posts ("Budget: 15m", "budget of 5 million naira").
        if (preg_match('/\bbudget\b[^\d]{0,15}([\d,]+(?:\.\d+)?)\s*(million|m|k|thousand)?\b/i', $text, $m)) {
            return $this->normalizeAmount($m[1], $m[2] ?? '');
        }

        return [null, null];
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function normalizeAmount(string $rawAmount, string $suffix): array
    {
        $amount = (float) str_replace(',', '', $rawAmount);
        $suffix = strtolower($suffix);

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

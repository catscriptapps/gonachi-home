<?php
// /src/Service/ContractorListingClassifier.php

declare(strict_types=1);

namespace Src\Service;

/**
 * Filters out content that isn't one specific contractor's own profile —
 * everything a general web-search connector (SerperContractorConnector)
 * turns up alongside actual individual business listings:
 *  - Informational content: a blog post, a news article, a "Top Ten..."
 *    listicle — e.g. "Top Ten Solar Energy Companies in Abuja" is a roundup
 *    article, not a business.
 *  - Trade associations/industry bodies — e.g. "Abuja Ambassadors for Solar
 *    Energy" turned out to be a registered trade association representing
 *    many businesses, not one contractor.
 *  - Aggregated multi-business snippets — a search snippet that mashes
 *    together several different people/companies' contact info in one
 *    result (common when the underlying page is itself a forum thread with
 *    multiple replies, or a directory listing many businesses at once).
 * Deliberately simple (v1), same convention as Src\Service\
 * LeadIntentClassifier's own NON_LISTING_PATTERNS.
 */
final class ContractorListingClassifier
{
    private const NON_LISTING_PATTERNS = [
        '/\b(blog|news|editorial|press release)\b/i',
        '/\btop\s*(\d+|ten|nine|eight|seven|six|five|four|three|two|one)\b/i',
        '/\bbest\s+\d*\s*(companies|contractors|firms|businesses|services|providers)\b/i',
        '/\b(ultimate|complete|beginner\'?s|comprehensive)\s+guide\b/i',
        '/\bguide\s+to\b/i',
        '/\bhow\s+to\b/i',
        '/\bwhy\s+you\s+should\b/i',
        '/\b(list of|complete list)\b/i',
        '/\b\d+\s+(best|top)\b/i',
        '/\bvs\.?\b/i',
    ];

    /**
     * Phrasing specific enough that a real single-business description is
     * very unlikely to use it incidentally (unlike a bare "association" or
     * "advocates", which a contractor might legitimately say about itself).
     */
    private const ASSOCIATION_PATTERNS = [
        '/\btrade\s+association\b/i',
        '/\b(registered\s+)?association\s+of\b/i',
        '/\bambassadors?\s+for\b/i',
        '/\bchapter\s+of\b/i',
        '/\bfederation\s+of\b/i',
        '/\bcouncil\s+of\b/i',
        '/\binstitute\s+of\b/i',
        '/\bunion\s+of\b/i',
        '/\bprofessional\s+body\b/i',
    ];

    /**
     * @param string $title Business name as scraped (before any cleanup).
     * @param string|null $snippet Search-result description, if any.
     */
    public function isListing(string $title, ?string $snippet = null): bool
    {
        $text = trim($title . ' ' . ($snippet ?? ''));
        if ($text === '') {
            return false;
        }

        if ($this->matchesAny($text, self::NON_LISTING_PATTERNS)) {
            return false;
        }

        if ($this->matchesAny($text, self::ASSOCIATION_PATTERNS)) {
            return false;
        }

        if ($this->looksLikeMultipleEntities($text)) {
            return false;
        }

        return true;
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

    /**
     * True when the text reads like several different people/companies'
     * contact info concatenated into one snippet, rather than one
     * business's own description — semicolon-delimited, with multiple
     * segments each containing a name-like run of 2+ consecutive
     * capitalized words (e.g. "Abduljalal Muhammad Lamido", "Engr Daniel").
     * Requires at least 3 semicolon segments in the first place (a single
     * stray semicolon in a normal description is common and not a signal
     * on its own) and at least 2 of them to be name-like, keeping this
     * conservative against false-positiving on a genuinely single-business
     * description that just happens to list e.g. several service areas.
     */
    private function looksLikeMultipleEntities(string $text): bool
    {
        $segments = preg_split('/\s*;\s*/', $text);
        if (count($segments) < 3) {
            return false;
        }

        $nameLikeSegments = 0;
        foreach ($segments as $segment) {
            // A "Label: Value" segment (e.g. "Location: Victoria Island") is
            // a structured field belonging to ONE profile, not a new
            // entity's own intro — skip it so a single business's own
            // multi-field description (Director name, location, ...) can't
            // trip this the way id=57's actual multi-person text does.
            if (str_contains($segment, ':')) {
                continue;
            }

            if (preg_match('/\b(?:[A-Z][a-zA-Z.]*\s+){1,}[A-Z][a-zA-Z.]*\b/', $segment)) {
                $nameLikeSegments++;
            }
        }

        return $nameLikeSegments >= 2;
    }
}

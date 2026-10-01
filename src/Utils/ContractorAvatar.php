<?php
// /src/Utils/ContractorAvatar.php

declare(strict_types=1);

namespace Src\Utils;

/**
 * Deterministic placeholder avatar for a contractor with no uploaded photo
 * yet — a colored initials card, same idea as the generated-initial fallback
 * already used for Users without an avatar_url (see layout-header.php /
 * profile.php), extended with a per-business-name color so every contractor
 * gets a visually distinct placeholder instead of one flat color. Uses
 * inline styles rather than Tailwind utility classes since the color is
 * picked at runtime from PHP — a dynamically-interpolated class name
 * wouldn't be in any file Tailwind's content scanner reads, so JIT would
 * never generate it (see the project's known "unscanned dynamic class"
 * gotcha). Replaced by a real uploaded photo (cde_contractors.avatar_url)
 * once a contractor claims their profile.
 */
final class ContractorAvatar
{
    /** [from, to] hex pairs for a 135deg gradient background. */
    private const PALETTE = [
        ['#f43f5e', '#fb923c'],
        ['#f59e0b', '#eab308'],
        ['#10b981', '#14b8a6'],
        ['#06b6d4', '#0ea5e9'],
        ['#6366f1', '#3b82f6'],
        ['#8b5cf6', '#a855f7'],
        ['#d946ef', '#ec4899'],
        ['#84cc16', '#22c55e'],
    ];

    /**
     * Up to 2 uppercase initials from the business name — one per word for
     * the first two words, or the first two letters of a single-word name.
     */
    public static function initials(string $name): string
    {
        $words = array_values(array_filter(preg_split('/\s+/', trim($name)) ?: []));

        if (empty($words)) {
            return '?';
        }

        if (count($words) === 1) {
            return mb_strtoupper(mb_substr($words[0], 0, 2));
        }

        return mb_strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1));
    }

    /**
     * A CSS `background` value, deterministic from $seed (the business
     * name) so the same contractor always gets the same color.
     */
    public static function gradientStyle(string $seed): string
    {
        [$from, $to] = self::PALETTE[crc32($seed) % count(self::PALETTE)];

        return "background: linear-gradient(135deg, {$from}, {$to});";
    }
}

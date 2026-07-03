<?php
// /src/Service/LeadSources/LeadCandidate.php

declare(strict_types=1);

namespace Src\Service\LeadSources;

/**
 * A raw, unclassified item pulled from a source, before intent
 * classification and dedup have run.
 */
final class LeadCandidate
{
    public function __construct(
        public readonly string $externalId,
        public readonly ?string $url,
        public readonly string $text,
        public readonly ?\DateTimeImmutable $postedAt = null,
        public readonly ?string $contactInfoRaw = null,
    ) {
    }
}

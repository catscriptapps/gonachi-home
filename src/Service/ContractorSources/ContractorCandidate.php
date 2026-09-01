<?php
// /src/Service/ContractorSources/ContractorCandidate.php

declare(strict_types=1);

namespace Src\Service\ContractorSources;

/**
 * A raw, unclassified contractor listing pulled from a source, before
 * dedup has run. Mirrors Src\Service\LeadSources\LeadCandidate.
 */
final class ContractorCandidate
{
    public function __construct(
        public readonly string $externalId,
        public readonly string $businessName,
        public readonly string $serviceCategory,
        public readonly string $location,
        public readonly ?string $website,
        public readonly ?string $description,
        public readonly ?string $phone = null,
    ) {
    }
}

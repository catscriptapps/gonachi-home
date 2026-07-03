<?php
// /src/Service/LeadSources/LeadSourceConnector.php

declare(strict_types=1);

namespace Src\Service\LeadSources;

/**
 * Contract for a single-source lead connector. Each implementation is
 * responsible for one source only (one forum, one search API, etc.) and
 * knows nothing about classification, dedup, or storage.
 */
interface LeadSourceConnector
{
    /**
     * @param array<string, mixed> $config Per-source settings from lead_sources.config
     * @return iterable<LeadCandidate>
     */
    public function fetchCandidates(array $config): iterable;
}

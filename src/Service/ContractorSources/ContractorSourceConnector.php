<?php
// /src/Service/ContractorSources/ContractorSourceConnector.php

declare(strict_types=1);

namespace Src\Service\ContractorSources;

/**
 * Contract for a single-source contractor-discovery connector. Mirrors
 * Src\Service\LeadSources\LeadSourceConnector.
 */
interface ContractorSourceConnector
{
    /**
     * @param array<string, mixed> $config Per-source settings from cde_contractor_sources.config
     * @return iterable<ContractorCandidate>
     */
    public function fetchCandidates(array $config): iterable;
}

<?php
// /src/Service/ContractorIngestionService.php

declare(strict_types=1);

namespace Src\Service;

use App\Models\Contractor;
use App\Models\ContractorSource;
use Src\Service\ContractorSources\ContractorCandidate;

/**
 * Takes raw candidates from a ContractorSourceConnector, dedups against
 * existing contractors, filters out informational content (blog posts,
 * "Top Ten..." listicles — see ContractorListingClassifier) that a
 * web-search connector turns up alongside real listings, and stores new
 * ones. Profiles that pass publish immediately — contractor_discovery.pdf's
 * Phase 1 spec calls for that ("ensures the platform appears active and
 * searchable from day one"), matching how the existing admin-curated seed
 * contractors already work (status=active, claim_status=unclaimed on
 * creation) — there's just no *manual* review queue, unlike Leads.
 */
final class ContractorIngestionService
{
    private ContractorListingClassifier $classifier;

    public function __construct(?ContractorListingClassifier $classifier = null)
    {
        $this->classifier = $classifier ?? new ContractorListingClassifier();
    }

    /**
     * @param iterable<ContractorCandidate> $candidates
     * @return array{found: int, new: int, duplicate: int, rejected: int}
     */
    public function ingest(ContractorSource $source, iterable $candidates): array
    {
        $stats = ['found' => 0, 'new' => 0, 'duplicate' => 0, 'rejected' => 0];

        foreach ($candidates as $candidate) {
            $stats['found']++;

            if (trim($candidate->businessName) === '') {
                $stats['rejected']++;
                continue;
            }

            if (!$this->classifier->isListing($candidate->businessName, $candidate->description)) {
                $stats['rejected']++;
                continue;
            }

            $isDuplicate = Contractor::where('contractor_source_id', $source->id)
                ->where('external_id', $candidate->externalId)
                ->exists();

            if ($isDuplicate) {
                $stats['duplicate']++;
                continue;
            }

            Contractor::create([
                'contractor_source_id' => $source->id,
                'external_id' => $candidate->externalId,
                'business_name' => $candidate->businessName,
                'service_category' => $candidate->serviceCategory,
                'location' => $candidate->location,
                'phone' => $candidate->phone,
                'email' => $candidate->email,
                'website' => $candidate->website,
                'description' => $candidate->description,
                'claim_status' => 'unclaimed',
                'status' => 'active',
            ]);

            $stats['new']++;
        }

        return $stats;
    }
}

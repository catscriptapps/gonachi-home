<?php
// /src/Service/ContractorIngestionService.php

declare(strict_types=1);

namespace Src\Service;

use App\Models\Contractor;
use App\Models\ContractorSource;
use Src\Service\ContractorSources\ContractorCandidate;

/**
 * Takes raw candidates from a ContractorSourceConnector, dedups against
 * existing contractors, and stores new ones. Unlike LeadIngestionService,
 * there is no classification/review step — contractor_discovery.pdf's
 * Phase 1 spec calls for profiles to publish immediately ("ensures the
 * platform appears active and searchable from day one"), matching how the
 * existing admin-curated seed contractors already work (status=active,
 * claim_status=unclaimed on creation).
 */
final class ContractorIngestionService
{
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

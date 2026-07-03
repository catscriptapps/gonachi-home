<?php
// /src/Service/LeadIngestionService.php

declare(strict_types=1);

namespace Src\Service;

use App\Models\Lead;
use App\Models\LeadCategory;
use App\Models\LeadSource;
use App\Models\Location;
use Carbon\Carbon;
use Src\Service\LeadSources\LeadCandidate;

/**
 * Takes raw candidates from a connector, dedups against existing leads,
 * classifies intent, resolves category/location, and stores new leads.
 */
final class LeadIngestionService
{
    private LeadIntentClassifier $classifier;

    public function __construct(?LeadIntentClassifier $classifier = null)
    {
        $this->classifier = $classifier ?? new LeadIntentClassifier(
            Location::pluck('name')->all()
        );
    }

    /**
     * @param iterable<LeadCandidate> $candidates
     * @return array{found: int, new: int, duplicate: int, rejected: int}
     */
    public function ingest(LeadSource $source, iterable $candidates): array
    {
        $stats = ['found' => 0, 'new' => 0, 'duplicate' => 0, 'rejected' => 0];

        foreach ($candidates as $candidate) {
            $stats['found']++;

            $isDuplicate = Lead::where('lead_source_id', $source->id)
                ->where('external_id', $candidate->externalId)
                ->exists();

            if ($isDuplicate) {
                $stats['duplicate']++;
                continue;
            }

            $classified = $this->classifier->classify($candidate->text);

            if ($classified === null) {
                $stats['rejected']++;
                continue;
            }

            $location = $classified->locationRaw
                ? Location::where('name', $classified->locationRaw)->first()
                : null;

            $category = $this->resolveCategory($classified->requestType, $classified->propertyType);

            Lead::create([
                'lead_source_id' => $source->id,
                'external_id' => $candidate->externalId,
                'source_url' => $candidate->url,
                'raw_text' => $candidate->text,
                'request_type' => $classified->requestType,
                'property_type' => $classified->propertyType,
                'bedrooms' => $classified->bedrooms,
                'location_id' => $location?->id,
                'location_raw' => $classified->locationRaw,
                'budget_min' => $classified->budgetMin,
                'budget_max' => $classified->budgetMax,
                'intent_level' => $classified->intentLevel,
                'contact_info_raw' => $candidate->contactInfoRaw,
                'status' => 'pending_review',
                'category_id' => $category?->id,
                'posted_at' => $candidate->postedAt,
                'scraped_at' => Carbon::now(),
            ]);

            $stats['new']++;
        }

        return $stats;
    }

    /**
     * Matches an existing category rather than auto-creating one, so the
     * category list (and the SEO pages built from it) stays curated instead
     * of sprawling from scraped-text noise.
     */
    private function resolveCategory(string $requestType, ?string $propertyType): ?LeadCategory
    {
        $query = LeadCategory::where('request_type', $requestType);

        if ($propertyType) {
            $specific = (clone $query)->where('property_type', $propertyType)->first();
            if ($specific) {
                return $specific;
            }
        }

        return $query->whereNull('property_type')->first();
    }
}

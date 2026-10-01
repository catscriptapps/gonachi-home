<?php
// /src/Service/LeadIngestionService.php

declare(strict_types=1);

namespace Src\Service;

use App\Models\Lead;
use App\Models\LeadCategory;
use App\Models\LeadSource;
use App\Models\Location;
use Carbon\Carbon;
use Src\Controller\LeadsController;
use Src\Service\LeadSources\LeadCandidate;
use Src\Service\LeadSources\RequiresCompleteListingInfo;

/**
 * Takes raw candidates from a connector, dedups against existing leads,
 * classifies intent, resolves category/location, and stores new leads.
 */
final class LeadIngestionService
{
    private LeadIntentClassifier $classifier;

    public function __construct(?LeadIntentClassifier $classifier = null)
    {
        $this->classifier = $classifier ?? new LeadIntentClassifier(self::locationIndex());

        // Lazily adds rel_leads.phone/email/slug to an already-deployed
        // database (see Lead::ensureLeadColumns()) before the first
        // create() below needs to write to them.
        Lead::ensureLeadColumns();
    }

    /**
     * Every location name paired with its depth in the tree (0 = country,
     * 1 = state, 2 = area) — see LeadIntentClassifier::detectLocation(),
     * which needs depth to prefer a specific area match over a country/state
     * one that happens to also appear in the same text. Shared with
     * scripts/backfill-lead-locations.php so a standalone re-run uses the
     * exact same index this class builds for live ingestion.
     *
     * @return array<int, array{name: string, depth: int}>
     */
    public static function locationIndex(): array
    {
        $locations = Location::all(['id', 'name', 'parent_id'])->keyBy('id');

        return $locations->map(function (Location $location) use ($locations) {
            $depth = 0;
            $current = $location;

            while ($current->parent_id !== null && $locations->has($current->parent_id)) {
                $depth++;
                $current = $locations->get($current->parent_id);
            }

            return ['name' => $location->name, 'depth' => $depth];
        })->values()->all();
    }

    /**
     * @param iterable<LeadCandidate> $candidates
     * @return array{found: int, new: int, duplicate: int, rejected: int}
     */
    public function ingest(LeadSource $source, iterable $candidates): array
    {
        $stats = ['found' => 0, 'new' => 0, 'duplicate' => 0, 'rejected' => 0];

        // Web-search connectors (Serper/Google CSE) crawl arbitrary public
        // pages, where blog posts and news articles turn up alongside real
        // listings/requests — held to a stricter bar (a detected budget is
        // mandatory) than a single dedicated forum board's own threads. See
        // RequiresCompleteListingInfo's own doc comment for the full reasoning.
        $requireBudget = is_a($source->connector_class, RequiresCompleteListingInfo::class, true);

        foreach ($candidates as $candidate) {
            $stats['found']++;

            $isDuplicate = Lead::where('lead_source_id', $source->id)
                ->where('external_id', $candidate->externalId)
                ->exists();

            if ($isDuplicate) {
                $stats['duplicate']++;
                continue;
            }

            $classified = $this->classifier->classify($candidate->text, $requireBudget);

            if ($classified === null) {
                $stats['rejected']++;
                continue;
            }

            $location = $classified->locationRaw
                ? Location::where('name', $classified->locationRaw)->first()
                : null;

            $category = $this->resolveCategory($classified->requestType, $classified->propertyType);

            // Contact info can appear in either the source's own dedicated
            // field (contactInfoRaw) or just inline in the listing text
            // itself — check both. See ContactInfoParser's docblock.
            $contactText = trim(($candidate->contactInfoRaw ?? '') . ' ' . $candidate->text);
            $phone = ContactInfoParser::extractPhone($contactText);
            $email = ContactInfoParser::extractEmail($contactText);

            $lead = Lead::create([
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
                'phone' => $phone,
                'email' => $email,
                'status' => 'pending_review',
                'category_id' => $category?->id,
                'posted_at' => $candidate->postedAt,
                'scraped_at' => Carbon::now(),
            ]);

            // SEO slug — see LeadsController::buildUniqueSlug()'s docblock
            // for why this needs $lead->location loaded (lazy-loads here via
            // $lead->location_id, one extra query, acceptable for a
            // background ingestion run).
            $slug = LeadsController::buildUniqueSlug($lead);
            if ($slug) {
                $lead->update(['slug' => $slug]);
            }

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

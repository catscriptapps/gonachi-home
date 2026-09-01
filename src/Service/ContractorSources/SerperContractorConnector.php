<?php
// /src/Service/ContractorSources/SerperContractorConnector.php

declare(strict_types=1);

namespace Src\Service\ContractorSources;

use GuzzleHttp\Client;

/**
 * Discovery connector for contractor_discovery.pdf's Phase 1 "Data
 * Collection": runs a set of "{category} in {region}" queries through
 * Serper.dev (see Src\Service\LeadSources\SerperConnector — same provider,
 * same reasoning) and yields the matching public business listings.
 *
 * Requires SERPER_API_KEY in .env (configurable via config['api_key_env']).
 * Until it's present this connector yields nothing rather than failing the
 * run — safe to leave the cde_contractor_sources row active before a key
 * exists.
 *
 * config['queries'] is an array of {category, location, query} triples —
 * category/location are stored explicitly rather than parsed back out of
 * the query text, since we already know them (we wrote the query).
 */
final class SerperContractorConnector implements ContractorSourceConnector
{
    private const ENDPOINT = 'https://google.serper.dev/search';

    public function fetchCandidates(array $config): iterable
    {
        $apiKeyEnv = $config['api_key_env'] ?? 'SERPER_API_KEY';
        $apiKey = $_ENV[$apiKeyEnv] ?? (getenv($apiKeyEnv) ?: null);

        if (!$apiKey) {
            return;
        }

        $queries = $config['queries'] ?? [];
        if (!$queries) {
            return;
        }

        $client = new Client(['timeout' => 20]);

        foreach ($queries as $entry) {
            $category = $entry['category'] ?? null;
            $location = $entry['location'] ?? null;
            $searchText = $entry['query'] ?? null;

            if (!$category || !$location || !$searchText) {
                continue;
            }

            $response = $client->post(self::ENDPOINT, [
                'headers' => [
                    'X-API-KEY' => $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => ['q' => $searchText, 'num' => 10],
            ]);

            $data = json_decode((string) $response->getBody(), true) ?? [];

            foreach ($data['organic'] ?? [] as $item) {
                $link = $item['link'] ?? null;
                $title = trim($item['title'] ?? '');
                $snippet = trim($item['snippet'] ?? '');

                if (!$link || $title === '') {
                    continue;
                }

                yield new ContractorCandidate(
                    externalId: md5($link),
                    businessName: $this->cleanBusinessName($title),
                    serviceCategory: $category,
                    location: $location,
                    website: $link,
                    description: $snippet !== '' ? $snippet : null,
                    phone: $this->extractPhone($snippet),
                );
            }
        }
    }

    /**
     * Search result titles are often "Business Name | Site Name" or
     * "Business Name - Facebook" — take the first segment when splitting
     * looks safe, otherwise keep the full title rather than risk chopping
     * a real business name that happens to contain a dash.
     */
    private function cleanBusinessName(string $title): string
    {
        foreach ([' | ', ' – ', ' — ', ' - '] as $separator) {
            if (str_contains($title, $separator)) {
                $first = trim(strstr($title, $separator, true));
                if (mb_strlen($first) >= 3) {
                    return $first;
                }
            }
        }

        return $title;
    }

    /**
     * Best-effort Nigerian phone number extraction from a search snippet.
     * Matches common local (0801...) and international (+234801...) forms.
     */
    private function extractPhone(string $text): ?string
    {
        if (preg_match('/(\+?234[\s-]?\d{3}[\s-]?\d{3}[\s-]?\d{4}|0\d{3}[\s-]?\d{3}[\s-]?\d{4})/', $text, $matches)) {
            return preg_replace('/[\s-]/', '', $matches[0]);
        }

        return null;
    }
}

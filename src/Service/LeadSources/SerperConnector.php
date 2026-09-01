<?php
// /src/Service/LeadSources/SerperConnector.php

declare(strict_types=1);

namespace Src\Service\LeadSources;

use GuzzleHttp\Client;

/**
 * Discovery connector: runs a set of intent-phrase queries through
 * Serper.dev (a commercial Google-search-results API) and yields the
 * matching public pages. Replaces GoogleCseConnector, which Google closed
 * to new Cloud projects in 2025 and is fully discontinuing on 2027-01-01 —
 * see https://developers.google.com/custom-search/v1/overview. Serper has
 * the same shape (run a query, get back result URLs + snippets), so this
 * mirrors GoogleCseConnector's structure closely.
 *
 * Requires SERPER_API_KEY to be set in .env (configurable via
 * config['api_key_env']). Until it's present this connector yields nothing
 * rather than failing the run — it's safe to leave the lead_sources row
 * active before a key exists.
 */
final class SerperConnector implements LeadSourceConnector
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

        foreach ($queries as $query) {
            $response = $client->post(self::ENDPOINT, [
                'headers' => [
                    'X-API-KEY' => $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => ['q' => $query, 'num' => 10],
            ]);

            $data = json_decode((string) $response->getBody(), true) ?? [];

            foreach ($data['organic'] ?? [] as $item) {
                $link = $item['link'] ?? null;
                $title = trim($item['title'] ?? '');
                $snippet = trim($item['snippet'] ?? '');

                if (!$link || $title === '') {
                    continue;
                }

                yield new LeadCandidate(
                    externalId: md5($link),
                    url: $link,
                    text: trim($title . '. ' . $snippet),
                );
            }
        }
    }
}

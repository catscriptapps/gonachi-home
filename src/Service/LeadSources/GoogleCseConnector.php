<?php
// /src/Service/LeadSources/GoogleCseConnector.php

declare(strict_types=1);

namespace Src\Service\LeadSources;

use GuzzleHttp\Client;

/**
 * Discovery connector: runs a set of intent-phrase queries through Google's
 * official Custom Search JSON API and yields the matching public pages.
 * This is the most compliant/scalable source since it uses an official API
 * rather than scraping a platform directly.
 *
 * Requires GOOGLE_CSE_API_KEY and GOOGLE_CSE_ID to be set in .env (names are
 * configurable via config['api_key_env'] / config['cse_id_env']). Until both
 * are present this connector yields nothing rather than failing the run —
 * it's safe to leave the lead_sources row active before credentials exist.
 */
final class GoogleCseConnector implements LeadSourceConnector
{
    private const ENDPOINT = 'https://www.googleapis.com/customsearch/v1';

    public function fetchCandidates(array $config): iterable
    {
        $apiKeyEnv = $config['api_key_env'] ?? 'GOOGLE_CSE_API_KEY';
        $cseIdEnv = $config['cse_id_env'] ?? 'GOOGLE_CSE_ID';

        $apiKey = $_ENV[$apiKeyEnv] ?? (getenv($apiKeyEnv) ?: null);
        $cseId = $_ENV[$cseIdEnv] ?? (getenv($cseIdEnv) ?: null);

        if (!$apiKey || !$cseId) {
            return;
        }

        $queries = $config['queries'] ?? [];
        if (!$queries) {
            return;
        }

        $client = new Client(['timeout' => 20]);

        foreach ($queries as $query) {
            $response = $client->get(self::ENDPOINT, [
                'query' => [
                    'key' => $apiKey,
                    'cx' => $cseId,
                    'q' => $query,
                    'num' => 10,
                ],
            ]);

            $data = json_decode((string) $response->getBody(), true) ?? [];

            foreach ($data['items'] ?? [] as $item) {
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

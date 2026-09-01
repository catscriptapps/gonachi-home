<?php
// /src/Service/LeadSources/NairalandPropertiesConnector.php

declare(strict_types=1);

namespace Src\Service\LeadSources;

use GuzzleHttp\Client;

/**
 * Reads thread titles from Nairaland's public "Properties" board index.
 *
 * Nairaland sits behind Cloudflare's JS challenge (confirmed via the
 * Cf-Mitigated: challenge response header — not a simple bot-detection
 * header check, an actual JS proof-of-work page), which no plain HTTP
 * client can solve. Requests are routed through ScraperAPI
 * (https://api.scraperapi.com) with ultra_premium=true — its tier
 * documented for "the most aggressively protected sites" — which runs a
 * real browser on their infrastructure and hands back the resolved HTML.
 *
 * Requires SCRAPERAPI_KEY in .env (configurable via
 * config['scraperapi_key_env']). Until it's present this connector yields
 * nothing rather than failing the run — same pattern as the other
 * connectors before their credentials existed.
 *
 * Only exposes thread titles (not full post bodies) — classification runs
 * on the title text alone, so coverage/hit-rate is intentionally limited.
 * Most threads on this board are construction/materials ads rather than
 * buyer/seller intent posts.
 */
final class NairalandPropertiesConnector implements LeadSourceConnector
{
    private const SCRAPERAPI_ENDPOINT = 'https://api.scraperapi.com';

    public function fetchCandidates(array $config): iterable
    {
        $boardUrl = $config['board_url'] ?? 'https://www.nairaland.com/properties';

        $apiKeyEnv = $config['scraperapi_key_env'] ?? 'SCRAPERAPI_KEY';
        $apiKey = $_ENV[$apiKeyEnv] ?? (getenv($apiKeyEnv) ?: null);

        if (!$apiKey) {
            return;
        }

        // ScraperAPI's own guidance: allow up to ~70s for difficult,
        // browser-rendered domains rather than timing out prematurely.
        $client = new Client(['timeout' => 75]);

        $html = (string) $client->get(self::SCRAPERAPI_ENDPOINT, [
            'query' => [
                'api_key' => $apiKey,
                'url' => $boardUrl,
                'ultra_premium' => 'true',
            ],
        ])->getBody();

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        // Force UTF-8 interpretation without mangling multi-byte characters.
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $rows = $xpath->query("//td[starts-with(@id, 'top')]");

        foreach ($rows as $row) {
            $threadId = substr($row->getAttribute('id'), 3);
            if ($threadId === '' || !ctype_digit($threadId)) {
                continue;
            }

            // The thread title link is always the first anchor in the row
            // whose href contains "/{threadId}/" — pagination links ("...  /2", "/3")
            // point at the same thread but appear later in the row.
            $titleNode = $xpath->query(".//a[contains(@href, '/{$threadId}/')]", $row)->item(0);
            if (!$titleNode) {
                continue;
            }

            $title = trim($titleNode->textContent);
            if ($title === '') {
                continue;
            }

            $href = $titleNode->getAttribute('href');
            $url = str_starts_with($href, 'http') ? $href : 'https://www.nairaland.com' . $href;

            yield new LeadCandidate(
                externalId: $threadId,
                url: $url,
                text: $title,
            );
        }
    }
}

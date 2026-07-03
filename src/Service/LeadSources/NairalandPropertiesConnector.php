<?php
// /src/Service/LeadSources/NairalandPropertiesConnector.php

declare(strict_types=1);

namespace Src\Service\LeadSources;

use GuzzleHttp\Client;

/**
 * Reads thread titles from Nairaland's public "Properties" board index.
 * No login/API key required; robots.txt for nairaland.com permits crawling
 * this path as of the time this connector was written.
 *
 * Only exposes thread titles (not full post bodies) — classification runs
 * on the title text alone, so coverage/hit-rate is intentionally limited.
 * Most threads on this board are construction/materials ads rather than
 * buyer/seller intent posts.
 */
final class NairalandPropertiesConnector implements LeadSourceConnector
{
    public function fetchCandidates(array $config): iterable
    {
        $boardUrl = $config['board_url'] ?? 'https://www.nairaland.com/properties';

        $client = new Client([
            'timeout' => 20,
            'headers' => [
                'User-Agent' => 'GonachiLeadBot/0.1 (+https://gonachi.example/bot-info)',
            ],
        ]);

        $html = (string) $client->get($boardUrl)->getBody();

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

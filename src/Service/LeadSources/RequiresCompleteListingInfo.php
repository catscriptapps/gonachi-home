<?php
// /src/Service/LeadSources/RequiresCompleteListingInfo.php

declare(strict_types=1);

namespace Src\Service\LeadSources;

/**
 * Marker interface: tags connectors that crawl arbitrary public web pages
 * (general search results) rather than a single platform's own listings/
 * forum threads. That arbitrary-content shape is exactly what lets blog
 * posts, news articles, and market-guide pages slip into results alongside
 * actual buyer/seller/renter requests — so LeadIngestionService holds
 * connectors tagged with this to a stricter bar (a detected budget is
 * required, on top of the usual intent-phrase + property-noun checks).
 *
 * NairalandPropertiesConnector deliberately does NOT implement this: it
 * only ever sees thread titles from one dedicated forum board, which are
 * inherently individual posts (never blog/news content) but almost never
 * state a price in the title alone — requiring budget there would silence
 * that source almost entirely.
 */
interface RequiresCompleteListingInfo
{
}

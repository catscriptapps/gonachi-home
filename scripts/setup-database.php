<?php
// /scripts/setup-database.php
//
// CLI entry point for setting up gonachi_home_db from scratch: the shared
// `users` table plus every project's tables. Currently that's just the
// real-estate-leads project (rel_ prefixed tables); as more projects land
// under gonachi-home, add their reset calls here alongside it.
//
// Run: php scripts/setup-database.php

declare(strict_types=1);

require_once __DIR__ . '/../server/bootstrap.php';

$messages = [];

// --------------------------------------------------
// Shared: tables every project/page in gonachi-home still depends on
// --------------------------------------------------
require_once __DIR__ . '/reset/countries.php';
$messages = array_merge($messages, resetCountriesTable());

require_once __DIR__ . '/reset/regions.php';
$messages = array_merge($messages, resetRegionsTable());

require_once __DIR__ . '/reset/users.php';
$messages = array_merge($messages, resetUsersTable());

require_once __DIR__ . '/reset/recent-activities.php';
$messages = array_merge($messages, resetRecentActivitiesTable());

require_once __DIR__ . '/reset/faqs.php';
$messages = array_merge($messages, resetFaqsTable());

require_once __DIR__ . '/reset/password-resets.php';
$messages = array_merge($messages, resetPasswordResetsTable());

require_once __DIR__ . '/reset/user-verifications.php';
$messages = array_merge($messages, resetUserVerificationsTable());

require_once __DIR__ . '/reset/messages.php';
$messages = array_merge($messages, resetMessagesTable());

// --------------------------------------------------
// Project: real-estate-leads (rel_ prefixed tables)
// --------------------------------------------------
require_once __DIR__ . '/reset/rel-locations.php';
$messages = array_merge($messages, resetRelLocationsTable());

require_once __DIR__ . '/reset/rel-lead-categories.php';
$messages = array_merge($messages, resetRelLeadCategoriesTable());

require_once __DIR__ . '/reset/rel-lead-sources.php';
$messages = array_merge($messages, resetRelLeadSourcesTable());

require_once __DIR__ . '/reset/rel-leads.php';
$messages = array_merge($messages, resetRelLeadsTable());

require_once __DIR__ . '/reset/rel-lead-extraction-runs.php';
$messages = array_merge($messages, resetRelLeadExtractionRunsTable());

require_once __DIR__ . '/reset/rel-seed.php';
$messages = array_merge($messages, seedRelLeadsBaselineData());

foreach ($messages as $message) {
    echo $message . PHP_EOL;
}

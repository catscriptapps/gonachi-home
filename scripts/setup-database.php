<?php
// /scripts/setup-database.php
//
// CLI entry point for setting up gonachi_home_db from scratch: the shared
// `users` table plus every project's tables — real-estate-leads (rel_
// prefixed), landlord-tenant-validation (ltv_ prefixed), and
// contractor-discovery (cde_ prefixed) so far; as more projects land under
// gonachi-home, add their reset calls here alongside it.
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

require_once __DIR__ . '/reset/rel-credit-accounts.php';
$messages = array_merge($messages, resetRelCreditAccountsTable());

require_once __DIR__ . '/reset/rel-credit-transactions.php';
$messages = array_merge($messages, resetRelCreditTransactionsTable());

require_once __DIR__ . '/reset/rel-lead-unlocks.php';
$messages = array_merge($messages, resetRelLeadUnlocksTable());

require_once __DIR__ . '/reset/rel-seed.php';
$messages = array_merge($messages, seedRelLeadsBaselineData());

// --------------------------------------------------
// Project: landlord-tenant-validation (ltv_ prefixed tables)
// --------------------------------------------------
require_once __DIR__ . '/reset/ltv-landlords.php';
$messages = array_merge($messages, resetLtvLandlordsTable());

require_once __DIR__ . '/reset/ltv-properties.php';
$messages = array_merge($messages, resetLtvPropertiesTable());

require_once __DIR__ . '/reset/ltv-reports.php';
$messages = array_merge($messages, resetLtvReportsTable());

require_once __DIR__ . '/reset/ltv-report-photos.php';
$messages = array_merge($messages, resetLtvReportPhotosTable());

require_once __DIR__ . '/reset/ltv-seed.php';
$messages = array_merge($messages, seedLtvBaselineData());

// --------------------------------------------------
// Project: contractor-discovery (cde_ prefixed tables)
// --------------------------------------------------
require_once __DIR__ . '/reset/cde-job-requests.php';
$messages = array_merge($messages, resetCdeJobRequestsTable());

require_once __DIR__ . '/reset/cde-job-request-photos.php';
$messages = array_merge($messages, resetCdeJobRequestPhotosTable());

require_once __DIR__ . '/reset/cde-contractors.php';
$messages = array_merge($messages, resetCdeContractorsTable());

require_once __DIR__ . '/reset/cde-contractor-claims.php';
$messages = array_merge($messages, resetCdeContractorClaimsTable());

require_once __DIR__ . '/reset/cde-seed.php';
$messages = array_merge($messages, seedCdeBaselineData());

foreach ($messages as $message) {
    echo $message . PHP_EOL;
}

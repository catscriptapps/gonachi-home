<?php
// /scripts/setup-database.php
//
// CLI entry point for setting up gonachi_home_db from scratch: the shared
// `users` table plus every project's tables — real-estate-leads (rel_
// prefixed), landlord-tenant-validation (ltv_ prefixed), contractor-discovery
// (cde_ prefixed), and real-estate-world (rew_ prefixed) so far; as more
// projects land under gonachi-home, add their reset calls here alongside it.
//
// Run: php scripts/setup-database.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;

require_once __DIR__ . '/../server/bootstrap.php';
require_once __DIR__ . '/reset/preserve-scraped-data.php';

$messages = [];

// Snapshot real, cron-discovered leads/contractors before anything is
// dropped — see reset/preserve-scraped-data.php for why this can't just be
// "skip dropping those tables" (their foreign keys point at parent tables
// that DO get reseeded with new IDs).
$scrapedDataBackup = backupScrapedData();

// Each reset*Table() function below drops and recreates one table in
// isolation, without regard for cross-table FK ordering (e.g. rew_posts vs.
// its own rew_post_comments/rew_post_likes children) — so a plain DROP TABLE
// on a parent fails once a child table from a previous run still holds a
// live FK reference to it. Disabling FK checks for the whole reset sidesteps
// ordering entirely, same as server/api/reset.php's UI-triggered reset does.
Capsule::schema()->disableForeignKeyConstraints();

// --------------------------------------------------
// Shared: tables every project/page in gonachi-home still depends on
// --------------------------------------------------
require_once __DIR__ . '/reset/countries.php';
$messages = array_merge($messages, resetCountriesTable());

require_once __DIR__ . '/reset/regions.php';
$messages = array_merge($messages, resetRegionsTable());

require_once __DIR__ . '/reset/users.php';
$messages = array_merge($messages, resetUsersTable());

// countries.php/regions.php/users.php each locally disable-then-re-enable
// FK checks around their own drop+create, which clobbers the disable set
// above the moment resetUsersTable() returns — re-assert it here so every
// reset*Table() call after this point (which all assume FK checks are still
// off) actually gets that.
Capsule::schema()->disableForeignKeyConstraints();

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

require_once __DIR__ . '/reset/chat-conversations.php';
$messages = array_merge($messages, resetChatConversationsTable());

require_once __DIR__ . '/reset/chat-messages.php';
$messages = array_merge($messages, resetChatMessagesTable());

require_once __DIR__ . '/reset/chat-ai-settings.php';
$messages = array_merge($messages, resetChatAiSettingsTable());

require_once __DIR__ . '/reset/system-settings.php';
$messages = array_merge($messages, resetSystemSettingsTable());

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

require_once __DIR__ . '/reset/rel-saved-searches.php';
$messages = array_merge($messages, resetRelSavedSearchesTable());

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

require_once __DIR__ . '/reset/ltv-rental-listings.php';
$messages = array_merge($messages, resetLtvRentalListingsTable());

require_once __DIR__ . '/reset/ltv-rental-listing-photos.php';
$messages = array_merge($messages, resetLtvRentalListingPhotosTable());

require_once __DIR__ . '/reset/ltv-seed.php';
$messages = array_merge($messages, seedLtvBaselineData());

// --------------------------------------------------
// Project: contractor-discovery (cde_ prefixed tables)
// --------------------------------------------------
require_once __DIR__ . '/reset/cde-job-requests.php';
$messages = array_merge($messages, resetCdeJobRequestsTable());

require_once __DIR__ . '/reset/cde-job-request-photos.php';
$messages = array_merge($messages, resetCdeJobRequestPhotosTable());

require_once __DIR__ . '/reset/cde-contractor-sources.php';
$messages = array_merge($messages, resetCdeContractorSourcesTable());

require_once __DIR__ . '/reset/cde-contractors.php';
$messages = array_merge($messages, resetCdeContractorsTable());

require_once __DIR__ . '/reset/cde-contractor-discovery-runs.php';
$messages = array_merge($messages, resetCdeContractorDiscoveryRunsTable());

require_once __DIR__ . '/reset/cde-contractor-claims.php';
$messages = array_merge($messages, resetCdeContractorClaimsTable());

require_once __DIR__ . '/reset/cde-seed.php';
$messages = array_merge($messages, seedCdeBaselineData());

// --------------------------------------------------
// Project: real-estate-world (rew_ prefixed tables)
// --------------------------------------------------
require_once __DIR__ . '/reset/rew-follows.php';
$messages = array_merge($messages, resetRewFollowsTable());

require_once __DIR__ . '/reset/rew-posts.php';
$messages = array_merge($messages, resetRewPostsTable());

require_once __DIR__ . '/reset/rew-post-comments.php';
$messages = array_merge($messages, resetRewPostCommentsTable());

require_once __DIR__ . '/reset/rew-post-likes.php';
$messages = array_merge($messages, resetRewPostLikesTable());

require_once __DIR__ . '/reset/rew-advert-ctas.php';
$messages = array_merge($messages, resetRewAdvertCtasTable());

require_once __DIR__ . '/reset/rew-advert-packages.php';
$messages = array_merge($messages, resetRewAdvertPackagesTable());

require_once __DIR__ . '/reset/rew-adverts.php';
$messages = array_merge($messages, resetRewAdvertsTable());

require_once __DIR__ . '/reset/rew-advert-pics.php';
$messages = array_merge($messages, resetRewAdvertPicsTable());

require_once __DIR__ . '/reset/rew-contractor-types.php';
$messages = array_merge($messages, resetRewContractorTypesTable());

require_once __DIR__ . '/reset/rew-skilled-trades.php';
$messages = array_merge($messages, resetRewSkilledTradesTable());

require_once __DIR__ . '/reset/rew-unit-types.php';
$messages = array_merge($messages, resetRewUnitTypesTable());

require_once __DIR__ . '/reset/rew-house-types.php';
$messages = array_merge($messages, resetRewHouseTypesTable());

require_once __DIR__ . '/reset/rew-quotation-types.php';
$messages = array_merge($messages, resetRewQuotationTypesTable());

require_once __DIR__ . '/reset/rew-quotation-destinations.php';
$messages = array_merge($messages, resetRewQuotationDestinationsTable());

require_once __DIR__ . '/reset/rew-quotations.php';
$messages = array_merge($messages, resetRewQuotationsTable());

require_once __DIR__ . '/reset/rew-quotation-pics.php';
$messages = array_merge($messages, resetRewQuotationPicsTable());

require_once __DIR__ . '/reset/rew-quotation-responses.php';
$messages = array_merge($messages, resetRewQuotationResponsesTable());

require_once __DIR__ . '/reset/rew-stakeholder-types.php';
$messages = array_merge($messages, resetRewStakeholderTypesTable());

require_once __DIR__ . '/reset/rew-mentors.php';
$messages = array_merge($messages, resetRewMentorsTable());

require_once __DIR__ . '/reset/rew-mentor-requests.php';
$messages = array_merge($messages, resetRewMentorRequestsTable());

// Re-attach the leads/contractors snapshotted at the top, now that their
// parent tables (sources, categories, locations) have fresh IDs to resolve against.
$messages = array_merge($messages, restoreScrapedData($scrapedDataBackup));

Capsule::schema()->enableForeignKeyConstraints();

foreach ($messages as $message) {
    echo $message . PHP_EOL;
}

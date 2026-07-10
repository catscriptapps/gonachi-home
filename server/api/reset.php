<?php
// /server/api/reset.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;

header('Content-Type: application/json');

$messages = [];

/**
 * 1. PRE-FLIGHT CHECKS & DISABLE CONSTRAINTS
 */
Capsule::schema()->disableForeignKeyConstraints();

/**
 * 2. AGGRESSIVE DROP PHASE (Children first, then Parents)
 * This ensures we don't have "ghost" tables blocking the reset scripts.
 */
$tablesToDrop = [
    // Authentication & Core (shared across every project)
    'password_resets',
    'user_verifications',
    'messages',
    'recent_activities',
    'users',

    // Static/Lookup Tables
    'regions',
    'countries',
    'faqs',

    // Project: real-estate-leads
    'rel_lead_unlocks',
    'rel_credit_transactions',
    'rel_credit_accounts',
    'rel_lead_extraction_runs',
    'rel_leads',
    'rel_lead_sources',
    'rel_lead_categories',
    'rel_locations',

    // Project: landlord-tenant-validation
    'ltv_report_photos',
    'ltv_reports',
    'ltv_properties',
    'ltv_landlords',

    // Project: contractor-discovery
    'cde_job_request_photos',
    'cde_job_requests',
];

foreach ($tablesToDrop as $table) {
    Capsule::schema()->dropIfExists($table);
}

$messages[] = "database cleared: All dependent and parent tables dropped.";

/**
 * 3. CREATION PHASE - SHARED (used by every project under gonachi-home)
 */
require_once __DIR__ . '/../../scripts/reset/countries.php';
$messages = array_merge($messages, resetCountriesTable());

require_once __DIR__ . '/../../scripts/reset/regions.php';
$messages = array_merge($messages, resetRegionsTable());

require_once __DIR__ . '/../../scripts/reset/users.php';
$messages = array_merge($messages, resetUsersTable());

require_once __DIR__ . '/../../scripts/reset/recent-activities.php';
$messages = array_merge($messages, resetRecentActivitiesTable());

require_once __DIR__ . '/../../scripts/reset/faqs.php';
$messages = array_merge($messages, resetFaqsTable());

require_once __DIR__ . '/../../scripts/reset/password-resets.php';
$messages = array_merge($messages, resetPasswordResetsTable());

require_once __DIR__ . '/../../scripts/reset/user-verifications.php';
$messages = array_merge($messages, resetUserVerificationsTable());

require_once __DIR__ . '/../../scripts/reset/messages.php';
$messages = array_merge($messages, resetMessagesTable());

/**
 * 4. CREATION PHASE - PROJECT: real-estate-leads (rel_ prefixed tables)
 */
require_once __DIR__ . '/../../scripts/reset/rel-locations.php';
$messages = array_merge($messages, resetRelLocationsTable());

require_once __DIR__ . '/../../scripts/reset/rel-lead-categories.php';
$messages = array_merge($messages, resetRelLeadCategoriesTable());

require_once __DIR__ . '/../../scripts/reset/rel-lead-sources.php';
$messages = array_merge($messages, resetRelLeadSourcesTable());

require_once __DIR__ . '/../../scripts/reset/rel-leads.php';
$messages = array_merge($messages, resetRelLeadsTable());

require_once __DIR__ . '/../../scripts/reset/rel-lead-extraction-runs.php';
$messages = array_merge($messages, resetRelLeadExtractionRunsTable());

require_once __DIR__ . '/../../scripts/reset/rel-credit-accounts.php';
$messages = array_merge($messages, resetRelCreditAccountsTable());

require_once __DIR__ . '/../../scripts/reset/rel-credit-transactions.php';
$messages = array_merge($messages, resetRelCreditTransactionsTable());

require_once __DIR__ . '/../../scripts/reset/rel-lead-unlocks.php';
$messages = array_merge($messages, resetRelLeadUnlocksTable());

require_once __DIR__ . '/../../scripts/reset/rel-seed.php';
$messages = array_merge($messages, seedRelLeadsBaselineData());

/**
 * 4b. CREATION PHASE - PROJECT: landlord-tenant-validation (ltv_ prefixed tables)
 */
require_once __DIR__ . '/../../scripts/reset/ltv-landlords.php';
$messages = array_merge($messages, resetLtvLandlordsTable());

require_once __DIR__ . '/../../scripts/reset/ltv-properties.php';
$messages = array_merge($messages, resetLtvPropertiesTable());

require_once __DIR__ . '/../../scripts/reset/ltv-reports.php';
$messages = array_merge($messages, resetLtvReportsTable());

require_once __DIR__ . '/../../scripts/reset/ltv-report-photos.php';
$messages = array_merge($messages, resetLtvReportPhotosTable());

require_once __DIR__ . '/../../scripts/reset/ltv-seed.php';
$messages = array_merge($messages, seedLtvBaselineData());

/**
 * 4c. CREATION PHASE - PROJECT: contractor-discovery (cde_ prefixed tables)
 */
require_once __DIR__ . '/../../scripts/reset/cde-job-requests.php';
$messages = array_merge($messages, resetCdeJobRequestsTable());

require_once __DIR__ . '/../../scripts/reset/cde-job-request-photos.php';
$messages = array_merge($messages, resetCdeJobRequestPhotosTable());

require_once __DIR__ . '/../../scripts/reset/cde-seed.php';
$messages = array_merge($messages, seedCdeBaselineData());


/**
 * 5. FINALIZE
 */
Capsule::schema()->enableForeignKeyConstraints();

$deleteAllPicturesAndPDFs = true;

// --- DELETE specific transient application upload content only ---
if ($deleteAllPicturesAndPDFs) {
    $targetFolders = [
        __DIR__ . '/../../public/images/uploads',
        __DIR__ . '/../../public/videos',
    ];

    foreach ($targetFolders as $folder) {
        $resolved = realpath($folder);

        // Skip if the folder doesn't exist to avoid errors
        if ($resolved === false || !is_dir($resolved)) {
            $messages[] = "Skipping: folder not found: $folder";
            continue;
        }

        $messages[] = "cleaning contents of: $resolved";

        $entries = scandir($resolved);
        if ($entries === false) continue;

        $deletedCount = 0;
        foreach ($entries as $entry) {
            // NEVER delete current, parent, or .gitkeep (keeps the folder structure in Git)
            if (in_array($entry, ['.', '..', '.gitkeep'])) continue;

            $path = $resolved . DIRECTORY_SEPARATOR . $entry;

            if (is_dir($path)) {
                // If it's a subfolder, delete it and its contents
                if (rrmdir($path)) $deletedCount++;
            } else {
                // If it's a file, delete it
                if (unlink($path)) $deletedCount++;
            }
        }

        $messages[] = "purged $deletedCount item(s) from $folder. (Avatars preserved)";
    }
}

json_response(['success' => true, 'messages' => $messages]);

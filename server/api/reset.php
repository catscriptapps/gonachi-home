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
    'landlord_services',

    // Dynamic Transactional Pipelines
    'access_tokens',
    'tenants',
    'subscriptions',
    'properties_pics',
    'properties',
    'landlords',

    // Authentication & Core
    'password_resets',
    'user_verifications', // Added here to ensure it gets cleared out aggressively
    'messages',
    'recent_activities',
    'users',
    'user_types',

    // Static/Lookup Tables
    'cities',
    'regions',
    'countries',
    'faqs',
    'services',
];

foreach ($tablesToDrop as $table) {
    Capsule::schema()->dropIfExists($table);
}

$messages[] = "database cleared: All dependent and parent tables dropped.";

/**
 * 3. CREATION PHASE - LEVEL 1: LOOKUPS & INDEPENDENT PARENTS
 * These must exist first because other tables reference them.
 */

// Core Users & Types
require_once __DIR__ . '/../../scripts/reset/user-types.php';
$messages = array_merge($messages, resetUserTypesTable());

require_once __DIR__ . '/../../scripts/reset/countries.php';
$messages = array_merge($messages, resetCountriesTable());

require_once __DIR__ . '/../../scripts/reset/regions.php';
$messages = array_merge($messages, resetRegionsTable());

require_once __DIR__ . '/../../scripts/reset/cities.php';
$messages = array_merge($messages, resetCitiesTable());

require_once __DIR__ . '/../../scripts/reset/users.php';
$messages = array_merge($messages, resetUsersTable());

// Support & Transient Auth Tables
require_once __DIR__ . '/../../scripts/reset/recent-activities.php';
$messages = array_merge($messages, resetRecentActivitiesTable());

require_once __DIR__ . '/../../scripts/reset/faqs.php';
$messages = array_merge($messages, resetFaqsTable());

require_once __DIR__ . '/../../scripts/reset/password-resets.php';
$messages = array_merge($messages, resetPasswordResetsTable());

// Placed directly next to password resets following your dash naming standards
require_once __DIR__ . '/../../scripts/reset/user-verifications.php';
$messages = array_merge($messages, resetUserVerificationsTable());

require_once __DIR__ . '/../../scripts/reset/messages.php';
$messages = array_merge($messages, resetMessagesTable());


/**
 * 4. CREATION PHASE - LEVEL 2: PROPERTY CORE INFRASTRUCTURE
 * Construct operational profiles, active assets, and downstream pipeline variables.
 */
require_once __DIR__ . '/../../scripts/reset/landlords.php';
$messages = array_merge($messages, resetLandlordsTable());

require_once __DIR__ . '/../../scripts/reset/subscriptions.php';
$messages = array_merge($messages, resetSubscriptionsTable());

require_once __DIR__ . '/../../scripts/reset/properties.php';
$messages = array_merge($messages, resetPropertiesTable());

require_once __DIR__ . '/../../scripts/reset/properties-pics.php';
$messages = array_merge($messages, resetPropertyPicsTable());

require_once __DIR__ . '/../../scripts/reset/services.php';
$messages = array_merge($messages, resetServicesTable());

require_once __DIR__ . '/../../scripts/reset/landlord-services.php';
$messages = array_merge($messages, resetLandlordServiceTable());

require_once __DIR__ . '/../../scripts/reset/access-tokens.php';
$messages = array_merge($messages, resetAccessTokensTable());

require_once __DIR__ . '/../../scripts/reset/tenants.php';
$messages = array_merge($messages, resetTenantsTable());


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

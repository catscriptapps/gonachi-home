<?php
// /scripts/reset/preserve-system-settings.php
//
// A database reset drops and recreates system_settings (see
// reset/system-settings.php), which reseeds both scraping toggles back to
// their default of true — silently undoing an admin's "paused" choice on
// the Settings page every time the database gets reset, even though that
// choice is an operational preference, not sample/demo data. Same
// backup-before-drop, restore-after-reseed pattern as
// reset/preserve-scraped-data.php, just for this one singleton row instead
// of the scraped leads/contractors tables.

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Call BEFORE the drop phase.
 *
 * @return array{leads_scraping_enabled: bool, contractor_discovery_enabled: bool}|null
 */
function backupSystemSettings(): ?array
{
    if (!Capsule::schema()->hasTable('system_settings')) {
        return null;
    }

    $row = Capsule::table('system_settings')->where('id', 1)->first();

    if (!$row) {
        return null;
    }

    return [
        'leads_scraping_enabled' => (bool) $row->leads_scraping_enabled,
        'contractor_discovery_enabled' => (bool) $row->contractor_discovery_enabled,
    ];
}

/**
 * Call AFTER system_settings has been recreated and reseeded with defaults
 * (resetSystemSettingsTable()) — overwrites those defaults with whatever
 * was actually set before the reset.
 *
 * @param array{leads_scraping_enabled: bool, contractor_discovery_enabled: bool}|null $backup
 * @return string[]
 */
function restoreSystemSettings(?array $backup): array
{
    if (!$backup) {
        return [];
    }

    Capsule::table('system_settings')->where('id', 1)->update($backup);

    return [
        'restored scraping toggle state from before the reset (leads: '
            . ($backup['leads_scraping_enabled'] ? 'on' : 'paused') . ', contractor discovery: '
            . ($backup['contractor_discovery_enabled'] ? 'on' : 'paused') . ')',
    ];
}

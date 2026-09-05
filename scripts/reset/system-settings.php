<?php
// /scripts/reset/system-settings.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\SystemSetting;

/**
 * Singleton settings row for global admin toggles — currently the
 * lead-scraping and contractor-discovery on/off switches on the Settings
 * page (see server/api/system-settings.php and the cron scripts under
 * scripts/cron/).
 */
function resetSystemSettingsTable(): array
{
    $messages = [];

    try {
        $tableName = (new SystemSetting())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('leads_scraping_enabled')->default(true);
            $table->boolean('contractor_discovery_enabled')->default(true);
            $table->timestamps();
        });

        $messages[] = "created {$tableName} table";

        SystemSetting::create([
            'leads_scraping_enabled' => true,
            'contractor_discovery_enabled' => true,
        ]);
        $messages[] = "seeded default {$tableName} row";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

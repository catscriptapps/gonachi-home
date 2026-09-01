<?php
// /scripts/reset/cde-contractor-sources.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\ContractorSource;

/**
 * Registry of contractor-discovery data sources — mirrors rel_lead_sources
 * (see scripts/reset/rel-lead-sources.php) so the same poll/track/log
 * pattern applies to contractor discovery as it does to lead extraction.
 */
function resetCdeContractorSourcesTable(): array
{
    $messages = [];

    try {
        $tableName = (new ContractorSource())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type');
            $table->string('connector_class');
            $table->string('base_url')->nullable();
            $table->json('config')->nullable();

            $table->boolean('is_active')->default(false);
            $table->unsignedInteger('poll_interval_minutes')->default(1440);
            $table->timestamp('last_polled_at')->nullable();

            $table->timestamps();
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

<?php
// /scripts/reset/lead-sources.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\LeadSource;

function resetLeadSourcesTable(): array
{
    $messages = [];

    try {
        $tableName = (new LeadSource())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('slug')->unique();
            // forum | classifieds | rss | search_api
            $table->string('type')->index();
            // Fully-qualified class name implementing Src\Service\LeadSources\LeadSourceConnector
            $table->string('connector_class');
            $table->string('base_url')->nullable();
            // Per-source settings (search queries, API key env var name, board URL, etc.)
            $table->json('config')->nullable();
            $table->boolean('is_active')->default(false)->index();
            $table->unsignedInteger('poll_interval_minutes')->default(60);
            $table->timestamp('last_polled_at')->nullable();
            $table->timestamps();
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

<?php
// /scripts/reset/rel-lead-extraction-runs.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\LeadExtractionRun;

function resetRelLeadExtractionRunsTable(): array
{
    $messages = [];

    try {
        $tableName = (new LeadExtractionRun())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('lead_source_id')->index();

            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();

            $table->unsignedInteger('items_found')->default(0);
            $table->unsignedInteger('items_new')->default(0);
            $table->unsignedInteger('items_duplicate')->default(0);
            $table->unsignedInteger('items_rejected')->default(0);

            // running | success | failed | partial
            $table->string('status')->default('running')->index();
            $table->text('error_message')->nullable();

            $table->timestamps();
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

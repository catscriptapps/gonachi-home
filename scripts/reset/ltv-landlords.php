<?php
// /scripts/reset/ltv-landlords.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\LandlordRecord;

function resetLtvLandlordsTable(): array
{
    $messages = [];

    try {
        $tableName = (new LandlordRecord())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            // trimmed/collapsed-whitespace/lowercased — used for find-or-create dedup
            $table->string('normalized_name')->index();
            $table->timestamps();
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

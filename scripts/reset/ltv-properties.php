<?php
// /scripts/reset/ltv-properties.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\PropertyRecord;

function resetLtvPropertiesTable(): array
{
    $messages = [];

    try {
        $tableName = (new PropertyRecord())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('landlord_id')->index();
            $table->string('address');
            $table->string('normalized_address')->index();
            $table->string('property_type')->nullable();
            $table->timestamps();

            $table->unique(['landlord_id', 'normalized_address']);
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

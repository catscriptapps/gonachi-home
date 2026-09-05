<?php
// /scripts/reset/rew-house-types.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\HouseType;

function resetRewHouseTypesTable(): array
{
    $messages = [];

    try {
        $tableName = (new HouseType())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->id('house_type_id');
            $table->string('house_type', 150);
            $table->timestamps();
        });

        $messages[] = "created {$tableName} table";

        $houses = ['Detached House', 'Semi-Detached House', 'Town House', 'Bungalow', 'Cottage'];
        foreach ($houses as $i => $house) {
            HouseType::create(['house_type_id' => $i + 1, 'house_type' => $house]);
        }

        $messages[] = "seeded " . count($houses) . " house types";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

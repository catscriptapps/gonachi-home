<?php
// /scripts/reset/rew-bedrooms.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Bedroom;

function resetRewBedroomsTable(): array
{
    $messages = [];

    try {
        $tableName = (new Bedroom())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->id('bedroom_id');
            $table->string('bedroom', 300)->nullable();
            $table->timestamps();
        });

        $messages[] = "created {$tableName} table";

        $seeds = [
            '1', '1 +Den', '2', '2 +Den', '3', '3 +Den', '4', '4 +Den', '5', '5 +Den',
            'Whole House (with Unfinished Basement)',
            'Whole House (with Finished Basement)',
            'Whole House (with Apartment Basement)',
        ];

        foreach ($seeds as $i => $bedroom) {
            Bedroom::create(['bedroom_id' => $i + 1, 'bedroom' => $bedroom]);
        }

        $messages[] = "seeded " . count($seeds) . " bedroom options";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

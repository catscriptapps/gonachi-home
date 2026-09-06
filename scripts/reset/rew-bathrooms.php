<?php
// /scripts/reset/rew-bathrooms.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Bathroom;

function resetRewBathroomsTable(): array
{
    $messages = [];

    try {
        $tableName = (new Bathroom())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->id('bathroom_id');
            $table->string('bathroom', 10)->nullable();
            $table->timestamps();
        });

        $messages[] = "created {$tableName} table";

        $seeds = ['1', '1.5', '2', '2.5', '3', '3.5', '4', '4.5', '5', '5.5', '6 +'];

        foreach ($seeds as $i => $bathroom) {
            Bathroom::create(['bathroom_id' => $i + 1, 'bathroom' => $bathroom]);
        }

        $messages[] = "seeded " . count($seeds) . " bathroom options";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

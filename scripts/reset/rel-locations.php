<?php
// /scripts/reset/rel-locations.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Location;

function resetRelLocationsTable(): array
{
    $messages = [];

    try {
        $tableName = (new Location())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            // Only ever set on root-level (country) rows — see
            // scripts/reset/rel-seed.php and Src\Utils\CountryScope. State/
            // area rows inherit their country implicitly via parent_id.
            $table->unsignedInteger('country_id')->nullable()->index();
            $table->timestamps();
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

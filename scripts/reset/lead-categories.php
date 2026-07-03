<?php
// /scripts/reset/lead-categories.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\LeadCategory;

function resetLeadCategoriesTable(): array
{
    $messages = [];

    try {
        $tableName = (new LeadCategory())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('slug')->unique();
            // buyer | seller | investor | renter
            $table->string('request_type')->index();
            // residential | commercial | land, nullable when not specific
            $table->string('property_type')->nullable();
            $table->timestamps();
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

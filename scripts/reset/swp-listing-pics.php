<?php
// /scripts/reset/swp-listing-pics.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\SwapListingPic;

function resetSwpListingPicsTable(): array
{
    $messages = [];

    try {
        $tableName = (new SwapListingPic())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('listing_id');
            $table->string('file_path');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index('listing_id');
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

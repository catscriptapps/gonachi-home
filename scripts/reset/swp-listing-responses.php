<?php
// /scripts/reset/swp-listing-responses.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\SwapListingResponse;

function resetSwpListingResponsesTable(): array
{
    $messages = [];

    try {
        $tableName = (new SwapListingResponse())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sender_id')->index();
            $table->unsignedBigInteger('listing_id')->index();
            $table->string('status', 20)->default('pending');
            $table->text('message')->nullable();
            $table->timestamps();
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

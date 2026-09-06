<?php
// /scripts/reset/rew-listing-responses.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\ListingResponse;

function resetRewListingResponsesTable(): array
{
    $messages = [];

    try {
        $tableName = (new ListingResponse())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sender_id')->index();
            $table->unsignedBigInteger('listing_id')->index();
            $table->string('status', 20)->default('pending');
            $table->text('message')->nullable();
            // Flips to false when the owner accepts/declines, so the sender
            // can be shown a "new" indicator the next time they see this
            // listing — flips back to true once that's been surfaced to them.
            $table->boolean('is_read')->default(true);
            $table->timestamps();

            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

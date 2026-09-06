<?php
// /scripts/reset/rew-listing-pics.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\ListingPic;

function resetRewListingPicsTable(): array
{
    $messages = [];

    try {
        $tableName = (new ListingPic())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->id('entry_id');
            $table->unsignedBigInteger('listing_id')->index();
            $table->string('pic_name', 300);
            $table->string('pic_caption', 300)->nullable();
            $table->unsignedInteger('pos_index')->default(0);
            $table->timestamps();

            $table->foreign('listing_id')->references('listing_id')->on('rew_listings')->onDelete('cascade');
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

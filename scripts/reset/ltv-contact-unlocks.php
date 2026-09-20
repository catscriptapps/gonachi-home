<?php
// /scripts/reset/ltv-contact-unlocks.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\LandlordContactUnlock;

function resetLtvContactUnlocksTable(): array
{
    $messages = [];

    try {
        $tableName = (new LandlordContactUnlock())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('landlord_id')->index();
            $table->timestamps();

            $table->unique(['user_id', 'landlord_id']);
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

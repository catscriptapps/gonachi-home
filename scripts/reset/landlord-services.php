<?php
// /scripts/reset/landlord-services.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\LandlordService;

function resetLandlordServiceTable(): array
{
    $messages = [];
    $tableName = (new LandlordService())->getTable();

    try {
        // Temporarily disable foreign key checks to prevent dropping errors
        Capsule::statement('SET FOREIGN_KEY_CHECKS=0;');

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "Dropped existing {$tableName} table.";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('landlord_id');
            $table->unsignedInteger('service_id');
            $table->unsignedTinyInteger('status_id')->default(1)->comment('1: Active, 0: Inactive');
            $table->dateTime('expires_at')->nullable();
            $table->dateTime('date_created')->useCurrent();
            $table->dateTime('timestamp')->useCurrentOnUpdate()->nullable();

            $table->foreign('landlord_id')->references('id')->on('landlords')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');

            $table->unique(['landlord_id', 'service_id']);
        });

        $messages[] = "Created {$tableName} table.";

        // Re-enable foreign key checks
        Capsule::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Seed default subscriptions
        // Give Landlord 1 (Simcoe) both services
        LandlordService::create(['landlord_id' => 1, 'service_id' => 1, 'status_id' => 1]);
        LandlordService::create(['landlord_id' => 1, 'service_id' => 2, 'status_id' => 1]);

        // Give Landlord 2 (Northern) just the rental application suite
        LandlordService::create(['landlord_id' => 2, 'service_id' => 1, 'status_id' => 1]);

        $messages[] = "Seeded default landlord-service relationships.";
    } catch (\Throwable $e) {
        $messages[] = "Error resetting {$tableName} table: " . $e->getMessage();
    }

    return $messages;
}

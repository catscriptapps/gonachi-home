<?php
// /scripts/reset/properties.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Property;

/**
 * Resets the properties table and sets up operational portfolio nodes.
 */
function resetPropertiesTable(): array
{
    $messages = [];

    try {
        $model = new Property();
        $tableName = $model->getTable();

        // Drop existing table
        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table.";

        // Create modular property layout architecture
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('landlord_id')->index();
            $table->string('property_name', 255)->nullable();
            $table->string('unit_number', 100)->nullable();
            $table->string('address_line1', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->unsignedInteger('region_id')->nullable()->index();
            $table->unsignedInteger('country_id')->nullable()->index();
            $table->string('postal_code', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('views')->default(0);
            $table->dateTime('date_created')->useCurrent();
            $table->dateTime('timestamp')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('landlord_id')->references('id')->on('landlords')->onDelete('cascade');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
            $table->foreign('region_id')->references('id')->on('regions')->onDelete('set null');
        });

        $messages[] = "created {$tableName} table structure.";

        // Seed baseline test endpoints
        $defaultProperties = [
            [
                'id'            => 1,
                'landlord_id'   => 1,
                'property_name' => 'Victoria Complex',
                'unit_number'   => 'Suite 404',
                'address_line1' => '250 Simcoe St',
                'city'          => 'Toronto',
                'region_id'     => 866, // Ontario
                'country_id'    => 39,  // Canada
                'postal_code'   => 'M5T 2T4',
                'is_active'     => true,
            ],
            [
                'id'            => 2,
                'landlord_id'   => 1,
                'property_name' => 'Lakeshore Tower',
                'unit_number'   => 'Townhouse 12',
                'address_line1' => '88 Lakeshore Rd',
                'city'          => 'Toronto',
                'region_id'     => 866, // Ontario
                'country_id'    => 39,  // Canada
                'postal_code'   => 'M5J 2W2',
                'is_active'     => true,
            ]
        ];

        foreach ($defaultProperties as $property) {
            Property::create($property);
        }

        $messages[] = "successfully seeded " . count($defaultProperties) . " asset structural units.";
    } catch (\Throwable $e) {
        $messages[] = 'properties table error: ' . $e->getMessage();
    }

    return $messages;
}

<?php
// /scripts/reset/rew-unit-types.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\UnitType;

function resetRewUnitTypesTable(): array
{
    $messages = [];

    try {
        $tableName = (new UnitType())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->id('unit_type_id');
            $table->string('unit_type', 150);
            $table->timestamps();
        });

        $messages[] = "created {$tableName} table";

        // unit_type_id 5 ("House") is the one that unlocks the House Type
        // field on the quotation form — matches legacy's Quotation::save()
        // check (`house_type_id` only persisted when unit_type_id === 5).
        $units = [
            'Apartment', 'Basement', 'Condo', 'Duplex / Triplex', 'House',
            'Shared Accomodation Bedrooms', 'Shared Accomodation House', 'Cottage Rentals',
        ];

        foreach ($units as $i => $unit) {
            UnitType::create(['unit_type_id' => $i + 1, 'unit_type' => $unit]);
        }

        $messages[] = "seeded " . count($units) . " unit types";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

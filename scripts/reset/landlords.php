<?php
// /scripts/reset/landlords.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Landlord;

function resetLandlordsTable(): array
{
    $messages = [];
    $tableName = (new Landlord())->getTable();

    try {
        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "Dropped existing {$tableName} table.";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('company_name')->nullable();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('phone')->nullable();
            $table->unsignedInteger('country_id')->nullable()->index();
            $table->unsignedInteger('region_id')->nullable()->index();
            $table->string('city')->nullable();
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('tax_id')->nullable();
            $table->text('avatar_url')->nullable();
            $table->unsignedTinyInteger('status_id')->default(1)->comment('1: Active, 0: Inactive');
            $table->dateTime('date_created')->useCurrent();
            $table->dateTime('timestamp')->useCurrentOnUpdate()->nullable();

            // Foreign keys
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
            $table->foreign('region_id')->references('id')->on('regions')->onDelete('set null');
        });

        $messages[] = "Created {$tableName} table.";

        // ---------------------------------------------------------
        // Default Landlord entries
        // ---------------------------------------------------------
        $defaultLandlords = [
            [
                'company_name' => 'Simcoe Property Group',
                'email' => 'accounts@simcoepg.com',
                'password' => password_hash('password', PASSWORD_DEFAULT),
                'phone' => '705-555-1234',
                'country_id' => 39, // Canada
                'region_id' => 866,  // Assuming 866 is Ontario
                'city' => 'Barrie',
                'address_line1' => '123 Main Street',
                'postal_code' => 'L4N 0A1',
                'avatar_url' => null,
                'status_id' => 1,
            ],
            [
                'company_name' => 'Northern Management',
                'email' => 'contact@northernmgmt.ca',
                'password' => password_hash('password', PASSWORD_DEFAULT),
                'phone' => '705-555-5678',
                'country_id' => 39, // Canada
                'region_id' => 866,  // Ontario
                'city' => 'Sudbury',
                'address_line1' => '456 Oak Avenue',
                'postal_code' => 'P3C 5N8',
                'avatar_url' => null,
                'status_id' => 1,
            ],
        ];

        foreach ($defaultLandlords as $landlord) {
            Landlord::create($landlord);
        }

        $messages[] = "Seeded " . count($defaultLandlords) . " default landlords.";
    } catch (\Throwable $e) {
        $messages[] = "Error resetting {$tableName} table: " . $e->getMessage();
    }

    return $messages;
}

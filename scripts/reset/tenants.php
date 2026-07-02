<?php
// /scripts/reset/tenants.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Tenant;

function resetTenantsTable(): array
{
    $messages = [];

    try {
        $tableName = (new Tenant())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "Dropped existing {$tableName} table.";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email')->unique();
            $table->string('phone', 30)->nullable();
            $table->string('password')->nullable();
            $table->unsignedTinyInteger('status_id')->default(1)->comment('1: Active, 0: Inactive');
            $table->dateTime('date_created')->useCurrent();
            $table->dateTime('timestamp')->useCurrent()->useCurrentOnUpdate();
        });

        $messages[] = "Created {$tableName} table.";

        // ---------------------------------------------------------
        // Default Tenant entries
        // ---------------------------------------------------------
        $defaultTenants = [
            [
                'first_name' => 'Jane',
                'last_name'  => 'Doe',
                'email'      => 'jane.doe@example.com',
                'phone'      => '705-555-0101',
                'status_id'  => 1,
            ],
            [
                'first_name' => 'John',
                'last_name'  => 'Smith',
                'email'      => 'john.smith@example.com',
                'phone'      => null,
                'status_id'  => 1,
            ],
        ];

        foreach ($defaultTenants as $tenant) {
            Tenant::create($tenant);
        }

        $messages[] = "Seeded " . count($defaultTenants) . " default tenants.";
    } catch (\Throwable $e) {
        $messages[] = "Error resetting tenants table: " . $e->getMessage();
    }

    return $messages;
}

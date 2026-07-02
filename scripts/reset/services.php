<?php
// /scripts/reset/services.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Service;

function resetServicesTable(): array
{
    $messages = [];
    $tableName = (new Service())->getTable();

    try {
        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "Dropped existing {$tableName} table.";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('short_description')->nullable();
            $table->text('long_description')->nullable();
            $table->unsignedTinyInteger('status_id')->default(1);
            $table->dateTime('date_created')->useCurrent();
            $table->dateTime('timestamp')->useCurrentOnUpdate()->nullable();
        });

        $messages[] = "Created {$tableName} table.";

        // ---------------------------------------------------------
        // Default Service entries
        // ---------------------------------------------------------
        $defaultServices = [
            [
                'name' => 'Rental Applications Suite',
                'slug' => 'rental-applications',
                'short_description' => 'Prospective tenant application processing engine.',
                'long_description' => 'Collect verification files, generate risk metrics, and process lease approvals cleanly.',
                'status_id' => 1,
            ],
            [
                'name' => 'Field Inspection Suite',
                'slug' => 'inspections',
                'short_description' => 'Real-time property verification systems.',
                'long_description' => 'Structured multi-point documentation containing high-resolution asset tracking reports.',
                'status_id' => 1,
            ],
        ];

        foreach ($defaultServices as $service) {
            Service::create($service);
        }

        $messages[] = "Seeded " . count($defaultServices) . " default services.";
    } catch (\Throwable $e) {
        $messages[] = "Error resetting {$tableName} table: " . $e->getMessage();
    }

    return $messages;
}

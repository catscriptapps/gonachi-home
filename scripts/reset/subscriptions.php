<?php
// /scripts/reset/subscriptions.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Subscription;

/**
 * Resets the subscriptions table and wires default active packages.
 */
function resetSubscriptionsTable(): array
{
    $messages = [];

    try {
        $model = new Subscription();
        $tableName = $model->getTable();

        // Drop existing table
        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table.";

        // Create table schema mapping configuration targets
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('landlord_id')->index();
            $table->string('tier_code', 50)->default('basic');
            $table->string('status', 50)->default('active');
            $table->integer('token_limit')->default(5);
            $table->integer('token_count')->default(0);
            $table->decimal('monthly_inflow', 10, 2)->default(0.00);
            $table->dateTime('date_created')->useCurrent();
            $table->dateTime('timestamp')->useCurrent()->useCurrentOnUpdate();
        });

        $messages[] = "created {$tableName} table structure.";

        // Seed default system records
        $defaultSubscriptions = [
            [
                'id'             => 1,
                'landlord_id'    => 1,
                'tier_code'      => 'portfolio',
                'status'         => 'active',
                'token_limit'    => 25,
                'token_count'    => 2,
                'monthly_inflow' => 149.99,
            ]
        ];

        foreach ($defaultSubscriptions as $subscription) {
            Subscription::create($subscription);
        }

        $messages[] = "successfully seeded " . count($defaultSubscriptions) . " pipeline subscriptions.";
    } catch (\Throwable $e) {
        $messages[] = 'subscriptions table error: ' . $e->getMessage();
    }

    return $messages;
}

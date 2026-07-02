<?php
// /scripts/reset/access-tokens.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\AccessToken;

/**
 * Resets the access_tokens table and records foundational tracking identifiers.
 */
function resetAccessTokensTable(): array
{
    $messages = [];

    try {
        $model = new AccessToken();
        $tableName = $model->getTable();

        // Drop existing table
        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table.";

        // Create transaction mapping tracking fields
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('landlord_id')->index();
            $table->unsignedInteger('property_id')->index();
            $table->unsignedInteger('service_id')->index();
            $table->string('token_code', 100)->unique();
            $table->string('status', 20)->default('active'); // 'active' | 'revoked'
            $table->dateTime('date_created')->useCurrent();
            $table->dateTime('timestamp')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');

            // Enforces at most one active token per property + service. Revoked
            // tokens fall back to NULL, and MySQL allows unlimited NULLs in a unique index.
            $table->string('active_key', 64)->virtualAs("IF(status='active', CONCAT(property_id,'-',service_id), NULL)")->nullable()->unique();
        });

        $messages[] = "created {$tableName} table structure.";

        // Seed starting token vectors using structural configuration variables
        $defaultTokens = [
            [
                'id'          => 1,
                'landlord_id' => 1,
                'property_id' => 1,
                'service_id'  => 1,
                'token_code'  => 'ACC-26-SIMCOE-4041',
                'status'      => 'active',
            ],
            [
                'id'          => 2,
                'landlord_id' => 1,
                'property_id' => 2,
                'service_id'  => 1,
                'token_code'  => 'ACC-26-LAKE-T122',
                'status'      => 'active',
            ]
        ];

        foreach ($defaultTokens as $token) {
            AccessToken::create($token);
        }

        $messages[] = "successfully seeded " . count($defaultTokens) . " operational access keys.";
    } catch (\Throwable $e) {
        $messages[] = 'access tokens table error: ' . $e->getMessage();
    }

    return $messages;
}

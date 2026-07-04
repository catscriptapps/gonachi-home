<?php
// /scripts/reset/users.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\User;

function resetUsersTable(): array
{
    $messages = [];
    try {
        // 1. Force safety inside the script
        Capsule::schema()->disableForeignKeyConstraints();

        $tableName = 'users';
        Capsule::schema()->dropIfExists($tableName);

        // 2. Create structure matching legacy + your new JSON field
        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id'); // This is our BIGINT to match other tables
            $table->string('first_name', 300)->nullable();
            $table->string('last_name', 300)->nullable();
            $table->string('email', 300)->nullable();
            $table->unsignedInteger('country_id')->nullable();
            $table->unsignedInteger('region_id')->nullable();
            $table->string('city', 300)->nullable();
            $table->string('user_code', 40)->nullable();
            $table->string('password', 300)->nullable();
            $table->string('api_token', 300)->nullable();
            $table->integer('status_id')->nullable();
            $table->datetime('date_created')->nullable();
            $table->datetime('user_last_log')->nullable();
            $table->text('avatar_url')->nullable();
            $table->boolean('email_verified')->default(false);
            $table->datetime('timestamp')->nullable();

            // The magic collection column
            $table->json('user_type_ids')->nullable();
        });

        $messages[] = "recreated 'users' table with correct legacy fields.";

        // 3. IDEAL MAPPING ARRAY
        $legacyMappings = [

            [1, 1],
            [1, 2],
            [2, 1],
            [2, 2],
        ];

        // 4. Convert pairs into a lookup dictionary
        $userTypeLookup = [];
        foreach ($legacyMappings as $pair) {
            $uid = $pair[0];
            $tid = $pair[1];
            $userTypeLookup[$uid][] = $tid;
        }

        // 5. THE LEGACY USER DATA
        // Order: user_id, first_name, last_name, email, country_id, region_id, city, 
        // user_code, password, status_id, date_created, user_last_log, avatar_url, 
        // email_verified, timestamp
        $usersData = [

            [1, 'Cat', 'Nduanya', 'mindofcat@hotmail.com', 39, 866, 'Barrie', '7QESZL', '$2y$10$n7WqLLBr3SPk/A7jgK8nt.Rke6dZ5VGsX9E5tDGL1p0XYAJpHudNy', 1, '2023-07-29', '2026-02-18 19:48:54', null, 1, '2023-07-29 14:29:33'],
            [2, 'Elas', 'Abone', 'chyigwe@yahoo.com', 39, 866, 'Ajax', '', '$2y$10$WHQkT.ddJe/PnIA1a1ruM.pOfQpt6WlHBts5yn.4PmHhHEfDYV0SK', 1, '2023-07-29', '2024-07-15 12:04:45', null, 1, '2024-06-19 18:06:43'],
        ];

        // 6. Loop and Insert
        $count = 0;
        foreach ($usersData as $row) {
            $legacyId = $row[0];

            // Check the dictionary for types, or provide empty array
            $assignedTypes = $userTypeLookup[$legacyId] ?? [];

            User::create([
                'id'             => $legacyId,
                'first_name'     => $row[1],
                'last_name'      => $row[2],
                'email'          => $row[3],
                'country_id'     => $row[4],
                'region_id'      => $row[5],
                'city'           => $row[6],
                'user_code'      => $row[7],
                'password'       => $row[8],
                'status_id'      => $row[9],
                'date_created'   => $row[10],
                'user_last_log'  => $row[11],
                'avatar_url'     => null,
                'email_verified' => (bool)$row[13],
                'timestamp'      => $row[14],
                'user_type_ids'  => $assignedTypes // INJECTED!
            ]);
            $count++;
        }

        $messages[] = "Successfully imported $count users with their type mappings.";
    } catch (\Throwable $e) {
        $messages[] = 'Users table error: ' . $e->getMessage();
    } finally {
        Capsule::schema()->enableForeignKeyConstraints();
    }

    return $messages;
}

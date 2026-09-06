<?php
// /scripts/reset/user-types.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\UserType;

/**
 * The system-wide account/stakeholder type list (Admin plus every
 * real-world role), matching the legacy gonachi/ platform's users_types
 * table and seed data exactly.
 */
function resetUserTypesTable(): array
{
    $messages = [];

    try {
        $tableName = (new UserType())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('user_type_id');
            $table->string('user_type', 300)->nullable();
        });

        $messages[] = "created {$tableName} table";

        $types = [
            1 => 'Admin',
            2 => 'Landlord',
            3 => 'Tenant',
            4 => 'Property Manager',
            5 => 'Real Estate Agent',
            6 => 'Contractor',
            7 => 'Mortgage Broker',
            8 => 'User',
        ];

        foreach ($types as $id => $name) {
            UserType::create(['user_type_id' => $id, 'user_type' => $name]);
        }

        $messages[] = "seeded " . count($types) . " user types";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

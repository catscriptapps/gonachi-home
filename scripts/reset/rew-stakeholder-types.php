<?php
// /scripts/reset/rew-stakeholder-types.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\StakeholderType;

/**
 * Real Estate World's own stakeholder categories — currently used as the
 * Mentors module's "who this mentor helps" category. Distinct from the
 * system-wide account permission types in UserTypesController.
 */
function resetRewStakeholderTypesTable(): array
{
    $messages = [];

    try {
        $tableName = (new StakeholderType())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->timestamps();
        });

        $messages[] = "created {$tableName} table";

        $types = ['Landlord', 'Tenant', 'Contractor', 'Mortgage Broker', 'Property Manager', 'Real Estate Agent', 'Other'];
        foreach ($types as $i => $name) {
            StakeholderType::create(['id' => $i + 1, 'name' => $name]);
        }

        $messages[] = "seeded " . count($types) . " stakeholder types";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

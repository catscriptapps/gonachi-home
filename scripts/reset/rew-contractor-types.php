<?php
// /scripts/reset/rew-contractor-types.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\ContractorType;

function resetRewContractorTypesTable(): array
{
    $messages = [];

    try {
        $tableName = (new ContractorType())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->id('contractor_type_id');
            $table->string('contractor_type', 100);
            $table->timestamps();
        });

        $messages[] = "created {$tableName} table";

        $types = ['Licensed Contractor', 'Unlicensed Contractor', 'Not Applicable'];
        foreach ($types as $i => $type) {
            ContractorType::create(['contractor_type_id' => $i + 1, 'contractor_type' => $type]);
        }

        $messages[] = "seeded " . count($types) . " contractor types";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

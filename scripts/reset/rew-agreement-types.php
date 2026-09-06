<?php
// /scripts/reset/rew-agreement-types.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\AgreementType;

function resetRewAgreementTypesTable(): array
{
    $messages = [];

    try {
        $tableName = (new AgreementType())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->id('agreement_type_id');
            $table->string('agreement_type', 300)->nullable();
            $table->timestamps();
        });

        $messages[] = "created {$tableName} table";

        $seeds = ['Month-to-Month', '1 Year', 'Not Applicable'];

        foreach ($seeds as $i => $type) {
            AgreementType::create(['agreement_type_id' => $i + 1, 'agreement_type' => $type]);
        }

        $messages[] = "seeded " . count($seeds) . " agreement types";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

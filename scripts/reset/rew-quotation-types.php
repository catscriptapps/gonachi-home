<?php
// /scripts/reset/rew-quotation-types.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\QuotationType;

function resetRewQuotationTypesTable(): array
{
    $messages = [];

    try {
        $tableName = (new QuotationType())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->id('quotation_type_id');
            $table->string('quotation_type', 150);
            $table->timestamps();
        });

        $messages[] = "created {$tableName} table";

        $types = ['Labour Only', 'Labour And Materials'];
        foreach ($types as $i => $type) {
            QuotationType::create(['quotation_type_id' => $i + 1, 'quotation_type' => $type]);
        }

        $messages[] = "seeded " . count($types) . " quotation types";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

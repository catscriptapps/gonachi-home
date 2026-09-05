<?php
// /scripts/reset/rew-quotation-destinations.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\QuotationDestination;

function resetRewQuotationDestinationsTable(): array
{
    $messages = [];

    try {
        $tableName = (new QuotationDestination())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->id('quotation_dest_id');
            $table->string('quotation_dest', 150);
            $table->timestamps();
        });

        $messages[] = "created {$tableName} table";

        $destinations = ['Contractors Within Region', 'Contractors Within Country'];
        foreach ($destinations as $i => $dest) {
            QuotationDestination::create(['quotation_dest_id' => $i + 1, 'quotation_dest' => $dest]);
        }

        $messages[] = "seeded " . count($destinations) . " quotation destinations";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

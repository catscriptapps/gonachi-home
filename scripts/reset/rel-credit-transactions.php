<?php
// /scripts/reset/rel-credit-transactions.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\CreditTransaction;

function resetRelCreditTransactionsTable(): array
{
    $messages = [];

    try {
        $tableName = (new CreditTransaction())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            // Positive for grants/purchases, negative for spends
            $table->integer('amount');
            $table->integer('balance_after');
            // trial_grant | lead_unlock | purchase | admin_adjustment
            $table->string('reason')->index();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamps();
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

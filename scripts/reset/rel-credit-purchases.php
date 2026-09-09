<?php
// /scripts/reset/rel-credit-purchases.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\CreditPurchase;

function resetRelCreditPurchasesTable(): array
{
    $messages = [];

    try {
        $tableName = (new CreditPurchase())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            // Paystack transaction reference — unique so a verify call can
            // never credit the same purchase twice.
            $table->string('reference')->unique();
            $table->integer('credits');
            $table->integer('amount_kobo');
            // pending | success | failed
            $table->string('status')->default('pending')->index();
            $table->timestamps();
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

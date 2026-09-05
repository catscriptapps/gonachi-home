<?php
// /scripts/reset/rew-quotation-responses.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\QuotationResponse;

function resetRewQuotationResponsesTable(): array
{
    $messages = [];

    try {
        $tableName = (new QuotationResponse())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sender_id')->index();
            $table->unsignedBigInteger('quotation_id')->index();
            $table->string('status', 20)->default('pending');
            $table->text('message')->nullable();
            $table->timestamps();

            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('quotation_id')->references('quotation_id')->on('rew_quotations')->onDelete('cascade');
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

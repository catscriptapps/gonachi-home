<?php
// /scripts/reset/cde-contractor-outreach-log.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\ContractorOutreachLog;

function resetCdeContractorOutreachLogTable(): array
{
    $messages = [];

    try {
        $tableName = (new ContractorOutreachLog())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('contractor_id')->index();
            $table->unsignedBigInteger('sent_by_user_id')->nullable();

            // sms | email
            $table->string('channel')->index();
            $table->string('recipient');
            $table->text('message');

            // sent | failed
            $table->string('status')->index();
            $table->text('error_message')->nullable();

            $table->timestamps();
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

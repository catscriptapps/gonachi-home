<?php
// /scripts/reset/cde-job-request-bids.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\JobRequestBid;

/**
 * A contractor's bid/quote on a homeowner's job request — the "Submit A
 * Quote" flow. No FK constraints, matching the rest of the cde_job_requests
 * table family (unlike Real Estate World's rew_ tables, which do use FKs).
 */
function resetCdeJobRequestBidsTable(): array
{
    $messages = [];

    try {
        $tableName = (new JobRequestBid())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('job_request_id')->index();
            $table->unsignedBigInteger('sender_id')->index();
            $table->decimal('quote_amount', 14, 2)->nullable();
            $table->text('message')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->timestamps();
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

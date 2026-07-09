<?php
// /scripts/reset/ltv-reports.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\LandlordReport;

function resetLtvReportsTable(): array
{
    $messages = [];

    try {
        $tableName = (new LandlordReport())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('property_id')->index();
            // Denormalized for direct landlord-scoped queries
            $table->unsignedBigInteger('landlord_id')->index();
            $table->unsignedBigInteger('user_id')->index();

            $table->string('duration_of_tenancy')->nullable();
            // deposit | harassment | unsafe | eviction | other
            $table->string('issue_type')->index();
            $table->text('notes')->nullable();

            // pending_review | published | rejected
            $table->string('status')->default('pending_review')->index();

            $table->timestamps();
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

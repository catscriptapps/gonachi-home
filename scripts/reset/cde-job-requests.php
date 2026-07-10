<?php
// /scripts/reset/cde-job-requests.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\JobRequest;

function resetCdeJobRequestsTable(): array
{
    $messages = [];

    try {
        $tableName = (new JobRequest())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();

            // plumbing | electrical | painting | building_construction |
            // interior_design | renovation | solar_installation | other
            $table->string('service_category')->index();
            $table->string('location');
            $table->decimal('budget', 14, 2)->nullable();
            $table->text('description');
            $table->string('timeline')->nullable();
            $table->string('contact_phone');

            // open | closed
            $table->string('status')->default('open')->index();

            $table->timestamps();

            $table->index(['service_category', 'location', 'status']);
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

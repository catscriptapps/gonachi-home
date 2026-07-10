<?php
// /scripts/reset/cde-contractors.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Contractor;

function resetCdeContractorsTable(): array
{
    $messages = [];

    try {
        $tableName = (new Contractor())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('business_name');

            // plumbing | electrical | painting | building_construction |
            // interior_design | renovation | solar_installation | other
            $table->string('service_category')->index();
            $table->string('location');
            $table->text('operating_areas')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->text('description')->nullable();
            $table->decimal('rating', 2, 1)->nullable();
            $table->unsignedInteger('review_count')->default(0);

            $table->unsignedBigInteger('claimed_by_user_id')->nullable()->index();
            // unclaimed | pending | claimed
            $table->string('claim_status')->default('unclaimed')->index();

            // active | inactive
            $table->string('status')->default('active')->index();

            $table->timestamps();

            $table->index(['service_category', 'location', 'status']);
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

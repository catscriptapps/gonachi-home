<?php
// /scripts/reset/ltv-rental-listings.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\RentalListing;

function resetLtvRentalListingsTable(): array
{
    $messages = [];

    try {
        $tableName = (new RentalListing())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('property_id')->index();
            // Denormalized for direct landlord-scoped queries
            $table->unsignedBigInteger('landlord_id')->index();
            $table->unsignedBigInteger('user_id')->index();

            // Landlord-selected area, e.g. "Lekki" — grouped as-is for the
            // "New Listings in {area}" counters, so this stays a fixed
            // dropdown on the submission form rather than free text.
            $table->string('area')->index();
            $table->unsignedTinyInteger('bedrooms')->nullable();
            $table->string('property_type')->nullable();
            $table->decimal('rent_amount', 14, 2)->nullable();
            // year | month
            $table->string('rent_period')->default('year');
            $table->text('description')->nullable();

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

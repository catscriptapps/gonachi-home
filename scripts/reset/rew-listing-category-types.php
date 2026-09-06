<?php
// /scripts/reset/rew-listing-category-types.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\ListingCategoryType;

function resetRewListingCategoryTypesTable(): array
{
    $messages = [];

    try {
        $tableName = (new ListingCategoryType())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->id('category_type_id');
            $table->unsignedBigInteger('category_id')->index();
            $table->string('category_type', 300);
            $table->timestamps();

            $table->foreign('category_id')->references('category_id')->on('rew_listing_categories')->onDelete('cascade');
        });

        $messages[] = "created {$tableName} table";

        $types = [
            ['category_id' => 1, 'category_type' => 'Accomodation for Rent'],
            ['category_id' => 1, 'category_type' => 'Accomodation for Sale'],
            ['category_id' => 1, 'category_type' => 'Commercial Renting'],
            ['category_id' => 2, 'category_type' => 'Real Estate Agent Services'],
            ['category_id' => 2, 'category_type' => 'Broker Services'],
            ['category_id' => 2, 'category_type' => 'Contractor Services'],
            ['category_id' => 1, 'category_type' => 'Short Rentals'],
        ];

        foreach ($types as $i => $type) {
            ListingCategoryType::create(array_merge(['category_type_id' => $i + 1], $type));
        }

        $messages[] = "seeded " . count($types) . " listing category types";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

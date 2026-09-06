<?php
// /scripts/reset/rew-amenities.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\AmenityCategory;
use App\Models\Amenity;

function resetRewAmenitiesTables(): array
{
    $messages = [];

    try {
        Capsule::schema()->dropIfExists('rew_amenities');
        Capsule::schema()->dropIfExists('rew_amenity_categories');
        $messages[] = "dropped existing rew_amenities tables";

        Capsule::schema()->create('rew_amenity_categories', function (Blueprint $table) {
            $table->id('category_id');
            $table->string('name', 100);
            $table->timestamps();
        });

        Capsule::schema()->create('rew_amenities', function (Blueprint $table) {
            $table->id('amenity_id');
            $table->unsignedBigInteger('category_id');
            $table->string('name', 150);
            $table->timestamps();

            $table->foreign('category_id')->references('category_id')->on('rew_amenity_categories')->onDelete('cascade');
        });

        $messages[] = "created rew_amenities table structures";

        $categories = ['Appliances', 'Utilities', 'Wi-Fi & Entertainment', 'Outdoor Space'];
        foreach ($categories as $i => $name) {
            AmenityCategory::create(['category_id' => $i + 1, 'name' => $name]);
        }

        $amenities = [
            ['category_id' => 1, 'name' => 'Laundry (In Unit)'],
            ['category_id' => 1, 'name' => 'Laundry (In Building)'],
            ['category_id' => 1, 'name' => 'Dishwasher'],
            ['category_id' => 1, 'name' => 'Fridge / Freezer'],
            ['category_id' => 2, 'name' => 'Water'],
            ['category_id' => 2, 'name' => 'Hydro'],
            ['category_id' => 2, 'name' => 'Heat'],
            ['category_id' => 3, 'name' => 'Internet'],
            ['category_id' => 3, 'name' => 'Cable / TV'],
            ['category_id' => 4, 'name' => 'Yard'],
            ['category_id' => 4, 'name' => 'Balcony'],
        ];
        foreach ($amenities as $a) {
            Amenity::create($a);
        }

        $messages[] = "seeded " . count($categories) . " amenity categories and " . count($amenities) . " amenities";
    } catch (\Throwable $e) {
        $messages[] = "rew_amenities error: " . $e->getMessage();
    }

    return $messages;
}

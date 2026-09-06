<?php
// /scripts/reset/rew-listings.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Listing;
use App\Models\User;

function resetRewListingsTable(): array
{
    $messages = [];

    try {
        $tableName = (new Listing())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->id('listing_id');

            $table->unsignedBigInteger('orig_user_id')->nullable()->index();

            $table->string('listing_title', 300)->nullable();
            $table->string('city', 300)->nullable();

            // Two-level taxonomy: rew_listing_categories -> rew_listing_category_types.
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->unsignedBigInteger('category_type_id')->nullable()->index();

            $table->unsignedBigInteger('unit_type_id')->nullable()->index();
            $table->unsignedBigInteger('house_type_id')->nullable()->index();
            $table->unsignedBigInteger('bedroom_id')->nullable()->index();
            $table->unsignedBigInteger('bathroom_id')->nullable()->index();

            $table->text('listing_description')->nullable();
            $table->string('address', 300)->nullable();
            $table->unsignedInteger('country_id')->nullable()->index();
            $table->unsignedInteger('region_id')->nullable()->index();

            $table->unsignedBigInteger('agreement_type_id')->nullable()->index();
            $table->string('price', 100)->nullable();
            $table->string('property_size', 11)->nullable();
            $table->string('move_in_date', 11)->nullable();

            $table->string('is_ac', 30)->nullable();
            $table->string('is_furnished', 30)->nullable();
            $table->string('parking', 30)->nullable();
            $table->string('pets_allowed', 30)->nullable();

            $table->json('amenities')->nullable();

            $table->text('youtube_url')->nullable();
            $table->string('contact_phone', 30)->nullable();

            // 1=Active, 2=Archived — matches Quotations/Adverts status convention.
            $table->unsignedTinyInteger('status_id')->default(1);
            $table->unsignedInteger('views')->default(0);

            $table->timestamps();

            $table->foreign('orig_user_id')->references('id')->on('users')->onDelete('cascade');
        });

        $messages[] = "created {$tableName} table";

        $owner = User::find(1);

        Listing::create([
            'orig_user_id' => 1,
            'listing_title' => '2-Bedroom Downtown Condo for Rent',
            'city' => $owner->city ?? 'Toronto',
            'category_id' => 1,
            'category_type_id' => 1,
            'unit_type_id' => 3,
            'house_type_id' => null,
            'bedroom_id' => 3,
            'bathroom_id' => 3,
            'listing_description' => 'Bright and spacious 2-bedroom condo in the heart of downtown, walking distance to transit, shops, and restaurants.',
            'address' => '123 Main Street',
            'country_id' => $owner->country_id ?? 1,
            'region_id' => $owner->region_id ?? 0,
            'agreement_type_id' => 2,
            'price' => '2,200/month',
            'property_size' => '950',
            'move_in_date' => date('Y-m-d', strtotime('+30 days')),
            'is_ac' => '1',
            'is_furnished' => '0',
            'parking' => '1',
            'pets_allowed' => '0',
            'amenities' => [1, 5, 6, 8],
            'youtube_url' => '',
            'contact_phone' => '',
            'status_id' => 1,
            'views' => 0,
        ]);

        $messages[] = "seeded initial gonachi listing";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

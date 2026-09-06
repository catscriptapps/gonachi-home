<?php
// /scripts/reset/rew-listing-categories.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\ListingCategory;

function resetRewListingCategoriesTable(): array
{
    $messages = [];

    try {
        $tableName = (new ListingCategory())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->id('category_id');
            $table->string('category', 300)->nullable();
            $table->timestamps();
        });

        $messages[] = "created {$tableName} table";

        $categories = ['Real Estate', 'Real Estate Services', 'Other'];
        foreach ($categories as $i => $category) {
            ListingCategory::create(['category_id' => $i + 1, 'category' => $category]);
        }

        $messages[] = "seeded " . count($categories) . " listing categories";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

<?php
// /scripts/reset/rel-saved-searches.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\SavedSearch;

/**
 * A saved search is a (search_query, region_slug) pair a user wants to keep
 * an eye on — mirrors the exact filter vocabulary already live on
 * /real-estate-leads (see LeadsController::browse()), so "View Matches"
 * can just deep-link into that same page with the same query params.
 * last_viewed_at drives the "N new since you last checked" badge.
 */
function resetRelSavedSearchesTable(): array
{
    $messages = [];

    try {
        $tableName = (new SavedSearch())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('user_id')->index();
            $table->string('search_query')->nullable();
            $table->string('region_slug')->nullable();
            $table->timestamp('last_viewed_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

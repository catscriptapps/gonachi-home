<?php
// /scripts/reset/swp-listings.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\SwapListing;

/**
 * Gonachi Swap Marketplace's core entity — ported from the standalone gonachi-swap app's
 * `listings` table (its own gonachi_swap_db), simplified to match this
 * app's sibling-project conventions: a plain bigIncrements id instead of a
 * custom-named PK, and listing_type/condition as plain string columns (like
 * LandlordReport.issue_type / TenantReport.conduct_type) instead of separate
 * listing_types/listing_conditions lookup tables — these are small fixed
 * enums, not admin-managed data.
 */
function resetSwpListingsTable(): array
{
    $messages = [];

    try {
        $tableName = (new SwapListing())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            // swap | sale | gift
            $table->string('listing_type')->default('swap');
            // new | like_new | used | parts
            $table->string('condition')->default('used');
            $table->decimal('price', 10, 2)->nullable();
            $table->text('trade_pref')->nullable();
            $table->string('city')->nullable();
            // Uploaded video filename only (e.g. "vid_....mp4") — the URL is
            // always derived as assetBase + 'videos/swap-listings/' + this,
            // same convention as Real Estate World's rew_quotations.video_name.
            // Max one at a time: SwapListingsController::attachVideo()
            // always replaces whichever video already exists.
            $table->string('video_name')->nullable();
            $table->unsignedBigInteger('views')->default(0);
            // draft | posted | completed | archived
            $table->string('status')->default('posted');
            $table->timestamps();

            $table->index('category_id');
            $table->index('listing_type');
            $table->index('status');
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

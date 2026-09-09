<?php
// /scripts/reset/rew-ratings.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Rating;
use App\Models\User;

/**
 * A user's rating of another user, in the context of a role (user_type) and
 * location — ported from gonachi-old's gnc_rating table. No status_id
 * column here: the legacy column existed but was hard-coded to 1 on every
 * insert and never read/branched on anywhere in that codebase, so it's
 * genuinely dead weight, not a real moderation state.
 *
 * Depends on rew-rating-criteria.php having already run (not for an FK —
 * there isn't one to this table — but resetRewRatingCriteriaScoresTable(),
 * which seeds the example rating's stars, needs both this table and the
 * criteria table to already exist, so it always runs after this one).
 */
function resetRewRatingsTable(): array
{
    $messages = [];

    try {
        $tableName = (new Rating())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('rating_id');

            $table->unsignedBigInteger('orig_user_id')->index();
            $table->unsignedBigInteger('dest_user_id')->index();

            $table->unsignedInteger('dest_country_id')->nullable();
            $table->unsignedInteger('dest_region_id')->nullable();
            $table->string('dest_city', 150)->nullable();
            $table->unsignedInteger('dest_user_type_id')->index();

            $table->text('comment');

            $table->timestamps();

            $table->foreign('orig_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('dest_user_id')->references('id')->on('users')->onDelete('cascade');
        });

        $messages[] = "created {$tableName} table";

        $rater = User::find(2);
        $ratee = User::find(1);

        if ($rater && $ratee) {
            Rating::create([
                'orig_user_id' => $rater->id,
                'dest_user_id' => $ratee->id,
                'dest_country_id' => $ratee->country_id,
                'dest_region_id' => $ratee->region_id,
                'dest_city' => $ratee->city,
                'dest_user_type_id' => 2,
                'comment' => 'Great to work with — always responsive and easy to reach.',
            ]);

            $messages[] = "seeded initial gonachi rating";
        }
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

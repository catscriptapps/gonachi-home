<?php
// /scripts/reset/rew-rating-criteria-scores.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Rating;
use App\Models\RatingCriteriaScore;
use App\Models\RatingCriterion;

/**
 * One 1-5 star score per criterion for a given rating — ported from
 * gonachi-old's gnc_rating_details. A rating for a user type with no
 * criteria (e.g. the generic "User" type) simply has zero rows here.
 */
function resetRewRatingCriteriaScoresTable(): array
{
    $messages = [];

    try {
        $tableName = (new RatingCriteriaScore())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('rating_id')->index();
            $table->unsignedBigInteger('criteria_id')->index();
            $table->unsignedTinyInteger('stars');
            $table->timestamps();

            $table->foreign('rating_id')->references('rating_id')->on('rew_ratings')->onDelete('cascade');
            $table->foreign('criteria_id')->references('criteria_id')->on('rew_rating_criteria')->onDelete('cascade');
        });

        $messages[] = "created {$tableName} table";

        $seedRating = Rating::first();
        if ($seedRating) {
            $stars = [5, 4, 5, 4];
            foreach (RatingCriterion::where('user_type_id', $seedRating->dest_user_type_id)->orderBy('criteria_id')->get() as $i => $criterion) {
                RatingCriteriaScore::create([
                    'rating_id' => $seedRating->rating_id,
                    'criteria_id' => $criterion->criteria_id,
                    'stars' => $stars[$i] ?? 4,
                ]);
            }

            $messages[] = "seeded example rating criteria scores";
        }
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

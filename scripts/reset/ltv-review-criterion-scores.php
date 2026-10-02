<?php
// /scripts/reset/ltv-review-criterion-scores.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\ReviewCriterionScore;

function resetLtvReviewCriterionScoresTable(): array
{
    $messages = [];

    try {
        $tableName = (new ReviewCriterionScore())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('review_id')->index();
            $table->unsignedBigInteger('criterion_id')->index();

            // stars/is_na are deliberately two columns, not one nullable
            // stars column — the submission form must enforce every
            // criterion is ACTIVELY answered (a star 1-5, or explicit N/A),
            // never silently skipped. See Src\Service\ReviewAggregationService
            // ::reviewOverallScore() for how is_na is excluded + weights
            // re-normalized.
            $table->unsignedTinyInteger('stars')->nullable();
            $table->boolean('is_na')->default(false);

            $table->timestamps();
            $table->unique(['review_id', 'criterion_id']);
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

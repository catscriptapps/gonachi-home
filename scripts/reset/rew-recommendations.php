<?php
// /scripts/reset/rew-recommendations.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Recommendation;
use App\Models\User;

/**
 * A user recommending another user (in a given role + location) to an
 * audience group (another role) — ported from gonachi-old's gnc_recommend
 * table. Deliberately drops rec_type_id/rec_user_id: gonachi-old's schema
 * still carried an "Individual" targeting mode (recommend to one specific
 * named person) alongside the "User Groups" mode, but the live app's UI
 * only ever exposed "User Groups" and the live submit handler hard-forced
 * rec_user_id=0 regardless of input — Individual targeting was dead code
 * there, not a real feature, so it isn't resurrected here. No status_id
 * either, for the same reason as rew_ratings (hard-coded to 1, never read).
 */
function resetRewRecommendationsTable(): array
{
    $messages = [];

    try {
        $tableName = (new Recommendation())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('recommend_id');

            $table->unsignedBigInteger('orig_user_id')->index();
            $table->unsignedBigInteger('dest_user_id')->index();

            $table->unsignedInteger('dest_country_id')->nullable();
            $table->unsignedInteger('dest_region_id')->nullable();
            $table->string('dest_city', 150)->nullable();
            $table->unsignedInteger('dest_user_type_id')->index();

            // The audience/group this recommendation is aimed at (e.g.
            // "recommended to Landlords") — a rew_users_types-style id.
            $table->unsignedInteger('rec_user_type_id')->index();

            $table->text('comment');

            $table->timestamps();

            $table->foreign('orig_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('dest_user_id')->references('id')->on('users')->onDelete('cascade');
        });

        $messages[] = "created {$tableName} table";

        $recommender = User::find(2);
        $recommended = User::find(1);

        if ($recommender && $recommended) {
            Recommendation::create([
                'orig_user_id' => $recommender->id,
                'dest_user_id' => $recommended->id,
                'dest_country_id' => $recommended->country_id,
                'dest_region_id' => $recommended->region_id,
                'dest_city' => $recommended->city,
                'dest_user_type_id' => 2,
                'rec_user_type_id' => 3,
                'comment' => 'Highly recommended for anyone renting in this area — professional and fair.',
            ]);

            $messages[] = "seeded initial gonachi recommendation";
        }
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

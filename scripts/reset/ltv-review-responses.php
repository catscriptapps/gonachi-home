<?php
// /scripts/reset/ltv-review-responses.php
//
// Right of response — exactly ONE professional response per review, allowed
// to the party who was reviewed. The unique() on review_id (not just an
// index) is a deliberate, hard DB-level rule: unlike this app's usual
// app-level-only dedup discipline (e.g. ltv_landlords.normalized_name), a
// second response here isn't a "soft duplicate directory entry" risk, it's
// a hard product rule ("no unlimited back-and-forth") worth a real constraint.

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\ReviewResponse;

function resetLtvReviewResponsesTable(): array
{
    $messages = [];

    try {
        $tableName = (new ReviewResponse())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('review_id')->unique();
            $table->unsignedBigInteger('responder_user_id')->index();
            $table->text('response');
            $table->timestamps();
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

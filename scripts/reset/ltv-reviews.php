<?php
// /scripts/reset/ltv-reviews.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Review;

function resetLtvReviewsTable(): array
{
    $messages = [];

    try {
        $tableName = (new Review())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('tenancy_id')->index();
            $table->unsignedBigInteger('reviewer_user_id')->index(); // must be a logged-in Gonachi user

            $table->string('reviewee_type')->index(); // landlord | tenant — which side is being rated
            $table->unsignedBigInteger('reviewee_landlord_id')->nullable()->index();
            $table->unsignedBigInteger('reviewee_tenant_id')->nullable()->index();

            $table->text('comment')->nullable(); // "Tell us about your experience"

            // Double-blind release lifecycle — independent of moderation.
            // See Src\Service\ReviewService::maybeRelease().
            $table->string('release_status')->default('pending')->index(); // pending | released
            $table->timestamp('released_at')->nullable();

            // Moderation lifecycle — independent of release. Disputes never
            // auto-hide; this only flips if an admin upholds a report (see
            // ltv-review-reports.php / ReviewModerationController).
            $table->string('moderation_status')->default('published')->index(); // published | removed_by_moderation

            // Jurisdiction snapshot at submission time — distinct from
            // tenancy.country_id so an already-submitted review's recorded
            // jurisdiction never silently changes if the tenancy/property is
            // corrected later.
            $table->unsignedInteger('country_id')->nullable()->index();

            $table->timestamps(); // created_at is the review date

            $table->unique(['tenancy_id', 'reviewer_user_id']); // one review per party per tenancy
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

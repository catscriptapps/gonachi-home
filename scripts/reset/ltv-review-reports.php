<?php
// /scripts/reset/ltv-review-reports.php
//
// "Report Review" disputes — a reported review never auto-hides, it just
// enters this queue (see Src\Controller\ReviewModerationController). Only
// flips the parent review's ltv_reviews.moderation_status if an admin
// upholds the report.

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\ReviewReport;

function resetLtvReviewReportsTable(): array
{
    $messages = [];

    try {
        $tableName = (new ReviewReport())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('review_id')->index();
            $table->unsignedBigInteger('reporter_user_id')->index();

            // One of the 8 fixed reason slugs from the PDF: no_tenancy_
            // relationship | false_information | discriminatory_hateful |
            // threats_harassment | private_personal_information |
            // irrelevant_information | conflict_of_interest | other
            $table->string('reason')->index();
            $table->text('details')->nullable(); // mainly used for "other"

            $table->string('status')->default('pending_review')->index(); // pending_review | upheld | dismissed
            $table->unsignedBigInteger('resolved_by_user_id')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();

            $table->timestamps();
            $table->index(['review_id', 'reporter_user_id']);
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

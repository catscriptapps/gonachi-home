<?php
// /scripts/reset/ltv-review-tags.php
//
// Structured tag selections on a review — validated server-side against the
// fixed PHP constant list in Src\Utils\ReviewTagCatalog (5 landlord + 5
// tenant tags, exact strings from landlord_and_tenant_validation.pdf), not a
// DB catalog table, since tags are fixed cosmetic strings with no
// weight/order to tune (unlike ltv_review_criteria).

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\ReviewTag;

function resetLtvReviewTagsTable(): array
{
    $messages = [];

    try {
        $tableName = (new ReviewTag())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('review_id')->index();
            $table->string('tag_key')->index();
            $table->timestamps();
            $table->unique(['review_id', 'tag_key']);
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

<?php
// /scripts/reset/rew-post-comments.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\PostComment;

function resetRewPostCommentsTable(): array
{
    $messages = [];

    try {
        $tableName = (new PostComment())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('post_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->text('comment_text');
            $table->timestamps();

            $table->foreign('post_id')->references('id')->on('rew_posts')->onDelete('cascade');
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

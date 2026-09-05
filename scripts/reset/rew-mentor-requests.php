<?php
// /scripts/reset/rew-mentor-requests.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\MentorRequest;

function resetRewMentorRequestsTable(): array
{
    $messages = [];

    try {
        $tableName = (new MentorRequest())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sender_id')->index();
            $table->unsignedBigInteger('mentor_id')->index();
            $table->string('status', 20)->default('pending');
            $table->text('message')->nullable();

            // Legacy carries the mentor's accept/decline reply only inside a
            // Notification row (see the reasoning behind this port). Since
            // gonachi-home has no notification system, that reply needs a
            // durable home of its own — this column is it.
            $table->text('response_message')->nullable();

            $table->timestamps();

            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('mentor_id')->references('id')->on('rew_mentors')->onDelete('cascade');
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

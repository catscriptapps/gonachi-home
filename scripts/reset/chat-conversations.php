<?php
// /scripts/reset/chat-conversations.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\ChatConversation;

function resetChatConversationsTable(): array
{
    $messages = [];

    try {
        $tableName = (new ChatConversation())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('guest_token')->nullable()->index();
            $table->string('guest_name')->nullable();
            $table->string('guest_email')->nullable();

            // open | closed
            $table->string('status')->default('open')->index();

            $table->timestamp('last_message_at')->nullable();

            $table->timestamps();
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

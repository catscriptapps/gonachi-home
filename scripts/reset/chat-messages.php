<?php
// /scripts/reset/chat-messages.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\ChatMessage;

function resetChatMessagesTable(): array
{
    $messages = [];

    try {
        $tableName = (new ChatMessage())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('conversation_id')->index();

            // visitor | admin
            $table->string('sender_role')->index();
            $table->unsignedBigInteger('sender_user_id')->nullable();

            // True for AI-generated admin replies (see Src\Service\ChatAutoResponder) —
            // distinguishes them from a real admin's own messages, which is how the
            // autoresponder knows a human has taken over a conversation.
            $table->boolean('is_ai')->default(false);

            $table->text('body');

            $table->boolean('is_read_by_admin')->default(false)->index();
            $table->boolean('is_read_by_visitor')->default(false)->index();

            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

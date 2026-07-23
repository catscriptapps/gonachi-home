<?php
// /scripts/reset/chat-ai-settings.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\ChatAiSetting;

/**
 * Singleton settings row for the live chat AI autoresponder (see
 * Src\Service\ChatAutoResponder) — the admin-facing on/off toggle plus the
 * business-context instructions used as its system prompt.
 */
function resetChatAiSettingsTable(): array
{
    $messages = [];

    try {
        $tableName = (new ChatAiSetting())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('enabled')->default(false);
            $table->text('instructions')->nullable();
            $table->timestamps();
        });

        $messages[] = "created {$tableName} table";

        ChatAiSetting::create(['enabled' => false, 'instructions' => '']);
        $messages[] = "seeded default {$tableName} row";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

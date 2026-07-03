<?php
// /scripts/reset/leads.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Lead;

function resetLeadsTable(): array
{
    $messages = [];

    try {
        $tableName = (new Lead())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('lead_source_id')->index();
            // Source-native identifier (post id, URL hash, etc.) used for dedup
            $table->string('external_id');
            $table->text('source_url')->nullable();
            $table->text('raw_text');

            // buyer | seller | investor | renter
            $table->string('request_type')->index();
            // residential | commercial | land
            $table->string('property_type')->nullable();
            $table->unsignedTinyInteger('bedrooms')->nullable();

            $table->unsignedBigInteger('location_id')->nullable()->index();
            $table->string('location_raw')->nullable();

            $table->decimal('budget_min', 14, 2)->nullable();
            $table->decimal('budget_max', 14, 2)->nullable();

            // high | medium | low
            $table->string('intent_level')->default('medium');

            // Only populated when the source publicly displayed contact info
            $table->text('contact_info_raw')->nullable();

            // pending_review | active | expired | rejected
            $table->string('status')->default('pending_review')->index();

            $table->unsignedBigInteger('category_id')->nullable()->index();

            $table->timestamp('posted_at')->nullable();
            $table->timestamp('scraped_at')->useCurrent();

            $table->timestamps();

            $table->unique(['lead_source_id', 'external_id']);
            $table->index(['category_id', 'location_id', 'status']);
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

<?php
// /scripts/reset/ltv-tenancies.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Tenancy;

function resetLtvTenanciesTable(): array
{
    $messages = [];

    try {
        $tableName = (new Tenancy())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('landlord_id')->index();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('property_id')->nullable()->index();
            $table->unsignedBigInteger('initiated_by_user_id')->index();

            // Mutual-confirmation inputs — see Src\Service\TenancyService
            // ::recomputeVerification(). Per-tenancy, not per-directory-entry,
            // since one landlord has many tenancies confirmed separately.
            $table->timestamp('landlord_confirmed_at')->nullable();
            $table->timestamp('tenant_confirmed_at')->nullable();

            $table->string('status')->default('active')->index(); // active | ended | archived
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // Jurisdiction — see Src\Utils\CountryScope. Snapshot here so it
            // survives even if the linked property's own country_id is
            // edited later (reviews snapshot it again independently, see
            // ltv-reviews.php).
            $table->unsignedInteger('country_id')->nullable()->index();

            // Cached/derived — recomputed by TenancyService whenever an
            // input changes, never hand-edited.
            $table->boolean('is_verified')->default(false)->index();
            $table->string('verification_method')->nullable(); // mutual_confirmation | property_association | null

            // Double-blind review window — opened the moment the FIRST
            // review (either direction) is submitted for this tenancy.
            $table->timestamp('review_window_opened_at')->nullable()->index();
            $table->timestamp('review_window_closes_at')->nullable()->index();

            $table->timestamps();

            $table->index(['landlord_id', 'tenant_id']);
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

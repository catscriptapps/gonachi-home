<?php
// /scripts/reset/ltv-tenant-reports.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\TenantReport;

function resetLtvTenantReportsTable(): array
{
    $messages = [];

    try {
        $tableName = (new TenantReport())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('user_id');
            $table->string('property_address')->nullable();
            // No property FK to piggyback on (property_address is free
            // text) so tenant reports carry their own country_id directly —
            // see Src\Utils\CountryScope.
            $table->unsignedInteger('country_id')->nullable()->index();
            $table->string('duration_of_tenancy')->nullable();
            $table->string('conduct_type');
            $table->text('notes')->nullable();
            $table->unsignedTinyInteger('rating');
            $table->string('reference_name')->nullable();
            // Optional contact for a reference (e.g. a previous landlord) —
            // backfilled onto ltv_tenants.reference_phone once APPROVED, the
            // "Tenant Records" contact LandlordCreditService::
            // unlockTenantReport() reveals. Mirrors ltv_reports.landlord_phone.
            $table->string('reference_phone')->nullable();
            $table->string('status')->default('pending_review');
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('status');
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

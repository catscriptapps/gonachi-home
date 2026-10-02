<?php
// /scripts/reset/ltv-tenants.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\TenantRecord;

function resetLtvTenantsTable(): array
{
    $messages = [];

    try {
        $tableName = (new TenantRecord())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            // trimmed/collapsed-whitespace/lowercased — used for find-or-create dedup
            $table->string('normalized_name')->index();
            // Best-known reference contact for this tenant (e.g. a previous
            // landlord's phone) — backfilled from the first APPROVED report
            // that included one, never overwritten. This is the "Tenant
            // Records" contact LandlordCreditService::unlockTenantReport()
            // reveals — see landlord_and_tenant_validation.pdf's Step 8 and
            // Tenant Profile's "References" field.
            $table->string('reference_phone')->nullable();
            // Set when this directory entry is matched to a real Gonachi
            // account — required for a Tenancy to ever reach
            // mutual_confirmation verification, see
            // Src\Service\TenancyService::recomputeVerification().
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->timestamps();
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

<?php
// /scripts/reset/ltv-landlords.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\LandlordRecord;

function resetLtvLandlordsTable(): array
{
    $messages = [];

    try {
        $tableName = (new LandlordRecord())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            // trimmed/collapsed-whitespace/lowercased — used for find-or-create dedup
            $table->string('normalized_name')->index();
            // Best-known phone for this landlord — backfilled from the first
            // APPROVED report that included one (LandlordReportReviewController
            // ::approve()), never overwritten by a later report so one bad/
            // wrong submission can't clobber an already-corroborated number.
            // This is the "Contact Details" LandlordCreditService unlocks —
            // see landlord_and_tenant_validation.pdf's Step 8.
            $table->string('phone')->nullable();
            // Set when this directory entry is matched to a real Gonachi
            // account (e.g. the landlord claimed/registered, or was the
            // logged-in user who initiated a tenancy as the landlord side) —
            // required for a Tenancy to ever reach mutual_confirmation
            // verification, see Src\Service\TenancyService::recomputeVerification().
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->timestamps();
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

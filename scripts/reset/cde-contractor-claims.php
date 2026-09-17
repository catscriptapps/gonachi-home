<?php
// /scripts/reset/cde-contractor-claims.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\ContractorClaim;

function resetCdeContractorClaimsTable(): array
{
    $messages = [];

    try {
        $tableName = (new ContractorClaim())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('contractor_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->text('message')->nullable();
            $table->string('contact_phone');

            // Photo of a business document (registration cert, letterhead,
            // signage, etc.) showing the exact business name — required at
            // submission time (ContractorClaimController::submit()) so the
            // admin review queue always has something concrete to verify
            // the claimant's identity against, mirroring how Google's own
            // business-claim flow requires video/document verification.
            $table->string('document_path');

            // pending | approved | rejected
            $table->string('status')->default('pending')->index();

            $table->timestamps();
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

<?php
// /scripts/reset/ltv-report-photos.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\LandlordReportPhoto;

function resetLtvReportPhotosTable(): array
{
    $messages = [];

    try {
        $tableName = (new LandlordReportPhoto())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('report_id')->index();
            // building_picture | supporting_evidence
            $table->string('kind');
            $table->string('file_path');
            $table->timestamps();
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

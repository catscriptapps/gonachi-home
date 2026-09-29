<?php
// /scripts/reset/pending-photo-uploads.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\PendingPhotoUpload;

function resetPendingPhotoUploadsTable(): array
{
    $messages = [];

    try {
        $tableName = (new PendingPhotoUpload())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            // e.g. 'images/uploads/adverts/xyz.jpg' — the asset-base-relative
            // path, same shape a *PicturesController::replacePhotos() strips
            // out of a submitted photo_urls entry.
            $table->string('relative_path')->unique();
            $table->timestamp('created_at')->useCurrent();
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

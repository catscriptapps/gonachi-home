<?php
// /scripts/reset/rew-advert-pics.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\AdvertPic;

function resetRewAdvertPicsTable(): array
{
    $messages = [];

    try {
        $tableName = (new AdvertPic())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('advert_id')->index();
            $table->text('pic_name')->nullable();
            $table->text('pic_caption')->nullable();
            $table->integer('pos_index')->default(0);
            $table->timestamps();

            $table->foreign('advert_id')->references('id')->on('rew_adverts')->onDelete('cascade');
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

<?php
// /scripts/reset/rew-quotation-pics.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\QuotationPic;

function resetRewQuotationPicsTable(): array
{
    $messages = [];

    try {
        $tableName = (new QuotationPic())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->id('entry_id');
            $table->unsignedBigInteger('quotation_id')->nullable()->index();
            $table->text('pic_name')->nullable();
            $table->integer('pos_index')->default(0);
            $table->timestamps();

            $table->foreign('quotation_id')->references('quotation_id')->on('rew_quotations')->onDelete('cascade');
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

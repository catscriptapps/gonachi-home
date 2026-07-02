<?php
// /scripts/reset/properties-pics.php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\PropertyPic;

/**
 * Resets the properties_pics table structure.
 */
function resetPropertyPicsTable(): array
{
    $messages = [];

    try {
        $model = new PropertyPic();
        $tableName = $model->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table.";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->id('entry_id');
            $table->unsignedInteger('property_id')->index();
            $table->string('pic_name', 300);
            $table->string('pic_caption', 300)->nullable();
            $table->integer('pos_index')->default(0);
            $table->timestamps();

            $table->foreign('property_id')
                ->references('id')
                ->on('properties')
                ->onDelete('cascade');
        });

        $messages[] = "created {$tableName} table structure (no seeding).";
    } catch (\Throwable $e) {
        $messages[] = 'properties pics table error: ' . $e->getMessage();
    }

    return $messages;
}

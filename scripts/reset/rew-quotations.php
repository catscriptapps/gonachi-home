<?php
// /scripts/reset/rew-quotations.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Quotation;
use App\Models\User;

function resetRewQuotationsTable(): array
{
    $messages = [];

    try {
        $tableName = (new Quotation())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('quotation_id');

            $table->unsignedBigInteger('orig_user_id')->nullable()->index();

            $table->unsignedInteger('country_id')->nullable()->index();
            $table->unsignedInteger('region_id')->nullable()->index();
            $table->unsignedInteger('contractor_type_id')->nullable()->index();
            $table->unsignedInteger('skilled_trade_id')->nullable()->index();

            $table->string('quotation_title', 300);
            $table->string('city', 300)->nullable();
            $table->text('description_of_work_to_be_done')->nullable();

            $table->unsignedInteger('unit_type_id')->nullable();
            $table->unsignedInteger('house_type_id')->nullable();

            $table->string('start_date', 12)->nullable();
            $table->string('finish_date', 12)->nullable();
            $table->string('start_time', 10)->nullable();
            $table->string('finish_time', 10)->nullable();

            $table->string('quotation_budget', 100)->nullable();
            $table->unsignedInteger('quotation_type_id')->nullable();
            $table->unsignedInteger('quotation_dest_id')->nullable();

            $table->text('youtube_url')->nullable();
            $table->string('contact_phone', 30)->nullable();
            $table->integer('views')->default(0);

            // 1 = Active, 2 = Archived/Deactivated (owner self-service toggle,
            // matching legacy — no admin approval workflow for quotations).
            $table->unsignedInteger('status_id')->default(1)->index();

            $table->timestamps();

            $table->foreign('orig_user_id')->references('id')->on('users')->onDelete('cascade');
        });

        $messages[] = "created {$tableName} table";

        $owner = User::find(1);

        Quotation::create([
            'orig_user_id' => 1,
            'country_id' => $owner->country_id ?? 1,
            'region_id' => $owner->region_id ?? 0,
            'contractor_type_id' => 1,
            'skilled_trade_id' => 27,
            'quotation_title' => 'Kitchen Renovation Quote Needed',
            'city' => $owner->city ?? '',
            'description_of_work_to_be_done' => 'Looking for a licensed contractor to fully renovate a mid-size kitchen — cabinets, countertops, plumbing, and electrical work included.',
            'unit_type_id' => 5,
            'house_type_id' => 1,
            'start_date' => date('Y-m-d', strtotime('+2 weeks')),
            'finish_date' => date('Y-m-d', strtotime('+6 weeks')),
            'start_time' => '09:00',
            'finish_time' => '17:00',
            'quotation_budget' => '$8,000 - $15,000',
            'quotation_type_id' => 2,
            'quotation_dest_id' => 2,
            'views' => 0,
            'status_id' => 1,
        ]);

        $messages[] = "seeded initial gonachi quotation";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

<?php
// /scripts/reset/rew-adverts.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Advert;
use App\Models\AdvertCta;

function resetRewAdvertsTable(): array
{
    $messages = [];

    try {
        $tableName = (new Advert())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable()->index();

            $table->string('title');
            $table->text('description');
            $table->unsignedInteger('cta_id')->nullable();
            $table->text('keywords')->nullable();
            $table->string('landing_page_url')->nullable();

            // JSON-encoded arrays (Eloquent 'array' cast) — country/user-type
            // IDs as strings/ints, or the literal ['ALL'] sentinel.
            $table->text('selected_countries')->nullable();
            $table->text('selected_user_types')->nullable();

            $table->unsignedInteger('package_id')->default(Advert::PACKAGE_FREE)->index();
            $table->string('status')->default(Advert::STATUS_PENDING)->index();
            $table->integer('views')->default(0);
            $table->timestamp('expires_at')->nullable();

            // At most one video per advert (not legacy — added at the
            // user's request, alongside the existing 12-picture cap).
            // Uploading always replaces whichever video is already there,
            // which is what naturally enforces the "1 video" limit.
            $table->string('video_name')->nullable();

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        $messages[] = "created {$tableName} table";

        // One clean, live "welcome" ad so the public browse feed isn't empty
        // out of the box — same intent as the legacy seed, minus its bugs
        // (lowercase 'all' instead of 'ALL', and a call_to_action field that
        // isn't actually fillable on the model).
        $ctaId = AdvertCta::where('label', 'Get Started')->value('id');

        Advert::create([
            'user_id' => 1,
            'title' => 'Welcome to Gonachi Adverts',
            'description' => 'Start promoting your real estate business today to thousands of targeted users.',
            'cta_id' => $ctaId,
            'keywords' => 'real estate, advertising, property',
            'landing_page_url' => 'https://gonachi.com',
            'selected_countries' => ['ALL'],
            'selected_user_types' => ['ALL'],
            'package_id' => Advert::PACKAGE_FREE,
            'status' => Advert::STATUS_ACTIVE,
            'expires_at' => null,
        ]);

        $messages[] = "seeded initial gonachi advert";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

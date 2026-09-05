<?php
// /scripts/reset/rew-mentors.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Mentor;
use App\Models\User;

function resetRewMentorsTable(): array
{
    $messages = [];

    try {
        $tableName = (new Mentor())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('orig_user_id')->nullable()->index();

            $table->unsignedInteger('country_id')->nullable()->index();
            $table->unsignedInteger('region_id')->nullable()->index();
            $table->string('city', 150)->nullable();

            // The audience this mentor wants to help (e.g. "Tenants", "Landlords").
            $table->unsignedInteger('target_user_type_id')->nullable()->index();

            $table->string('headline', 300)->nullable();
            $table->text('bio')->nullable();
            $table->json('skills')->nullable();
            $table->unsignedInteger('years_experience')->default(0);

            $table->string('youtube_url', 500)->nullable();
            $table->string('website_url', 500)->nullable();

            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();

            $table->foreign('orig_user_id')->references('id')->on('users')->onDelete('cascade');
        });

        $messages[] = "created {$tableName} table";

        $owner = User::find(1);

        Mentor::create([
            'orig_user_id' => 1,
            'country_id' => $owner->country_id ?? 1,
            'region_id' => $owner->region_id ?? 0,
            'city' => $owner->city ?? '',
            'target_user_type_id' => 2,
            'headline' => 'Real Estate Investment & Property Management Coach',
            'bio' => 'Over a decade helping landlords and new investors build sustainable rental portfolios — from first purchase to full-time property management.',
            'skills' => ['Property Management', 'Landlord Coaching', 'Real Estate Investing'],
            'years_experience' => 12,
            'youtube_url' => '',
            'website_url' => 'https://gonachi.com',
            'is_active' => true,
        ]);

        $messages[] = "seeded initial gonachi mentor";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

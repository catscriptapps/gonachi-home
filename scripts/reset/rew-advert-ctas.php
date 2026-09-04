<?php
// /scripts/reset/rew-advert-ctas.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\AdvertCta;

function resetRewAdvertCtasTable(): array
{
    $messages = [];

    try {
        $tableName = (new AdvertCta())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('label', 100);
            $table->timestamps();
        });

        $messages[] = "created {$tableName} table";

        // Exact list from the legacy platform's call-to-action presets.
        $labels = [
            'Request Quotation', 'Hire Now', 'Book Now', 'Contact Us', 'Get Offer',
            'Get Quote', 'Learn More', 'Ask for Price', 'Message Us', 'Contact for Price',
            'Call Us', 'Schedule a Viewing', 'Inquire Now', 'Call Us Today', 'Request a Demo',
            'Sign Up Now', 'Explore Services', 'Discover More', 'Start Free Trial',
            'Explore Options', 'Get Started', 'Shop Now', 'Join Now',
        ];

        foreach ($labels as $label) {
            AdvertCta::create(['label' => $label]);
        }

        $messages[] = "seeded " . count($labels) . " call-to-action options";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

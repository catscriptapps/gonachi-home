<?php
// /scripts/reset/rel-seed.php
//
// Baseline lookup data for the real-estate-leads project: locations,
// categories, and the two starter lead_sources rows. Shared by both
// server/api/reset.php (the in-app admin reset flow) and
// scripts/setup-database.php (the CLI equivalent), so seed data only
// lives in one place.

declare(strict_types=1);

use App\Models\Location;
use App\Models\LeadCategory;
use App\Models\LeadSource;
use Illuminate\Support\Str;

function seedRelLeadsBaselineData(): array
{
    $messages = [];

    // --------------------------------------------------
    // Locations (Nigeria > state > area)
    // --------------------------------------------------
    $nigeria = Location::create(['name' => 'Nigeria', 'slug' => 'nigeria']);

    $states = [
        'Lagos' => ['Lekki', 'Ikeja', 'Ajah', 'Yaba', 'Ikoyi', 'Victoria Island'],
        'Abuja' => ['Gwarinpa', 'Wuse', 'Maitama', 'Gwagwalada'],
        'Port Harcourt' => ['GRA', 'Trans Amadi'],
        'Ibadan' => [],
    ];

    foreach ($states as $stateName => $areas) {
        $state = Location::create([
            'name' => $stateName,
            'slug' => Str::slug($stateName),
            'parent_id' => $nigeria->id,
        ]);

        foreach ($areas as $area) {
            Location::create([
                'name' => $area,
                'slug' => Str::slug($area),
                'parent_id' => $state->id,
            ]);
        }
    }

    $messages[] = 'seeded ' . Location::count() . ' locations';

    // --------------------------------------------------
    // Lead categories
    // --------------------------------------------------
    $categories = [
        ['name' => 'Home Buyers', 'request_type' => 'buyer', 'property_type' => 'residential'],
        ['name' => 'Home Sellers', 'request_type' => 'seller', 'property_type' => 'residential'],
        ['name' => 'Land Buyers', 'request_type' => 'buyer', 'property_type' => 'land'],
        ['name' => 'Land Sellers', 'request_type' => 'seller', 'property_type' => 'land'],
        ['name' => 'Commercial Property Buyers', 'request_type' => 'buyer', 'property_type' => 'commercial'],
        ['name' => 'Property Investors', 'request_type' => 'investor', 'property_type' => null],
        ['name' => 'Renters', 'request_type' => 'renter', 'property_type' => null],
    ];

    foreach ($categories as $category) {
        LeadCategory::create([
            'name' => $category['name'],
            'slug' => Str::slug($category['name']),
            'request_type' => $category['request_type'],
            'property_type' => $category['property_type'],
        ]);
    }

    $messages[] = 'seeded ' . LeadCategory::count() . ' lead categories';

    // --------------------------------------------------
    // Starter lead sources
    // --------------------------------------------------
    LeadSource::create([
        'name' => 'Nairaland Properties Board',
        'slug' => 'nairaland-properties',
        'type' => 'forum',
        'connector_class' => \Src\Service\LeadSources\NairalandPropertiesConnector::class,
        'base_url' => 'https://www.nairaland.com/properties',
        'config' => ['board_url' => 'https://www.nairaland.com/properties'],
        'is_active' => true,
        'poll_interval_minutes' => 60,
    ]);

    LeadSource::create([
        'name' => 'Google Programmable Search (Real Estate Intent)',
        'slug' => 'google-cse-intent',
        'type' => 'search_api',
        'connector_class' => \Src\Service\LeadSources\GoogleCseConnector::class,
        'base_url' => 'https://www.googleapis.com/customsearch/v1',
        'config' => [
            'api_key_env' => 'GOOGLE_CSE_API_KEY',
            'cse_id_env' => 'GOOGLE_CSE_ID',
            'queries' => [
                'looking for 3 bedroom house Lagos',
                'looking for land to buy Lagos',
                'looking for house to rent Abuja',
                'seeking investment property Nigeria',
            ],
        ],
        // Inactive until GOOGLE_CSE_API_KEY / GOOGLE_CSE_ID are set in .env —
        // flip to true once credentials exist.
        'is_active' => false,
        'poll_interval_minutes' => 1440,
    ]);

    $messages[] = 'seeded ' . LeadSource::count() . ' lead sources';

    return $messages;
}

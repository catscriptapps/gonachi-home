<?php
// /scripts/reset/swp-seed.php
//
// Baseline categories + demo listings so the Swap home page isn't empty on
// a fresh install. Categories are the real gonachi-swap product taxonomy
// (ported from its own listing_categories seed); demo listings reuse the
// curated home-page photos as stand-in thumbnails, same convention as
// every other project's baseline seed (e.g. ltv-seed.php's "Mr X").

declare(strict_types=1);

use App\Models\SwapListing;
use App\Models\SwapListingCategory;
use App\Models\SwapListingPic;

function seedSwpBaselineData(): array
{
    $messages = [];

    $categoryNames = [
        'Vehicles',
        'Fashion & Clothing',
        'Electronics & Gadgets',
        'Entertainment',
        'Pet Supplies',
        'Musical Instruments',
        'Toys & Games',
        'Home & Living',
        'Beauty & Personal Care',
        'Health & Wellness',
        'Sports & Outdoors',
    ];

    $categories = [];
    foreach ($categoryNames as $name) {
        $categories[$name] = SwapListingCategory::create([
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
        ]);
    }

    $messages[] = 'seeded ' . count($categories) . ' listing categories';

    $demoListings = [
        [
            'category' => 'Electronics & Gadgets',
            'title' => 'Dell XPS 13 Laptop (2022)',
            'description' => 'Barely used, still under warranty. Looking to swap for a games console or a good camera.',
            'listing_type' => 'swap',
            'condition' => 'like_new',
            'trade_pref' => 'Games console, DSLR camera, or a solid offer.',
            'city' => 'Lekki, Lagos',
            'photo' => '1.jpg',
        ],
        [
            'category' => 'Home & Living',
            'title' => '6-Seater Dining Table Set',
            'description' => 'Solid wood dining set, minor scuff on one leg. Moving out and need it gone this week.',
            'listing_type' => 'sale',
            'condition' => 'used',
            'price' => 85000,
            'city' => 'Ikeja, Lagos',
            'photo' => '2.jpg',
        ],
        [
            'category' => 'Fashion & Clothing',
            'title' => 'Box Of Kids Clothes (Ages 4-6)',
            'description' => 'Outgrown but in great condition. Happy to give this away to a family that needs it.',
            'listing_type' => 'gift',
            'condition' => 'used',
            'city' => 'Port Harcourt',
            'photo' => '3.JPG',
        ],
        [
            'category' => 'Musical Instruments',
            'title' => 'Yamaha Acoustic Guitar',
            'description' => 'Great beginner guitar, comes with a soft case. Open to swapping for a keyboard.',
            'listing_type' => 'swap',
            'condition' => 'used',
            'trade_pref' => 'Keyboard/synth, or audio equipment.',
            'city' => 'Abuja',
        ],
    ];

    $listingCount = 0;
    foreach ($demoListings as $data) {
        $listing = SwapListing::create([
            'user_id' => 1,
            'category_id' => $categories[$data['category']]->id ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'listing_type' => $data['listing_type'],
            'condition' => $data['condition'],
            'price' => $data['price'] ?? null,
            'trade_pref' => $data['trade_pref'] ?? null,
            'city' => $data['city'] ?? null,
            'status' => 'posted',
        ]);

        if (!empty($data['photo'])) {
            SwapListingPic::create([
                'listing_id' => $listing->id,
                'file_path' => 'images/home/' . $data['photo'],
                'position' => 0,
            ]);
        }

        $listingCount++;
    }

    $messages[] = "seeded {$listingCount} demo listings";

    return $messages;
}

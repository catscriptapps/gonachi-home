<?php
// /scripts/reset/cde-seed.php
//
// Baseline records for the contractor-discovery project so its pages
// aren't empty on a fresh install — matches the original UI mockup
// exactly (Plumber Needed / Lekki / ₦150,000, Johnson Plumbing Services,
// Elite Builders Ltd). Contractor records are clearly placeholder data —
// no scraping pipeline exists yet (see contractor_discovery.pdf's Phase 1),
// so an admin is expected to add real contractors over time.

declare(strict_types=1);

use App\Models\JobRequest;
use App\Models\Contractor;

function seedCdeBaselineData(): array
{
    $messages = [];

    JobRequest::create([
        'user_id' => 1,
        'service_category' => 'plumbing',
        'location' => 'Lekki, Lagos',
        'budget' => 150000,
        'description' => 'Kitchen sink pipe burst and is leaking under the cabinet. Need someone available today or tomorrow morning.',
        'timeline' => 'ASAP',
        'contact_phone' => '08000000000',
        'status' => 'open',
    ]);

    $messages[] = 'seeded 1 baseline job request record';

    $contractors = [
        [
            'business_name' => 'Johnson Plumbing Services',
            'service_category' => 'plumbing',
            'location' => 'Lekki, Lagos',
            'operating_areas' => 'Lekki, Ajah, Victoria Island',
            'phone' => '08011111111',
            'description' => 'Residential and commercial plumbing repairs, installations, and emergency call-outs across Lekki and Ajah.',
            'rating' => 4.8,
            'review_count' => 32,
            'claimed_by_user_id' => 1,
            'claim_status' => 'claimed',
        ],
        [
            'business_name' => 'Elite Builders Ltd',
            'service_category' => 'building_construction',
            'location' => 'Ikeja, Lagos',
            'operating_areas' => null,
            'phone' => null,
            'description' => null,
            'rating' => null,
            'review_count' => 0,
            'claimed_by_user_id' => null,
            'claim_status' => 'unclaimed',
        ],
        [
            'business_name' => 'BrightSpark Electricals',
            'service_category' => 'electrical',
            'location' => 'Ikeja, Lagos',
            'operating_areas' => 'Ikeja, Magodo, Ogba',
            'phone' => '08022222222',
            'description' => 'Wiring, rewiring, and electrical fault diagnosis for homes and small offices.',
            'rating' => 4.5,
            'review_count' => 18,
            'claimed_by_user_id' => null,
            'claim_status' => 'unclaimed',
        ],
        [
            'business_name' => 'ColorCraft Painters',
            'service_category' => 'painting',
            'location' => 'Lekki, Lagos',
            'operating_areas' => 'Lekki, VI, Ikoyi',
            'phone' => null,
            'description' => 'Interior and exterior painting, texture finishes, and waterproof coatings.',
            'rating' => null,
            'review_count' => 0,
            'claimed_by_user_id' => null,
            'claim_status' => 'unclaimed',
        ],
        [
            'business_name' => 'Solaris Power Solutions',
            'service_category' => 'solar_installation',
            'location' => 'Abuja FCT',
            'operating_areas' => 'Abuja, Gwarinpa, Wuse',
            'phone' => '08033333333',
            'description' => 'Solar panel installation and inverter/battery setups for homes and businesses.',
            'rating' => 4.9,
            'review_count' => 11,
            'claimed_by_user_id' => null,
            'claim_status' => 'unclaimed',
        ],
        [
            'business_name' => 'Coastal Roofing & Renovation',
            'service_category' => 'renovation',
            'location' => 'Port Harcourt',
            'operating_areas' => null,
            'phone' => null,
            'description' => null,
            'rating' => null,
            'review_count' => 0,
            'claimed_by_user_id' => null,
            'claim_status' => 'unclaimed',
        ],
        [
            'business_name' => 'Interior Edge Design Studio',
            'service_category' => 'interior_design',
            'location' => 'Ikoyi, Lagos',
            'operating_areas' => 'Ikoyi, VI, Lekki',
            'phone' => '08044444444',
            'description' => 'Full interior design and fit-out services for apartments and offices.',
            'rating' => 4.6,
            'review_count' => 9,
            'claimed_by_user_id' => null,
            'claim_status' => 'unclaimed',
        ],
    ];

    foreach ($contractors as $contractor) {
        Contractor::create($contractor + ['status' => 'active']);
    }

    $messages[] = 'seeded ' . count($contractors) . ' baseline contractor records';

    return $messages;
}

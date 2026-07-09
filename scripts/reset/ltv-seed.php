<?php
// /scripts/reset/ltv-seed.php
//
// Baseline record for the landlord-tenant-validation project so the landing
// page isn't empty on a fresh install — matches the original UI mockup
// exactly (House 14, Lekki / Mr X / 1 published report / 40% confidence).

declare(strict_types=1);

use App\Models\LandlordRecord;
use App\Models\PropertyRecord;
use App\Models\LandlordReport;

function seedLtvBaselineData(): array
{
    $messages = [];

    $landlord = LandlordRecord::create([
        'name' => 'Mr X',
        'normalized_name' => 'mr x',
    ]);

    $property = PropertyRecord::create([
        'landlord_id' => $landlord->id,
        'address' => 'House 14, Lekki',
        'normalized_address' => 'house 14, lekki',
        'property_type' => 'flat',
    ]);

    LandlordReport::create([
        'property_id' => $property->id,
        'landlord_id' => $landlord->id,
        'user_id' => 1,
        'duration_of_tenancy' => '1 year',
        'issue_type' => 'deposit',
        'notes' => 'Deposit withheld at the end of tenancy without explanation.',
        'status' => 'published',
    ]);

    $messages[] = 'seeded 1 baseline landlord/property/report record';

    return $messages;
}

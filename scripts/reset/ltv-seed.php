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
use App\Models\TenantRecord;
use App\Models\TenantReport;

function seedLtvBaselineData(): array
{
    $messages = [];

    $landlord = LandlordRecord::create([
        'name' => 'Mr X',
        'normalized_name' => 'mr x',
        'phone' => '08011112222',
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
        'rating' => 2,
        'status' => 'published',
    ]);

    $messages[] = 'seeded 1 baseline landlord/property/report record';

    // Mirror-image baseline so the landing page's tenant side isn't empty
    // either — matches the same "1 published report" shape as Mr X above.
    $tenant = TenantRecord::create([
        'name' => 'Miss Y',
        'normalized_name' => 'miss y',
        'reference_phone' => '08033334444',
    ]);

    TenantReport::create([
        'tenant_id' => $tenant->id,
        'user_id' => 1,
        'property_address' => 'House 14, Lekki',
        'duration_of_tenancy' => '8 months',
        'conduct_type' => 'payment_default',
        'notes' => 'Rent was consistently paid 2-3 weeks late.',
        'rating' => 3,
        'reference_name' => 'Mr X',
        'reference_phone' => '08033334444',
        'status' => 'published',
    ]);

    $messages[] = 'seeded 1 baseline tenant/report record';

    return $messages;
}

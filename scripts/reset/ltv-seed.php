<?php
// /scripts/reset/ltv-seed.php
//
// Baseline data for the landlord-tenant-validation project so a fresh
// install's landing page and the new landlord/tenant profile pages aren't
// empty: one fully-scored, mutually-confirmed, published review in EACH
// direction (landlord -> tenant and tenant -> landlord) for a single shared
// Tenancy — "Mr X" (landlord, user 1) <-> "Miss Y" (tenant, user 2) at
// "House 14, Lekki". Exercises the real shape end to end (weighted
// criteria, tags, mutual-confirmation verification, double-blind release)
// rather than the old single-overall-rating seed this replaces.

declare(strict_types=1);

use App\Models\LandlordRecord;
use App\Models\PropertyRecord;
use App\Models\Review;
use App\Models\ReviewCriterion;
use App\Models\ReviewCriterionScore;
use App\Models\ReviewTag;
use App\Models\Tenancy;
use App\Models\TenantRecord;
use Carbon\Carbon;
use Src\Utils\CountryScope;

function seedLtvBaselineData(): array
{
    $messages = [];

    $nigeria = CountryScope::IDS['ng'];

    $landlord = LandlordRecord::create([
        'name' => 'Mr X',
        'normalized_name' => 'mr x',
        'phone' => '08011112222',
        'user_id' => 1,
    ]);

    $tenant = TenantRecord::create([
        'name' => 'Miss Y',
        'normalized_name' => 'miss y',
        'reference_phone' => '08033334444',
        'user_id' => 2,
    ]);

    $property = PropertyRecord::create([
        'landlord_id' => $landlord->id,
        'address' => 'House 14, Lekki',
        'normalized_address' => 'house 14, lekki',
        'property_type' => 'flat',
        'country_id' => $nigeria,
    ]);

    $tenancy = Tenancy::create([
        'landlord_id' => $landlord->id,
        'tenant_id' => $tenant->id,
        'property_id' => $property->id,
        'initiated_by_user_id' => 1,
        'landlord_confirmed_at' => Carbon::now(),
        'tenant_confirmed_at' => Carbon::now(),
        'status' => 'ended',
        'country_id' => $nigeria,
        'is_verified' => true,
        'verification_method' => 'mutual_confirmation',
        'review_window_opened_at' => Carbon::now(),
        'review_window_closes_at' => Carbon::now()->addDays(30),
    ]);

    // Tenant reviewing landlord (reviewee_type = landlord), all 8 criteria,
    // no N/As — mostly-positive scores with one area dinged, matching the
    // original seed's "deposit withheld" flavor.
    $landlordScores = [
        'property_condition_maintenance' => 5,
        'repairs_responsiveness' => 4,
        'communication' => 5,
        'lease_financial_transparency' => 2,
        'privacy_entry_practices' => 5,
        'safety_security_response' => 4,
        'issue_resolution' => 3,
        'move_in_move_out_experience' => 5,
    ];
    $review1 = Review::create([
        'tenancy_id' => $tenancy->id,
        'reviewer_user_id' => 2,
        'reviewee_type' => 'landlord',
        'reviewee_landlord_id' => $landlord->id,
        'comment' => 'Great communication and a well-kept property overall, but the deposit was withheld at the end of the tenancy without explanation.',
        'release_status' => 'released',
        'released_at' => Carbon::now(),
        'country_id' => $nigeria,
    ]);
    foreach (ReviewCriterion::forSubject('landlord') as $criterion) {
        ReviewCriterionScore::create([
            'review_id' => $review1->id,
            'criterion_id' => $criterion->id,
            'stars' => $landlordScores[$criterion->key],
            'is_na' => false,
        ]);
    }
    foreach (['professional_communication', 'well_maintained_property'] as $tagKey) {
        ReviewTag::create(['review_id' => $review1->id, 'tag_key' => $tagKey]);
    }

    // Landlord reviewing tenant (reviewee_type = tenant), all 8 criteria.
    $tenantScores = [
        'payment_reliability' => 3,
        'property_care' => 4,
        'communication' => 4,
        'lease_compliance' => 4,
        'maintenance_reporting' => 5,
        'reasonable_access_cooperation' => 5,
        'tenancy_community_conduct' => 4,
        'move_out_cooperation' => 4,
    ];
    $review2 = Review::create([
        'tenancy_id' => $tenancy->id,
        'reviewer_user_id' => 1,
        'reviewee_type' => 'tenant',
        'reviewee_tenant_id' => $tenant->id,
        'comment' => 'Rent was consistently paid a little late, but otherwise a conscientious tenant who took good care of the property and was easy to work with.',
        'release_status' => 'released',
        'released_at' => Carbon::now(),
        'country_id' => $nigeria,
    ]);
    foreach (ReviewCriterion::forSubject('tenant') as $criterion) {
        ReviewCriterionScore::create([
            'review_id' => $review2->id,
            'criterion_id' => $criterion->id,
            'stars' => $tenantScores[$criterion->key],
            'is_na' => false,
        ]);
    }
    foreach (['property_well_maintained', 'cooperative_with_maintenance'] as $tagKey) {
        ReviewTag::create(['review_id' => $review2->id, 'tag_key' => $tagKey]);
    }

    $messages[] = 'seeded 1 baseline tenancy with 2 published reviews (landlord <-> tenant)';

    return $messages;
}

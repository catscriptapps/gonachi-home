<?php
// /scripts/reset/ltv-review-criteria.php
//
// Static catalog of the 16 rating criteria from landlord_and_tenant_validation.pdf
// (8 landlord + 8 tenant, weights summing to 100% per subject_type). This is
// the ONLY source of ratable dimensions in the whole review system — the PDF's
// requirement that rating categories never touch protected characteristics
// (race, religion, disability, etc.) is satisfied structurally, since nothing
// outside this exact seeded list can ever be scored. Creates AND seeds in one
// call, since the catalog is baseline data, not user content.

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\ReviewCriterion;

function resetLtvReviewCriteriaTable(): array
{
    $messages = [];

    try {
        $tableName = (new ReviewCriterion())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('subject_type')->index(); // landlord | tenant
            $table->string('key');
            $table->string('label');
            $table->text('prompt');
            $table->unsignedTinyInteger('weight');
            $table->unsignedTinyInteger('sort_order');
            $table->timestamps();

            $table->unique(['subject_type', 'key']);
        });

        $messages[] = "created {$tableName} table";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
        return $messages;
    }

    $landlordCriteria = [
        ['key' => 'property_condition_maintenance', 'label' => 'Property Condition & Maintenance', 'prompt' => 'How well was the property maintained during your tenancy?', 'weight' => 20],
        ['key' => 'repairs_responsiveness', 'label' => 'Repairs & Responsiveness', 'prompt' => 'How responsive was the landlord/property manager to legitimate repair and maintenance requests?', 'weight' => 20],
        ['key' => 'communication', 'label' => 'Communication', 'prompt' => 'How clear, timely and professional was communication?', 'weight' => 15],
        ['key' => 'lease_financial_transparency', 'label' => 'Lease & Financial Transparency', 'prompt' => 'Were lease terms, rent, fees, utilities and other charges clearly communicated?', 'weight' => 15],
        ['key' => 'privacy_entry_practices', 'label' => 'Privacy & Entry Practices', 'prompt' => 'Did the landlord appropriately respect your privacy and applicable entry/notice requirements?', 'weight' => 10],
        ['key' => 'safety_security_response', 'label' => 'Safety & Security Response', 'prompt' => 'How appropriately did the landlord respond to property safety or security concerns?', 'weight' => 10],
        ['key' => 'issue_resolution', 'label' => 'Issue Resolution', 'prompt' => 'How effectively were tenancy-related concerns addressed?', 'weight' => 5],
        ['key' => 'move_in_move_out_experience', 'label' => 'Move-In/Move-Out Experience', 'prompt' => 'How organized and professional was the move-in/move-out process?', 'weight' => 5],
    ];

    $tenantCriteria = [
        ['key' => 'payment_reliability', 'label' => 'Payment Reliability', 'prompt' => 'Were rent payments made in accordance with the tenancy agreement or an agreed payment arrangement?', 'weight' => 25],
        ['key' => 'property_care', 'label' => 'Property Care', 'prompt' => 'Did the tenant take reasonable care of the property, excluding normal wear and tear?', 'weight' => 20],
        ['key' => 'communication', 'label' => 'Communication', 'prompt' => 'Was communication timely, respectful and constructive?', 'weight' => 15],
        ['key' => 'lease_compliance', 'label' => 'Lease Compliance', 'prompt' => 'Did the tenant generally comply with applicable and lawful tenancy obligations?', 'weight' => 15],
        ['key' => 'maintenance_reporting', 'label' => 'Maintenance Reporting', 'prompt' => 'Did the tenant appropriately report maintenance or property concerns?', 'weight' => 10],
        ['key' => 'reasonable_access_cooperation', 'label' => 'Reasonable Access Cooperation', 'prompt' => 'Did the tenant reasonably cooperate with properly arranged lawful access?', 'weight' => 5],
        ['key' => 'tenancy_community_conduct', 'label' => 'Tenancy/Community Conduct', 'prompt' => 'Were legitimate tenancy-related conduct expectations generally respected?', 'weight' => 5],
        ['key' => 'move_out_cooperation', 'label' => 'Move-Out Cooperation', 'prompt' => 'Was the tenancy transition handled reasonably and cooperatively?', 'weight' => 5],
    ];

    $seeded = 0;
    foreach (['landlord' => $landlordCriteria, 'tenant' => $tenantCriteria] as $subjectType => $criteria) {
        foreach ($criteria as $index => $criterion) {
            ReviewCriterion::create($criterion + ['subject_type' => $subjectType, 'sort_order' => $index + 1]);
            $seeded++;
        }
    }

    $messages[] = "seeded {$seeded} review criteria";

    return $messages;
}

<?php
// /scripts/reset/rew-rating-criteria.php
declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use App\Models\RatingCriterion;

/**
 * The per-user-type rating criteria catalog — ported from gonachi-old's
 * gnc_rating_criteria concept (exact seed text wasn't recoverable from that
 * source; it only ever lived in the production DB there). Structurally
 * matches what WAS recoverable: most user types share one generic set,
 * Contractor and Real Estate Agent each get their own specialized set, and
 * "User" (the generic/no-stated-role type) gets no criteria at all —
 * review-only, matching gonachi-old's own user_type_id=8 special case.
 */
function resetRewRatingCriteriaTable(): array
{
    $messages = [];

    try {
        $tableName = (new RatingCriterion())->getTable();

        Capsule::schema()->dropIfExists($tableName);
        $messages[] = "dropped existing {$tableName} table";

        Capsule::schema()->create($tableName, function (Blueprint $table) {
            $table->id('criteria_id');
            $table->unsignedInteger('user_type_id')->index();
            $table->string('criteria', 150);
            $table->timestamps();
        });

        $messages[] = "created {$tableName} table";

        // user_type_id: 1=Admin, 2=Landlord, 3=Tenant, 4=Property Manager,
        // 5=Real Estate Agent, 6=Contractor, 7=Mortgage Broker, 8=User.
        $generic = ['Communication', 'Reliability', 'Professionalism', 'Value for Money'];
        $agent = ['Market Knowledge', 'Responsiveness', 'Negotiation Skills', 'Communication', 'Professionalism'];
        $contractor = ['Quality of Work', 'Timeliness', 'Communication', 'Pricing Fairness', 'Cleanliness'];

        $seeds = [];
        foreach ([1, 2, 3, 4, 7] as $typeId) {
            foreach ($generic as $criteria) {
                $seeds[] = ['user_type_id' => $typeId, 'criteria' => $criteria];
            }
        }
        foreach ($agent as $criteria) {
            $seeds[] = ['user_type_id' => 5, 'criteria' => $criteria];
        }
        foreach ($contractor as $criteria) {
            $seeds[] = ['user_type_id' => 6, 'criteria' => $criteria];
        }
        // user_type_id 8 (User) intentionally has zero rows — review-only.

        foreach ($seeds as $seed) {
            RatingCriterion::create($seed);
        }

        $messages[] = "seeded " . count($seeds) . " rating criteria";
    } catch (\Throwable $e) {
        $messages[] = "{$tableName} table error: " . $e->getMessage();
    }

    return $messages;
}

<?php
// /scripts/reset/cde-seed.php
//
// Baseline record for the contractor-discovery project so the Job Requests
// page isn't empty on a fresh install — matches the original UI mockup
// exactly (Plumber Needed / Lekki / ₦150,000).

declare(strict_types=1);

use App\Models\JobRequest;

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

    return $messages;
}

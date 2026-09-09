<?php
// /server/api/rating-criteria.php
//
// The star-criteria list for a given destination role (?user_type_id=) —
// empty array for roles with none (e.g. the generic "User" type). No auth
// required — same as other static-lookup endpoints in this codebase.

declare(strict_types=1);

use Src\Controller\RatingsController;

header('Content-Type: application/json');

$userTypeId = (int) ($_GET['user_type_id'] ?? 0);

json_response([
    'success' => true,
    'data' => $userTypeId ? RatingsController::criteriaFor($userTypeId) : [],
]);

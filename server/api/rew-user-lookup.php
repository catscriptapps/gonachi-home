<?php
// /server/api/rew-user-lookup.php
//
// Shared "find a user" search for Ratings and Recommendations — both
// modules need to look someone up by name/email before rating/recommending
// them or browsing what they've received. Excludes the caller themselves
// and requires login (rating/recommending is an authenticated action in
// this app, unlike gonachi-old's live apps which didn't enforce that).

declare(strict_types=1);

use App\Models\User;
use Src\Service\AuthService;

header('Content-Type: application/json');

$userId = AuthService::userId();

if (!$userId) {
    json_response(['success' => false, 'message' => 'Please sign in.'], 401);
}

$query = trim((string) ($_GET['q'] ?? ''));

if ($query === '') {
    json_response(['success' => true, 'data' => []]);
}

$users = User::where('id', '!=', $userId)
    ->where(function ($q) use ($query) {
        $q->where('first_name', 'like', "%{$query}%")
            ->orWhere('last_name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%");
    })
    ->limit(10)
    ->get();

json_response([
    'success' => true,
    'data' => $users->map(fn (User $u) => [
        'id' => $u->id,
        'name' => $u->full_name ?? 'User',
        'initial' => strtoupper(substr($u->full_name ?? 'U', 0, 1)),
        'avatar' => $u->avatar_url ? getAssetBase() . 'images/uploads/avatars/' . $u->avatar_url : null,
        'city' => $u->city,
        'country_id' => $u->country_id,
        'region_id' => $u->region_id,
    ])->values()->all(),
]);

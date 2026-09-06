<?php
// /src/Controller/UsersController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\User;
use App\Models\Follow;
use App\Utils\IdEncoder;
use Src\Service\AuthService;
use App\Traits\RecentActivityLogger;

class UsersController
{
    use RecentActivityLogger;

    /**
     * Core accounts that must always survive a delete request — id 1 (Cat)
     * and id 2 (Elas), the two seeded legacy accounts every reset script
     * recreates. Enforced here (not just in the UI) since this is the
     * actual authority the API checks.
     */
    private const PROTECTED_USER_IDS = [1, 2];

    /**
     * Handle Delete
     * @param string|null $id
     * @return array
     */
    public function delete(?string $id): array
    {
        try {
            $rawId = (is_string($id) && !is_numeric($id)) ? IdEncoder::decode($id) : (int)$id;

            if (in_array((int) $rawId, self::PROTECTED_USER_IDS, true)) {
                return ['success' => false, 'messages' => ['This is a core account and cannot be deleted.']];
            }

            $user = User::find($rawId);

            if ($user) {
                $userName = $user->full_name;
                $userEmail = $user->email;

                if ($user->delete()) {
                    static::logActivity("Deleted user account: {$userName} ({$userEmail})", 'Users');
                    return ['success' => true, 'messages' => ['User deleted successfully.']];
                }
            }
            return ['success' => false, 'messages' => ['Failed to delete user.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    /**
     * Prepare data for the Users List Page
     * Optimized: Supports infinite scroll and search
     * @return void
     */
    public function index(): void
    {
        $query = $_GET['q'] ?? '';
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 100;
        $offset = ($page - 1) * $perPage;

        $mode = $_GET['mode'] ?? 'table';

        // Per-column header filters (all AND'd together, and with $query above)
        $filterName     = trim((string) ($_GET['filter_name'] ?? ''));
        $filterLocation = trim((string) ($_GET['filter_location'] ?? ''));
        $filterRoles    = trim((string) ($_GET['filter_roles'] ?? ''));
        $filterStatus   = trim((string) ($_GET['filter_status'] ?? ''));

        // Column sort: name | location | joined | status. Anything else falls
        // back to the original default (most recently created first).
        $sortKey = (string) ($_GET['sort'] ?? '');
        $sortDir = strtolower((string) ($_GET['dir'] ?? '')) === 'desc' ? 'desc' : 'asc';

        $builder = User::with(['country', 'region'])
            ->leftJoin('countries', 'users.country_id', '=', 'countries.id')
            ->leftJoin('regions', 'users.region_id', '=', 'regions.id')
            ->select('users.*');

        if (!empty($query)) {
            $builder->where(function ($q) use ($query) {
                $q->where('users.first_name', 'LIKE', "%{$query}%")
                    ->orWhere('users.last_name', 'LIKE', "%{$query}%")
                    ->orWhereRaw("CONCAT(users.first_name, ' ', users.last_name) LIKE ?", ["%{$query}%"])
                    ->orWhere('users.email', 'LIKE', "%{$query}%")
                    ->orWhere('users.city', 'LIKE', "%{$query}%")
                    ->orWhere('countries.country', 'LIKE', "%{$query}%")
                    ->orWhere('regions.region', 'LIKE', "%{$query}%");
            });
        }

        if ($filterName !== '') {
            $builder->where(function ($q) use ($filterName) {
                $q->where('users.first_name', 'LIKE', "%{$filterName}%")
                    ->orWhere('users.last_name', 'LIKE', "%{$filterName}%")
                    ->orWhereRaw("CONCAT(users.first_name, ' ', users.last_name) LIKE ?", ["%{$filterName}%"])
                    ->orWhere('users.email', 'LIKE', "%{$filterName}%");
            });
        }

        if ($filterLocation !== '') {
            $builder->where(function ($q) use ($filterLocation) {
                $q->where('users.city', 'LIKE', "%{$filterLocation}%")
                    ->orWhere('regions.region', 'LIKE', "%{$filterLocation}%")
                    ->orWhere('countries.country', 'LIKE', "%{$filterLocation}%");
            });
        }

        if ($filterStatus !== '') {
            // Only two labels ever render ("Current" / "Archived" — see
            // data-row.php's $statusBadge) so match the typed text against
            // those labels rather than the raw status_id.
            $needle = mb_strtolower($filterStatus);
            if (str_contains('current', $needle)) {
                $builder->where('users.status_id', 1);
            } elseif (str_contains('archived', $needle)) {
                $builder->where('users.status_id', '!=', 1);
            } else {
                $builder->whereRaw('1 = 0');
            }
        }

        if ($filterRoles !== '') {
            $matchingTypeIds = [];
            foreach (\Src\Controller\UserTypesController::list() as $type) {
                if (stripos((string) $type->user_type, $filterRoles) !== false) {
                    $matchingTypeIds[] = (int) $type->user_type_id;
                }
            }

            if (!empty($matchingTypeIds)) {
                $builder->where(function ($q) use ($matchingTypeIds) {
                    foreach ($matchingTypeIds as $typeId) {
                        $q->orWhereRaw('JSON_CONTAINS(users.user_type_ids, ?)', [json_encode($typeId)]);
                    }
                });
            } else {
                $builder->whereRaw('1 = 0');
            }
        }

        $totalFiltered = $builder->count();

        switch ($sortKey) {
            case 'name':
                $builder->orderByRaw("CONCAT(users.first_name, ' ', users.last_name) {$sortDir}");
                break;
            case 'location':
                $builder->orderBy('users.city', $sortDir);
                break;
            case 'joined':
                $builder->orderBy('users.date_created', $sortDir);
                break;
            case 'status':
                $builder->orderBy('users.status_id', $sortDir);
                break;
            default:
                $builder->orderBy('users.date_created', 'desc');
                break;
        }

        $users = $builder->offset($offset)
            ->limit($perPage)
            ->get();

        // AJAX response
        if (isset($_GET['q']) || isset($_GET['page'])) {
            header('Content-Type: application/json');

            // Standard Table Response
            echo json_encode([
                'success' => true,
                'data' => array_map(fn($u) => ['rowHtml' => self::renderRow($u)], $users->all()),
                'meta' => [
                    'total' => $totalFiltered,
                    'loaded' => $users->count(),
                    'hasMore' => ($offset + $users->count()) < $totalFiltered
                ]
            ]);
            exit;
        }

        // Standard Page Load logic remains the same...
        $html = '';
        foreach ($users as $user) {
            $html .= self::renderRow($user);
        }

        $GLOBALS['userRows'] = $html;
        $GLOBALS['title'] = "Users";
        $GLOBALS['totalUsersCount'] = $totalFiltered;
    }

    /**
     * Render individual table row HTML
     * @param User $user
     * @return string
     */
    public static function renderRow(\App\Models\User $user): string
    {
        $rowItem = $user->toArray();

        // Fix for PHP Warning: Use helper to ensure full_name exists
        if (!isset($rowItem['full_name'])) {
            $rowItem['full_name'] = $user->full_name;
        }

        $GLOBALS['assetBase'] = getAssetBase();

        // Location Mapping
        $rowItem['country_name'] = $user->country->country ?? 'N/A';
        $rowItem['region_name']  = $user->region->region ?? 'N/A';

        // Encoding ID for security
        $rowItem['encoded_id'] = IdEncoder::encode((int)$user->id);
        $rowItem['created_at_formatted'] = $user->date_created ? $user->date_created->format('M j, Y') : 'N/A';

        $path = __DIR__ . '/../../resources/views/components/users/data-row.php';

        ob_start();
        try {
            // Passing variables explicitly to prevent Scope issues
            $assetBase = getAssetBase();
            include $path;
        } catch (\Throwable $e) {
            ob_end_clean();
            return "<tr><td colspan='6'>Render Error: " . $e->getMessage() . "</td></tr>";
        }
        return ob_get_clean();
    }

    /**
     * Handle Create or Update for Users
     * @param array $data
     * @return array
     */
    public function save(array $data): array
    {
        try {
            $encodedId = $data['encoded_id'] ?? null;
            $email = trim($data['email'] ?? '');
            $isNew = empty($encodedId);

            if (empty($email)) throw new \Exception("Email address is required.");

            $userId = !$isNew ? IdEncoder::decode($encodedId) : null;
            $user = $userId ? User::find($userId) : new User();

            if (!$user) throw new \Exception("User not found.");

            // Email uniqueness check
            $existingQuery = User::where('email', $email);
            if ($user->exists) {
                $existingQuery->where('id', '!=', $user->id);
            }
            if ($existingQuery->exists()) {
                throw new \Exception("The email address '{$email}' is already in use.");
            }

            $user->first_name = $data['first_name'] ?? '';
            $user->last_name  = $data['last_name'] ?? '';
            $user->email      = $email;
            $user->city       = $data['city'] ?? null;
            $user->country_id = (int)($data['country_id'] ?? 1);
            $tableRegionId    = (int)($data['region_id'] ?? 0);
            $user->region_id  = $tableRegionId > 0 ? $tableRegionId : null;

            if (!empty($data['password'])) {
                $user->password = password_hash($data['password'], PASSWORD_BCRYPT);
            }

            // Role Compilation — anyone (guest self-registering, or a user
            // editing their own profile) may freely pick their own
            // non-admin stakeholder role(s) (Landlord, Tenant, Contractor,
            // etc.), but only an authenticated admin caller may grant or
            // keep the Admin role (user_type_id 1). The Admin checkbox is
            // already hidden from non-admin viewers client-side (see
            // users-modal.js's visibleRoles()) — this is the actual
            // enforcement, since the UI hiding it is not itself a
            // safeguard against a hand-crafted request.
            if (isset($data['user_type_ids']) && is_array($data['user_type_ids'])) {
                $submittedRoles = array_map('intval', $data['user_type_ids']);
                if (!AuthService::isAdmin()) {
                    $submittedRoles = array_values(array_diff($submittedRoles, [1]));
                }

                // Never leave a user with zero roles — falls back to the
                // default rather than wiping an existing account's roles
                // (only reachable via a hand-crafted request; the UI always
                // keeps at least one checkbox checked).
                $user->user_type_ids = $submittedRoles ?: ($isNew ? [2] : $user->user_type_ids);
            } elseif ($isNew) {
                $user->user_type_ids = [2];
            }

            // Core Account Admin Guard: If ID is 1 or 2, force Admin (1) into the array
            if (!$isNew && in_array((int)$user->id, [1, 2])) {
                $currentRoles = $user->user_type_ids; // Pull array out of the overloaded property
                if (!in_array(1, $currentRoles)) {
                    $currentRoles[] = 1;
                    $user->user_type_ids = array_values(array_unique($currentRoles)); // Re-assign the whole array
                }
            }

            $appEnv = $_ENV['APP_ENV'] ?? '';
            $isLocal = $appEnv === 'local';

            if ($isNew) {
                $user->status_id = $isLocal ? 1 : 0;
            } elseif (array_key_exists('status_id', $data)) {
                $user->status_id = (int) $data['status_id'] === 1 ? 1 : 0;
            }

            $user->save();

            if ($isNew && !$isLocal) {
                // 1. Generate a secure random token (32 bytes = 64 chars)
                $token = bin2hex(random_bytes(32));

                // 2. Store in your verification table (Emulating PasswordReset logic)
                \App\Models\UserVerification::updateOrCreate(
                    ['email' => $email],
                    [
                        'token' => password_hash($token, PASSWORD_DEFAULT),
                        'created_at' => date('Y-m-d H:i:s')
                    ]
                );

                // 3. Construct the Activation Link (Using your Env logic)
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
                $host     = $_SERVER['HTTP_HOST'];
                $envBase  = trim($_ENV['APP_BASE_PATH'] ?? '', '/');
                $fullBaseUrl = $protocol . $host . ($envBase ? '/' . $envBase : '');

                $activationLink = rtrim($fullBaseUrl, '/') . "/verify-account?token={$token}&email=" . urlencode($email);

                // 4. Send the Email via MailService
                $subject = "Activate Your Account";
                $body = "
                <div style='font-family: \"Quicksand\", sans-serif; color: #000000;'>
                    <h2 style='color: #EA580C;'>Welcome to the Team, {$user->first_name}!</h2>
                    <p>We're excited to have you. Please click the button below to verify your email and activate your account:</p>
                    <div style='margin: 32px 0;'>
                        <a href='{$activationLink}' style='background-color: #EA580C; color: white; padding: 14px 28px; text-decoration: none; border-radius: 10px; font-weight: bold; display: inline-block; box-shadow: 0 4px 6px rgba(139, 92, 246, 0.2);'>Verify My Account</a>
                    </div>
                    <p style='font-size: 0.875rem; color: #818181;'>If the button doesn't work, copy and paste this link: <br>{$activationLink}</p>
                </div>
            ";

                \Src\Service\MailService::send($email, $subject, $body);

                return [
                    'success' => true,
                    'is_registration' => true,
                    'messages' => ["Welcome! We've sent an activation link to <strong>{$email}</strong>. Please click it to complete your registration."]
                ];
            }

            $user->load(['country', 'region']);

            $actionLabel = $isNew ? "Created user profile" : "Updated user profile";
            static::logActivity("{$actionLabel}: {$user->full_name} ({$user->email})", 'Users');

            return [
                'success'  => true,
                'user_id'  => $user->id,
                'data'     => $user->toArray(),
                'rowHtml'  => self::renderRow($user),
                'messages' => ['User saved successfully.']
            ];
        } catch (\Throwable $e) {
            static::logActivity("User save error: " . $e->getMessage(), 'Users');
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    /**
     * Update only User Types
     * @param array $data
     * @return array
     */
    public function updateTypes(array $data): array
    {
        try {
            $encodedId = $data['encoded_id'] ?? null;
            if (!$encodedId) throw new \Exception("User ID is required.");

            $userId = IdEncoder::decode($encodedId);
            $user = User::find($userId);

            if (!$user) throw new \Exception("User not found.");

            $user->user_type_ids = isset($data['user_type_ids']) ? array_map('intval', $data['user_type_ids']) : [];
            $user->save();

            static::logActivity("Updated professional roles for: {$user->full_name}", 'Users');
            $user->load(['country', 'region']);

            return [
                'success' => true,
                'messages' => ['Roles updated successfully.'],
                'rowHtml' => self::renderRow($user)
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }
}

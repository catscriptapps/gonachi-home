<?php
// /src/Service/AuthService.php

declare(strict_types=1);

namespace Src\Service;

use App\Models\User;

/**
 * Class AuthService
 * Centralized authentication service updated for the modernized users table.
 */
class AuthService
{
    /**
     * Retrieves the currently logged-in user from the database.
     * Uses the standard 'id' primary key.
     */
    public static function currentUser(): ?User
    {
        self::ensureSession();

        return isset($_SESSION['user_id'])
            ? User::find((int)$_SESSION['user_id'])
            : null;
    }

    /**
     * Retrieves the currently logged-in landlord from the database.
     * Uses the 'landlord_id' from the session.
     */
    public static function currentLandlord(): ?\App\Models\Landlord
    {
        self::ensureSession();

        if (!isset($_SESSION['landlord_id']) || $_SESSION['account_type'] !== 'landlord') {
            return null;
        }
        return \App\Models\Landlord::find((int)$_SESSION['landlord_id']);
    }

    /**
     * Retrieves the currently logged-in tenant from the database.
     * Uses the 'tenant_id' from the session.
     */
    public static function currentTenant(): ?\App\Models\Tenant
    {
        self::ensureSession();

        if (!isset($_SESSION['tenant_id']) || $_SESSION['account_type'] !== 'tenant') {
            return null;
        }
        return \App\Models\Tenant::find((int)$_SESSION['tenant_id']);
    }

    /**
     * Check if the user has access to a specific app.
     * 'Users' is restricted to Admins (Type 1) only.
     */
    public static function hasAccess(string $appName): bool
    {
        if (self::isAdmin()) {
            return true;
        }

        // If the app is 'Users', and they aren't an Admin, it's a hard NO.
        if (strtolower($appName) === 'users') {
            return false;
        }

        // For other apps (Dashboard, Feed, etc.), we can check for a logged-in session
        $user = self::currentUser();
        return $user !== null;
    }

    /**
     * Ensures that a PHP session is started with a 2-week persistence.
     */
    protected static function ensureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Determine if the current user is an admin.
     * Admin is defined as having user_type_id 1 in their collection.
     */
    public static function isAdmin(): bool
    {
        self::ensureSession();
        $uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

        if ($uid === 0) {
            return false;
        }

        // Fetch the user to check their modernized type collection
        $user = \App\Models\User::find($uid);

        if (!$user || !is_array($user->user_type_ids)) {
            return false;
        }

        // Check if 1 (Admin) exists in their array of types
        return in_array(1, $user->user_type_ids);
    }

    /**
     * Determine if the current user is Cat (ID 1).
     */
    public static function isCat(): bool
    {
        self::ensureSession();
        return isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === 1;
    }

    /**
     * Checks if a user is currently logged in.
     */
    public static function isLoggedIn(): bool
    {
        self::ensureSession();
        // A user is considered logged in if either a user_id for a backend user
        // OR a landlord_id for a landlord is present in the session.
        $isUserLoggedIn = isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] > 0;
        $isLandlordLoggedIn = isset($_SESSION['landlord_id']) && (int)$_SESSION['landlord_id'] > 0;
        $isTenantLoggedIn = isset($_SESSION['tenant_id']) && (int)$_SESSION['tenant_id'] > 0;
        return $isUserLoggedIn || $isLandlordLoggedIn || $isTenantLoggedIn;
    }

    /**
     * Attempt to authenticate a user.
     * Only allows users with status_id = 1 (Active/Verified).
     */
    public static function login(string $email, string $password): array
    {
        self::ensureSession();

        // --- Priority 1: Attempt login as a backend User ---
        $user = User::where('email', $email)->first();
        if ($user && password_verify($password, $user->password)) {
            // CHECK STATUS: Only active users can proceed
            if ((int)$user->status_id !== 1) {
                return [
                    'success' => false,
                    'unverified' => true,
                    'messages' => ['Account not activated. Please verify your email.']
                ];
            }

            // Set User Session Data
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_email'] = $user->email;
            $_SESSION['user_full_name'] = $user->full_name;
            $_SESSION['account_type'] = 'user'; // Distinguish account type

            // Generate secure API token
            $token = bin2hex(random_bytes(32));
            $user->api_token = $token;
            $user->user_last_log = date('Y-m-d H:i:s');
            $user->save();

            return [
                'success' => true,
                'api_token' => $token,
                'messages' => ['Login successful!'],
                'redirect_url' => '/dashboard' // Explicit redirect for users
            ];
        }

        // --- Priority 2: Attempt login as a Landlord ---
        $landlord = \App\Models\Landlord::where('email', $email)->first();
        if ($landlord && password_verify($password, $landlord->password)) {
            // CHECK STATUS: Only active landlords can proceed
            if ((int)$landlord->status_id !== 1) {
                return [
                    'success' => false,
                    'unverified' => true,
                    'messages' => ['Landlord account not activated. Please check your email for a verification link or contact support.']
                ];
            }

            // Set Landlord Session Data
            $_SESSION['landlord_id'] = $landlord->id;
            $_SESSION['landlord_email'] = $landlord->email;
            $_SESSION['landlord_company_name'] = $landlord->company_name;
            $_SESSION['account_type'] = 'landlord'; // Distinguish account type

            // Landlords do not use API tokens in the same way as users for now
            $landlord->save(); // This will update the `timestamp` field

            return [
                'success' => true,
                'messages' => ['Landlord portal login successful!'],
                'redirect_url' => '/dashboard' // Explicit redirect for landlords
            ];
        }

        // --- Priority 3: Attempt login as a Tenant ---
        $tenant = \App\Models\Tenant::where('email', $email)->first();
        if ($tenant && $tenant->password && password_verify($password, $tenant->password)) {
            // CHECK STATUS: Only active tenants can proceed
            if ((int)$tenant->status_id !== 1) {
                return [
                    'success' => false,
                    'unverified' => true,
                    'messages' => ['Account not activated. Please verify your email.']
                ];
            }

            // Set Tenant Session Data
            $_SESSION['tenant_id'] = $tenant->id;
            $_SESSION['tenant_email'] = $tenant->email;
            $_SESSION['tenant_full_name'] = $tenant->full_name;
            $_SESSION['account_type'] = 'tenant'; // Distinguish account type

            return [
                'success' => true,
                'messages' => ['Tenant portal login successful!'],
                'redirect_url' => '/home' // No dedicated tenant dashboard yet
            ];
        }

        return ['success' => false, 'messages' => ['Invalid email or password.']];
    }

    /**
     * Authenticate an API request by checking an incoming Bearer Token.
     *
     * @param string $token
     * @return User|null
     */
    public static function getUserByToken(string $token): ?User
    {
        return User::where('api_token', $token)->first();
    }

    /**
     * Logs out the current user.
     */
    public static function logout(): void
    {
        self::ensureSession();

        // If a user session exists, nullify their API token in the database
        if (isset($_SESSION['user_id'])) {
            $user = User::find((int)$_SESSION['user_id']);
            if ($user) {
                $user->api_token = null;
                $user->save();
            }
        }

        session_unset();
        session_destroy();
    }

    /**
     * Get the current user ID.
     */
    public static function userId(): ?int
    {
        self::ensureSession();
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }
}

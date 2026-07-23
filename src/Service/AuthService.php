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
        // No Landlord account model in this app (removed in the PMB-template
        // cleanup) — always null. Kept as a stub so existing callers'
        // falsy checks keep working without touching every call site.
        return null;
    }

    /**
     * Retrieves the currently logged-in tenant from the database.
     * Uses the 'tenant_id' from the session.
     */
    public static function currentTenant(): ?\App\Models\Tenant
    {
        // No Tenant account model in this app — see currentLandlord() above.
        return null;
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
     *
     * Previously this only started the default session-only cookie (expires
     * on browser close, server-side data garbage-collected after ~24
     * minutes of inactivity per PHP's default gc_maxlifetime) despite this
     * docblock's claim — nothing actually configured the lifetime. That
     * mismatch is what let a guest's chat_guest_token (see ChatController)
     * silently expire while the widget's localStorage-cached conversation
     * id lived on indefinitely, producing a "couldn't find that
     * conversation" error the next time they sent a message.
     */
    protected static function ensureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $lifetime = 60 * 60 * 24 * 14; // 2 weeks

            session_set_cookie_params([
                'lifetime' => $lifetime,
                'path' => '/',
                'domain' => '',
                'secure' => !empty($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            ini_set('session.gc_maxlifetime', (string) $lifetime);

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

        // Note: this app only has backend User accounts. Older Landlord/Tenant
        // login fallbacks were removed here since those models no longer
        // exist (see currentLandlord()/currentTenant() above).

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

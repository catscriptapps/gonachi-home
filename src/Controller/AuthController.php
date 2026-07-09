<?php
// /src/Controller/AuthController.php
declare(strict_types=1);

namespace Src\Controller;

use Src\Service\AuthService;
use App\Traits\RecentActivityLogger;
use App\Models\User;
use App\Models\PasswordReset;
use App\Models\UserVerification;

/**
 * Class AuthController
 *
 * Acts as a thin controller layer between HTTP requests (API routes)
 * and the business logic handled by AuthService.
 *
 * Responsibilities:
 * - Parse and validate input from JSON or POST data.
 * - Delegate authentication logic to AuthService.
 * - Return consistent structured responses (JSON-serializable arrays).
 *
 * This controller does NOT handle direct output (echo) or HTTP headers.
 * That responsibility remains in the API endpoint scripts, which call
 * these controller methods and return the response as JSON.
 */
class AuthController
{
    use RecentActivityLogger; // ✅ Add logging trait

    /**
     * Returns the currently logged-in user’s information.
     * @return array
     */
    public static function currentUser(): array
    {
        try {
            $user = AuthService::currentUser();

            if (!$user) {
                return [
                    'success'  => false,
                    'messages' => ['No user is currently logged in.']
                ];
            }

            return [
                'success' => true,
                'user' => [
                    'id'        => $user->id,
                    'email'     => $user->email,
                    'full_name' => $user->full_name,
                ]
            ];
        } catch (\Throwable $e) {
            return [
                'success'  => false,
                'messages' => ['Error fetching current user: ' . $e->getMessage()]
            ];
        }
    }

    /**
     * Handles the password reset request
     */
    public static function forgotPassword(array $input): array
    {
        $email = $input['email'] ?? '';

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'messages' => ['Please provide a valid email address.']
            ];
        }

        // 1. Check if user exists
        $user = User::where('email', $email)->first();

        // Security Tip: Even if user doesn't exist, we often return "success" 
        // to prevent email enumeration, but for internal SaaS, showing an error is often fine.
        if (!$user) {
            return [
                'success' => false,
                'messages' => ['No account found with that email address.']
            ];
        }

        try {
            // 2. Generate a secure random token
            $token = bin2hex(random_bytes(32));

            // 3. Store token in database (using a dedicated table)
            // Typically: email, token, created_at
            PasswordReset::updateOrCreate(
                ['email' => $email],
                [
                    'token' => password_hash($token, PASSWORD_DEFAULT),
                    'created_at' => date('Y-m-d H:i:s')
                ]
            );

            // 4. Send the Email (Logic for your mailer here)

            // --- FIXED RECOVERY LINK LOGIC ---
            // We pull from ENV to respect the subfolder (e.g., /cas-sports/)
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
            $host     = $_SERVER['HTTP_HOST'];
            $envBase  = trim($_ENV['APP_BASE_PATH'] ?? '', '/');

            // Construct the full base (Host + Subfolder if exists)
            $fullBaseUrl = $protocol . $host . ($envBase ? '/' . $envBase : '');

            // Ensure single trailing slash before appending the route
            $resetLink = rtrim($fullBaseUrl, '/') . "/reset-password?token={$token}&email=" . urlencode($email);
            // ---------------------------------

            $subject = "Password Reset Request";
            $body = "
                <div style='font-family: \"Quicksand\", sans-serif; color: #431405;'>
                    <h2 style='color: #ea580c;'>Password Reset</h2>
                    <p>You are receiving this email because we received a password reset request for your account.</p>
                    <div style='margin: 32px 0;'>
                        <a href='{$resetLink}' style='background-color: #f97316; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;'>Reset Password</a>
                    </div>
                    <p style='font-size: 0.875rem; color: #818181;'>This password reset link will expire in 60 minutes.</p>
                    <p style='font-size: 0.875rem; color: #818181;'>If you did not request a password reset, no further action is required.</p>
                </div>
            ";

            \Src\Service\MailService::send($email, $subject, $body);

            // Log the successful request
            static::logActivity("Password reset email sent", 'Auth', $user->id);

            return [
                'success' => true,
                'message' => 'A password reset link has been sent to your email.'
            ];
        } catch (\Exception $e) {
            // Log the actual error for debugging
            static::logActivity("Forgot Password Error: " . $e->getMessage(), 'Auth');

            return [
                'success' => false,
                'messages' => ['An error occurred while processing your request.']
            ];
        }
    }

    /**
     * Handles public self-registration. Mirrors UsersController::save()'s
     * env-aware activation flow (used by the admin Users CRUD app), but
     * scoped to the public signup form: new accounts always default to
     * user_type_ids = [2] (Registered), never auto-elevated to Admin.
     *
     * When APP_ENV=local the account is instantly active (no email step,
     * since local dev typically has no mail transport configured) but the
     * caller does NOT get auto-logged-in — server/api/register.php sends
     * them back to sign in explicitly. Otherwise an activation email is
     * sent and VerificationController::verify() completes the flow.
     */
    public static function register(array $input): array
    {
        $firstName = trim($input['first_name'] ?? '');
        $lastName = trim($input['last_name'] ?? '');
        $email = trim($input['email'] ?? '');
        $password = (string) ($input['password'] ?? '');
        $passwordConfirmation = (string) ($input['password_confirmation'] ?? '');

        if ($firstName === '' || $lastName === '' || $email === '') {
            return ['success' => false, 'messages' => ['Please fill in your name and email address.']];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'messages' => ['Please provide a valid email address.']];
        }

        if (strlen($password) < 8) {
            return ['success' => false, 'messages' => ['Password must be at least 8 characters long.']];
        }

        if ($password !== $passwordConfirmation) {
            return ['success' => false, 'messages' => ['Passwords do not match.']];
        }

        if (User::where('email', $email)->exists()) {
            return ['success' => false, 'messages' => ["An account with the email '{$email}' already exists."]];
        }

        try {
            $appEnv = $_ENV['APP_ENV'] ?? '';
            $isLocal = $appEnv === 'local';

            $user = User::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'user_type_ids' => [2],
                'status_id' => $isLocal ? 1 : 0,
            ]);

            if (!$isLocal) {
                $token = bin2hex(random_bytes(32));

                UserVerification::updateOrCreate(
                    ['email' => $email],
                    ['token' => password_hash($token, PASSWORD_DEFAULT), 'created_at' => date('Y-m-d H:i:s')]
                );

                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
                $host = $_SERVER['HTTP_HOST'];
                $envBase = trim($_ENV['APP_BASE_PATH'] ?? '', '/');
                $fullBaseUrl = $protocol . $host . ($envBase ? '/' . $envBase : '');
                $activationLink = rtrim($fullBaseUrl, '/') . "/verify-account?token={$token}&email=" . urlencode($email);

                $subject = 'Activate Your Gonachi Account';
                $body = "
                    <div style='font-family: \"Quicksand\", sans-serif; color: #000000;'>
                        <h2 style='color: #EA580C;'>Welcome to Gonachi, {$firstName}!</h2>
                        <p>Please click the button below to verify your email and activate your account:</p>
                        <div style='margin: 32px 0;'>
                            <a href='{$activationLink}' style='background-color: #EA580C; color: white; padding: 14px 28px; text-decoration: none; border-radius: 10px; font-weight: bold; display: inline-block;'>Verify My Account</a>
                        </div>
                        <p style='font-size: 0.875rem; color: #818181;'>If the button doesn't work, copy and paste this link: <br>{$activationLink}</p>
                    </div>
                ";

                \Src\Service\MailService::send($email, $subject, $body);

                static::logActivity("New account registered (pending email verification): {$email}", 'Auth', $user->id);

                return [
                    'success' => true,
                    'is_registration' => true,
                    'messages' => ["We've sent an activation link to {$email}."],
                ];
            }

            static::logActivity("New account registered (local, auto-activated): {$email}", 'Auth', $user->id);

            return [
                'success' => true,
                'is_registration' => false,
                'messages' => ['Account created. Please sign in to continue.'],
            ];
        } catch (\Throwable $e) {
            static::logActivity('Registration error: ' . $e->getMessage(), 'Auth');
            return ['success' => false, 'messages' => ['An unexpected error occurred. Please try again.']];
        }
    }

    /**
     * Handles the login process.
     * Now processes the rich array returned by AuthService.
     */
    public static function login(array $input): array
    {
        // --- Step 1: Basic input extraction & validation ---
        $email = trim($input['email'] ?? '');
        $password = trim($input['password'] ?? '');

        if ($email === '' || $password === '') {
            static::logActivity('Failed login attempt - missing credentials', 'Auth');

            return [
                'success'  => false,
                'api_token' => null,
                'messages' => ['Email and password are required.']
            ];
        }

        // --- Step 2: Delegate authentication to AuthService ---
        try {
            // $result is now an array: ['success' => bool, 'messages' => [...], 'unverified' => bool]
            $result = AuthService::login($email, $password);
        } catch (\Throwable $e) {
            static::logActivity("Login error for email: {$email} - " . $e->getMessage(), 'Auth');

            return [
                'success'  => false,
                'api_token' => null,
                'messages' => ['Unexpected error during login: ' . $e->getMessage()]
            ];
        }

        // --- Step 3: Handle authentication outcome ---
        if ($result['success']) {
            // Log successful login
            $userId = $_SESSION['user_id'] ?? null;
            static::logActivity('Successful login', 'Auth', $userId);

            $response = [
                'success'  => true,
                'messages' => ['Login successful. Redirecting...'],
                'redirect_url' => $result['redirect_url'] ?? '/dashboard'
            ];

            if (isset($result['api_token'])) {
                $response['api_token'] = $result['api_token'];
            }
            return $response;
        }

        // Log specific failure (Unverified vs Invalid)
        $isUnverified = $result['unverified'] ?? false;
        $logMessage = $isUnverified
            ? "Failed login attempt (Unverified) for email: {$email}"
            : "Failed login attempt (Invalid credentials) for email: {$email}";

        static::logActivity($logMessage, 'Auth');

        // We return the $result exactly as the Service provided it
        // This ensures the 'unverified' flag reaches our JS LoginModal.
        return $result;
    }

    /**
     * Handles logout requests.
     * @return array
     */
    public static function logout(): array
    {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            AuthService::logout();

            // Log the logout event
            static::logActivity('User logged out', 'Auth', $userId);

            return [
                'success'  => true,
                'messages' => ['You have been successfully logged out.']
            ];
        } catch (\Throwable $e) {
            // Log unexpected error during logout
            static::logActivity('Logout error: ' . $e->getMessage(), 'Auth');

            return [
                'success'  => false,
                'messages' => ['Error while logging out: ' . $e->getMessage()]
            ];
        }
    }

    /**
     * Finalizes the password reset process
     */
    public static function resetPassword(array $input): array
    {
        $email = $input['email'] ?? '';
        $token = $input['token'] ?? '';
        $password = $input['password'] ?? '';
        $passwordConfirmation = $input['password_confirmation'] ?? '';

        // 1. Basic Validation
        if (empty($email) || empty($token) || empty($password)) {
            return [
                'success' => false,
                'messages' => ['Missing required information.']
            ];
        }

        if ($password !== $passwordConfirmation) {
            return [
                'success' => false,
                'messages' => ['Passwords do not match.']
            ];
        }

        if (strlen($password) < 8) {
            return [
                'success' => false,
                'messages' => ['Password must be at least 8 characters long.']
            ];
        }

        try {
            // 2. Find the reset record
            $resetRecord = PasswordReset::where('email', $email)->first();

            if (!$resetRecord) {
                return [
                    'success' => false,
                    'messages' => ['Invalid or expired reset request.']
                ];
            }

            // 3. Verify Token and Expiry
            if (!password_verify($token, $resetRecord->token)) {
                return [
                    'success' => false,
                    'messages' => ['Invalid token.']
                ];
            }

            if ($resetRecord->isExpired(60)) {
                $resetRecord->delete(); // Cleanup expired token
                return [
                    'success' => false,
                    'messages' => ['Reset link has expired. Please request a new one.']
                ];
            }

            // 4. Update the User
            $user = User::where('email', $email)->first();
            if (!$user) {
                return [
                    'success' => false,
                    'messages' => ['User account not found.']
                ];
            }

            $user->password = password_hash($password, PASSWORD_DEFAULT);
            $user->save();

            // 5. Cleanup: Remove the reset token so it can't be used again
            $resetRecord->delete();

            static::logActivity("Password updated via reset link", 'Auth', $user->id);

            return [
                'success' => true,
                'message' => 'Your password has been reset successfully.'
            ];
        } catch (\Exception $e) {
            static::logActivity("Reset Password Error: " . $e->getMessage(), 'Auth');
            return [
                'success' => false,
                'messages' => ['An unexpected error occurred. Please try again later.']
            ];
        }
    }
}

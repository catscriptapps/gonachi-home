<?php
// /src/Controller/VerificationController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\User;
use App\Models\UserVerification;

class VerificationController
{
    /**
     * Verify a registration email/token pair and, on success, log the
     * matching account straight in.
     *
     * Previously this checked App\Models\Landlord / App\Models\Tenant —
     * leftover from the old PMB template. Those classes no longer exist
     * (AuthService::currentLandlord()/currentTenant() are permanent null
     * stubs for the same reason), so calling them here always threw
     * "Class not found", silently swallowed by the catch below. This is
     * the first real caller of this flow (via the public signup form's
     * activation email), so it now checks the account type that actually
     * exists: App\Models\User.
     */
    public function verify(string $email, string $token): array
    {
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $email = trim($email);
            if ($email === '' || $token === '') {
                throw new \Exception("Invalid verification link.");
            }

            $verification = UserVerification::find($email);
            if (!$verification) {
                throw new \Exception("No pending verification found for this email.");
            }

            if ($verification->isExpired(60)) {
                $verification->delete();
                throw new \Exception("This verification link has expired. Please register again.");
            }

            if (!password_verify($token, $verification->token)) {
                throw new \Exception("Invalid or already-used verification link.");
            }

            $user = User::where('email', $email)->first();
            if ($user) {
                $user->status_id = 1;
                $user->email_verified = true;
                $user->save();
                $verification->delete();

                $_SESSION['user_id'] = $user->id;
                $_SESSION['user_email'] = $user->email;
                $_SESSION['user_full_name'] = $user->full_name;
                $_SESSION['account_type'] = 'user';

                return ['success' => true, 'messages' => ['Account verified.'], 'account_type' => 'user'];
            }

            throw new \Exception("We could not find an account matching this email.");
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }
}

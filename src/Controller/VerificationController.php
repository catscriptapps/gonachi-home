<?php
// /src/Controller/VerificationController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\User;
use App\Models\UserVerification;
use Src\Service\AuthService;
use Src\Service\MailService;

class VerificationController
{
    /**
     * Generates a fresh activation token, stores it (replacing any pending
     * one for this email), and emails the activation link. Shared by
     * UsersController::save() (on registration) and
     * server/api/resend-verification.php (the login modal's "Resend
     * Activation Link?" flow), so both stay in sync.
     *
     * @param string|null $resumeUrl Where to send the user back to once
     *   they click the link (e.g. the guest-gated page they were on when
     *   they registered) — see UserVerification.resume_url's doc comment
     *   for why this travels through the DB row rather than client-side
     *   session storage.
     */
    public static function sendVerificationEmail(User $user, ?string $resumeUrl = null): void
    {
        $email = $user->email;
        $token = bin2hex(random_bytes(32));

        UserVerification::updateOrCreate(
            ['email' => $email],
            [
                'token' => password_hash($token, PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s'),
                'resume_url' => $resumeUrl,
            ]
        );

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $envBase = trim($_ENV['APP_BASE_PATH'] ?? '', '/');
        $fullBaseUrl = $protocol . $host . ($envBase ? '/' . $envBase : '');

        $activationLink = rtrim($fullBaseUrl, '/') . "/verify-account?token={$token}&email=" . urlencode($email);

        $subject = 'Activate Your Gonachi Account';
        $body = "
        <div style='font-family: \"Quicksand\", sans-serif; color: #000000;'>
            <h2 style='color: #EA580C;'>Welcome to Gonachi, {$user->first_name}!</h2>
            <p>Please click the button below to verify your email and activate your account:</p>
            <div style='margin: 32px 0;'>
                <a href='{$activationLink}' style='background-color: #EA580C; color: white; padding: 14px 28px; text-decoration: none; border-radius: 10px; font-weight: bold; display: inline-block;'>Verify My Account</a>
            </div>
            <p style='font-size: 0.875rem; color: #818181;'>If the button doesn't work, copy and paste this link: <br>{$activationLink}</p>
            <p style='font-size: 0.875rem; color: #818181;'>Don't see this email? Check your junk/spam folder — and give it a few minutes, delivery isn't always instant. This link expires in 60 minutes.</p>
        </div>
        ";

        // A misconfigured/unreachable mail server must never make an
        // otherwise-successful registration look like it failed — the user
        // row and the verification token both already exist at this point
        // regardless, and they can always ask for a resend. See
        // MailService's own docblock for the .env vars this needs in a
        // real production deploy.
        try {
            MailService::send($email, $subject, $body);
        } catch (\Throwable $e) {
            error_log("VerificationController: failed to send activation email to {$email}: " . $e->getMessage());
        }
    }

    /**
     * Re-sends a fresh activation link — the login modal's "Resend
     * Activation Link?" flow (shown when a login attempt returns
     * `unverified`). Deliberately mirrors AuthController::forgotPassword()'s
     * lack of email-enumeration protection (returns an explicit "no
     * account found" message) for consistency with that existing flow.
     */
    public static function resend(string $email, ?string $resumeUrl = null): array
    {
        $email = trim($email);
        if ($email === '') {
            return ['success' => false, 'messages' => ['Please enter your email address.']];
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return ['success' => false, 'messages' => ['No account found with that email address.']];
        }

        // Keyed on email_verified, not status_id: accounts created while no
        // mail server was configured are active (status_id = 1) but still
        // unverified, and must be able to verify later.
        if ($user->email_verified) {
            return ['success' => false, 'messages' => ['This account is already verified — you can sign in.']];
        }

        // sendVerificationEmail() deliberately swallows delivery failures, so
        // without this a "resend" would report success while nothing can
        // actually be sent.
        if (!MailService::isConfigured()) {
            return [
                'success' => false,
                'messages' => ["Email verification isn't available just yet — we couldn't send a link. Your account works normally in the meantime."],
            ];
        }

        self::sendVerificationEmail($user, $resumeUrl);

        return [
            'success' => true,
            'messages' => [
                "A new activation link has been sent to <strong>{$email}</strong>. "
                    . 'Check your inbox (and your junk/spam folder) — it can take a few minutes to arrive.',
            ],
        ];
    }

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
            $email = trim($email);
            if ($email === '' || $token === '') {
                throw new \Exception('Invalid verification link.');
            }

            $verification = UserVerification::find($email);
            if (!$verification) {
                throw new \Exception('No pending verification found for this email.');
            }

            if ($verification->isExpired(60)) {
                $verification->delete();
                throw new \Exception('This verification link has expired. Please request a new one.');
            }

            if (!password_verify($token, $verification->token)) {
                throw new \Exception('Invalid or already-used verification link.');
            }

            $user = User::where('email', $email)->first();
            if (!$user) {
                throw new \Exception('We could not find an account matching this email.');
            }

            $user->status_id = 1;
            $user->email_verified = true;
            $user->save();

            $resumeUrl = $verification->resume_url;
            $verification->delete();

            // Logs the user straight in via the same session shape a real
            // password login gets (2-week cookie, api_token, user_last_log)
            // — this is what lets them "continue whatever they were doing
            // before having to sign up" without re-entering credentials.
            $login = AuthService::loginAsUser($user);
            $login['messages'] = ['Account verified.'];

            if ($resumeUrl) {
                $login['redirect_url'] = $resumeUrl;
            }

            return $login;
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }
}

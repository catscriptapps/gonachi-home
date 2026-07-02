<?php
// /src/Controller/TenantsController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Tenant;
use App\Models\UserVerification;
use Src\Service\MailService;
use App\Traits\RecentActivityLogger;

class TenantsController
{
    use RecentActivityLogger;

    /**
     * Handle Create for Tenants (self-service registration only — no edit/update yet).
     */
    public function save(array $data): array
    {
        try {
            $firstName = trim($data['first_name'] ?? '');
            $lastName  = trim($data['last_name'] ?? '');
            $email     = trim($data['email'] ?? '');
            $phone     = trim($data['phone'] ?? '') ?: null;

            if (empty($firstName) || empty($lastName)) {
                throw new \Exception("First and last name are required.");
            }
            if (empty($email)) {
                throw new \Exception("Email address is required.");
            }

            // Email uniqueness verification check across tenant accounts
            if (Tenant::where('email', $email)->exists()) {
                throw new \Exception("The email address '{$email}' is already registered to a tenant account.");
            }

            if (empty($data['password']) || strlen($data['password']) < 8) {
                throw new \Exception("Password must be at least 8 characters long.");
            }
            if ($data['password'] !== ($data['password_confirmation'] ?? '')) {
                throw new \Exception("Passwords do not match.");
            }

            $tenant = new Tenant();
            $tenant->first_name = $firstName;
            $tenant->last_name  = $lastName;
            $tenant->email      = $email;
            $tenant->phone      = $phone;
            $tenant->password   = password_hash($data['password'], PASSWORD_DEFAULT);

            $returnTo = $data['return_to'] ?? '/home';

            $appEnv = $_ENV['APP_ENV'] ?? '';
            $isLocal = $appEnv === 'local';

            $tenant->status_id = $isLocal ? 1 : 0;

            // If local, skip email verification: save, log the tenant in immediately, and
            // send them right back to wherever they registered from (e.g. the apply page).
            if ($isLocal) {
                $tenant->save();

                $_SESSION['tenant_id'] = $tenant->id;
                $_SESSION['tenant_email'] = $tenant->email;
                $_SESSION['tenant_full_name'] = $tenant->full_name;
                $_SESSION['account_type'] = 'tenant';

                static::logActivity("Registered tenant account: {$tenant->full_name} ({$tenant->email})", 'Tenants');

                return [
                    'success'         => true,
                    'is_registration' => true,
                    'redirect_url'    => $returnTo,
                ];
            }

            $tenant->save();

            // Production: require email verification before the account can be used.
            $token = bin2hex(random_bytes(32));

            UserVerification::updateOrCreate(
                ['email' => $email],
                [
                    'token'      => password_hash($token, PASSWORD_DEFAULT),
                    'created_at' => date('Y-m-d H:i:s'),
                ]
            );

            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
            $host     = $_SERVER['HTTP_HOST'];
            $envBase  = trim($_ENV['APP_BASE_PATH'] ?? '', '/');
            $fullBaseUrl = $protocol . $host . ($envBase ? '/' . $envBase : '');

            $activationLink = rtrim($fullBaseUrl, '/') . "/verify-account?token={$token}&email=" . urlencode($email) . "&redirect=" . urlencode($returnTo);

            $subject = "Activate Your Tenant Account";
            $body = "
                <div style='font-family: \"Quicksand\", sans-serif; color: #000000;'>
                    <h2 style='color: #0D9488;'>Welcome, {$tenant->first_name}!</h2>
                    <p>Your tenant account has been created. Please click the button below to verify your email address and activate your account:</p>
                    <div style='margin: 32px 0;'>
                        <a href='{$activationLink}' style='background-color: #0D9488; color: white; padding: 14px 28px; text-decoration: none; border-radius: 10px; font-weight: bold; display: inline-block; box-shadow: 0 4px 6px rgba(13, 148, 136, 0.2);'>Verify My Account</a>
                    </div>
                    <p style='font-size: 0.875rem; color: #818181;'>If the button doesn't function correctly, copy and paste this link: <br>{$activationLink}</p>
                </div>
            ";

            MailService::send($email, $subject, $body);

            static::logActivity("Tenant account pending verification: {$tenant->full_name} ({$tenant->email})", 'Tenants');

            return [
                'success'         => true,
                'is_registration' => true,
                'messages'        => ["Account created! We've sent an activation link to <strong>{$email}</strong>. Please verify it to continue."],
            ];
        } catch (\Throwable $e) {
            static::logActivity("Tenant registration failure: " . $e->getMessage(), 'Tenants');
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }
}

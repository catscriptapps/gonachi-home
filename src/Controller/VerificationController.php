<?php
// /src/Controller/VerificationController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Landlord;
use App\Models\Tenant;
use App\Models\UserVerification;

class VerificationController
{
    /**
     * Verify a guest-registration email/token pair (shared across Tenants and Landlords)
     * and, on success, log the matching account straight in.
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

            $tenant = Tenant::where('email', $email)->first();
            if ($tenant) {
                $tenant->status_id = 1;
                $tenant->save();
                $verification->delete();

                $_SESSION['tenant_id'] = $tenant->id;
                $_SESSION['tenant_email'] = $tenant->email;
                $_SESSION['tenant_full_name'] = $tenant->full_name;
                $_SESSION['account_type'] = 'tenant';

                return ['success' => true, 'messages' => ['Account verified.'], 'account_type' => 'tenant'];
            }

            $landlord = Landlord::where('email', $email)->first();
            if ($landlord) {
                $landlord->status_id = 1;
                $landlord->save();
                $verification->delete();

                $_SESSION['landlord_id'] = $landlord->id;
                $_SESSION['landlord_email'] = $landlord->email;
                $_SESSION['landlord_company_name'] = $landlord->company_name;
                $_SESSION['account_type'] = 'landlord';

                return ['success' => true, 'messages' => ['Account verified.'], 'account_type' => 'landlord'];
            }

            throw new \Exception("We could not find an account matching this email.");
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }
}

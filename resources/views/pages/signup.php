<?php
// /resources/views/pages/signup.php

declare(strict_types=1);

/**
 * Public self-registration page. Submits to server/api/register.php
 * (AuthController::register()), which auto-activates the account when
 * APP_ENV=local, or emails an activation link otherwise — see
 * VerificationController::verify() for the production completion path.
 *
 * Not project-specific, so — like account-created.php and verify-account.php
 * — this renders under the default app.php shell rather than one of the
 * three project layouts.
 *
 * @var string $baseUrl
 */

$redirect = sanitizeRedirectTarget($_GET['redirect'] ?? '');
$emailSent = isset($_GET['email_sent']);
$error = $_GET['error'] ?? null;
?>
<div class="max-w-md mx-auto py-10">

    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Create Your Account</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Join Gonachi to contribute, save searches, and unlock full records.</p>
    </div>

    <?php if ($emailSent): ?>
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-8 text-center shadow-sm">
            <svg class="h-10 w-10 text-primary-500 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
            <h4 class="text-base font-bold text-gray-900 dark:text-white">Check your email</h4>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">We've sent an activation link to your inbox. Click it to activate your account, then sign in.</p>
        </div>
    <?php else: ?>

        <?php if ($error): ?>
            <div class="flex items-start gap-3 bg-red-50 dark:bg-red-950/40 border border-red-100 dark:border-red-900/30 rounded-xl p-4 mb-6">
                <svg class="h-5 w-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M4.93 4.93a10 10 0 0114.14 0 10 10 0 010 14.14 10 10 0 01-14.14 0 10 10 0 010-14.14z" /></svg>
                <p class="text-xs text-red-700 dark:text-red-400"><?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>

        <form id="signup-form" method="POST" action="<?= $baseUrl ?>api/register" novalidate class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 sm:p-8 shadow-sm space-y-5">
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>" />

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="signup-first-name" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">First Name</label>
                    <input type="text" id="signup-first-name" name="first_name" required class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none text-gray-900 dark:text-white" />
                </div>
                <div>
                    <label for="signup-last-name" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Last Name</label>
                    <input type="text" id="signup-last-name" name="last_name" required class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none text-gray-900 dark:text-white" />
                </div>
            </div>

            <div>
                <label for="signup-email" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Email Address</label>
                <input type="email" id="signup-email" name="email" required class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none text-gray-900 dark:text-white" />
            </div>

            <div>
                <label for="signup-password" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Password</label>
                <input type="password" id="signup-password" name="password" required minlength="8" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none text-gray-900 dark:text-white" />
            </div>

            <div>
                <label for="signup-password-confirmation" class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Confirm Password</label>
                <input type="password" id="signup-password-confirmation" name="password_confirmation" required minlength="8" class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none text-gray-900 dark:text-white" />
            </div>

            <button type="submit" class="w-full px-6 py-2.5 bg-gray-900 hover:bg-gray-800 dark:bg-primary-600 dark:hover:bg-primary-500 text-white font-bold text-sm rounded-lg transition-colors shadow-sm">
                Create Account
            </button>

            <p class="text-center text-xs text-gray-400 dark:text-gray-500">
                Already have an account? <a href="<?= $baseUrl ?>login" data-login-button class="font-semibold text-primary-600 hover:underline">Sign In</a>
            </p>
        </form>
    <?php endif; ?>
</div>

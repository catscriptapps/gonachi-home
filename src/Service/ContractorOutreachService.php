<?php
// /src/Service/ContractorOutreachService.php

declare(strict_types=1);

namespace Src\Service;

use App\Models\Contractor;
use App\Models\ContractorOutreachLog;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Marketing outreach for auto-discovered, unclaimed contractors: rather
 * than waiting on luck for a business to stumble onto its own profile, an
 * admin can text/email them directly (using the phone/email
 * SerperContractorConnector dug up) inviting them to claim it. Sending is
 * always admin-triggered (see server/api/contractor-outreach-send.php) —
 * nothing here fires automatically on discovery.
 */
final class ContractorOutreachService
{
    /**
     * Unclaimed, active contractors with at least a phone or email to
     * reach them on — the admin outreach queue.
     */
    public static function outreachable(?string $channel = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = Contractor::where('status', 'active')
            ->where('claim_status', 'unclaimed');

        if ($channel === 'sms') {
            $query->whereNotNull('phone');
        } elseif ($channel === 'email') {
            $query->whereNotNull('email');
        } else {
            $query->where(fn($q) => $q->whereNotNull('phone')->orWhereNotNull('email'));
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * Substitutes {business_name}/{claim_url} tokens in an admin-supplied
     * template — used for bulk sends, where one shared, edited message
     * still needs to read as addressed to each specific business.
     */
    public static function personalize(Contractor $contractor, string $template): string
    {
        return str_replace(
            ['{business_name}', '{claim_url}'],
            [$contractor->business_name, self::profileUrl($contractor)],
            $template
        );
    }

    /**
     * Default, admin-editable marketing copy — filled in with the
     * contractor's own business name and a direct link to their profile
     * (where "Claim This Profile" lives).
     */
    public static function defaultMessage(Contractor $contractor): array
    {
        $claimUrl = self::profileUrl($contractor);

        $sms = "Hi! We found \"{$contractor->business_name}\" listed on Gonachi Contractor Discovery. Claim your free profile to get discovered by more customers and receive job requests directly: {$claimUrl}";

        $emailBody = "Hello,\n\n"
            . "This looks like your business — \"{$contractor->business_name}\" — on Gonachi Contractor Discovery.\n\n"
            . "Claiming your profile is free and only takes a minute. Once verified, you'll be able to:\n"
            . "  - Complete your profile with photos and certifications\n"
            . "  - Get found by customers actively searching for your services\n"
            . "  - Receive job requests directly\n\n"
            . "Claim your business profile here: {$claimUrl}\n\n"
            . "— The Gonachi Team";

        return [
            'sms' => $sms,
            'email_subject' => "Is \"{$contractor->business_name}\" your business? Claim your free profile",
            'email_body' => $emailBody,
        ];
    }

    /**
     * @return array{success: bool, message: string}
     */
    public static function sendSms(Contractor $contractor, string $message, ?int $adminUserId): array
    {
        if (!$contractor->phone) {
            return ['success' => false, 'message' => 'This contractor has no phone number on file.'];
        }

        $result = SmsService::send($contractor->phone, $message);

        ContractorOutreachLog::create([
            'contractor_id' => $contractor->id,
            'sent_by_user_id' => $adminUserId,
            'channel' => 'sms',
            'recipient' => $contractor->phone,
            'message' => $message,
            'status' => $result['success'] ? 'sent' : 'failed',
            'error_message' => $result['success'] ? null : $result['message'],
        ]);

        if ($result['success']) {
            $contractor->outreach_sms_sent_at = now();
            $contractor->save();
        }

        return $result;
    }

    /**
     * @return array{success: bool, message: string}
     */
    public static function sendEmail(Contractor $contractor, string $subject, string $body, ?int $adminUserId): array
    {
        if (!$contractor->email) {
            return ['success' => false, 'message' => 'This contractor has no email address on file.'];
        }

        $success = true;
        $errorMessage = null;

        try {
            MailService::send($contractor->email, $subject, nl2br(htmlspecialchars($body)));
        } catch (\Throwable $e) {
            $success = false;
            $errorMessage = $e->getMessage();
        }

        ContractorOutreachLog::create([
            'contractor_id' => $contractor->id,
            'sent_by_user_id' => $adminUserId,
            'channel' => 'email',
            'recipient' => $contractor->email,
            'message' => "Subject: {$subject}\n\n{$body}",
            'status' => $success ? 'sent' : 'failed',
            'error_message' => $errorMessage,
        ]);

        if ($success) {
            $contractor->outreach_email_sent_at = now();
            $contractor->save();
        }

        return ['success' => $success, 'message' => $success ? 'ok' : ($errorMessage ?? 'Email could not be sent.')];
    }

    private static function profileUrl(Contractor $contractor): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $envBase = trim($_ENV['APP_BASE_PATH'] ?? '', '/');
        $fullBaseUrl = $protocol . $host . ($envBase ? '/' . $envBase : '');

        return rtrim($fullBaseUrl, '/') . '/contractor/' . $contractor->id;
    }
}

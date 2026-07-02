<?php
// /src/Service/MailService.php

declare(strict_types=1);

namespace Src\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * MailService
 * Handles sending system emails using PHPMailer via Composer Autoload.
 */
class MailService
{
    /**
     * Sends an email.
     * * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body HTML content
     * @return bool
     * @throws Exception
     */
    public static function send(string $to, string $subject, string $body): bool
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();

            // GoDaddy internal relay settings
            $mail->Host         = 'localhost';
            $mail->SMTPAuth     = false;        // GoDaddy's internal relay doesn't require authentication
            $mail->SMTPAutoTLS  = false;
            $mail->SMTPSecure   = '';           // No encryption needed for the local loop
            $mail->Port         = 25;           // Port 25 is the standard for local relays

            // Recipients
            $mail->setFrom('noreply@pmbtracker.com', 'Property Management Brokers');
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);

            return $mail->send();
        } catch (Exception $e) {
            // Log the detailed error, but throw a clean one for the controller
            error_log("Mailer Error: {$mail->ErrorInfo}");
            throw new \Exception("The mail system is currently unavailable.");
        }
    }
}

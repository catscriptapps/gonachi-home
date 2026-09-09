<?php
// /src/Service/PaystackService.php

declare(strict_types=1);

namespace Src\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Thin wrapper around the Paystack REST API for the real-estate-leads
 * credit-pack purchase flow (see CreditService::PACKS / initiatePurchase() /
 * confirmPurchase()). Deliberately webhook-free: the client opens Paystack's
 * inline popup checkout with a reference we mint ourselves, and the moment
 * that popup reports success we verify the SAME reference server-side via
 * GET /transaction/verify/:reference before crediting anything — so nothing
 * needs a public callback URL, which matters for testing on localhost/XAMPP.
 * A missing secret key just means every call below no-ops/fails cleanly;
 * CreditService is what decides what that means for the caller.
 */
final class PaystackService
{
    private const BASE_URL = 'https://api.paystack.co';

    public static function isConfigured(): bool
    {
        return self::secretKey() !== null;
    }

    public static function publicKey(): ?string
    {
        $key = $_ENV['PAYSTACK_PUBLIC_KEY'] ?? (getenv('PAYSTACK_PUBLIC_KEY') ?: null);
        $key = $key !== null ? trim((string) $key) : '';
        return $key !== '' ? $key : null;
    }

    /**
     * Verifies a transaction reference against Paystack's own record of it.
     *
     * @return array{success: bool, status: ?string, amount: ?int, currency: ?string, message: string}
     */
    public static function verify(string $reference): array
    {
        $secretKey = self::secretKey();
        if (!$secretKey) {
            return ['success' => false, 'status' => null, 'amount' => null, 'currency' => null, 'message' => 'Payment gateway is not configured.'];
        }

        try {
            $client = new Client(['timeout' => 20]);
            $response = $client->get(self::BASE_URL . '/transaction/verify/' . rawurlencode($reference), [
                'headers' => ['Authorization' => 'Bearer ' . $secretKey],
            ]);

            $data = json_decode((string) $response->getBody(), true);
            $tx = $data['data'] ?? null;

            if (!is_array($data) || !($data['status'] ?? false) || !is_array($tx)) {
                return ['success' => false, 'status' => null, 'amount' => null, 'currency' => null, 'message' => $data['message'] ?? 'Verification failed.'];
            }

            return [
                'success' => true,
                'status' => $tx['status'] ?? null,
                'amount' => isset($tx['amount']) ? (int) $tx['amount'] : null,
                'currency' => $tx['currency'] ?? null,
                'message' => 'ok',
            ];
        } catch (GuzzleException $e) {
            error_log('PaystackService::verify failed: ' . $e->getMessage());
            return ['success' => false, 'status' => null, 'amount' => null, 'currency' => null, 'message' => 'Could not reach the payment gateway.'];
        }
    }

    private static function secretKey(): ?string
    {
        $key = $_ENV['PAYSTACK_SECRET_KEY'] ?? (getenv('PAYSTACK_SECRET_KEY') ?: null);
        $key = $key !== null ? trim((string) $key) : '';
        return $key !== '' ? $key : null;
    }
}

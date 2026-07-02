<?php
// /src/Controller/TenantPortalController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\AccessToken;

class TenantPortalController
{
    /**
     * Verify a tenant-submitted access token and hand back where to land next.
     */
    public function verifyToken(array $data): array
    {
        $tokenCode = trim((string)($data['access_token'] ?? ''));

        if ($tokenCode === '') {
            return ['success' => false, 'messages' => ['Please enter an access token.']];
        }

        $accessToken = AccessToken::where('token_code', $tokenCode)->first();

        if (!$accessToken) {
            return ['success' => false, 'messages' => ['We could not find an access token matching that code. Please check and try again.']];
        }

        if (!$accessToken->isActive()) {
            return ['success' => false, 'messages' => ['This access token has been revoked and is no longer valid. Please contact your landlord for a new one.']];
        }

        return [
            'success'      => true,
            'messages'     => ['Access token verified.'],
            'redirect_url' => '/apply/' . rawurlencode($accessToken->token_code),
        ];
    }

    /**
     * Load an access token with everything the tenant landing page needs to render.
     */
    public function loadByToken(string $tokenCode): ?AccessToken
    {
        if (trim($tokenCode) === '') {
            return null;
        }

        return AccessToken::with(['property.pictures', 'property.region', 'property.country', 'service'])
            ->where('token_code', $tokenCode)
            ->first();
    }
}

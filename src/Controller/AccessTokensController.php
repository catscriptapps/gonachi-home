<?php
// /src/Controller/AccessTokensController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\AccessToken;
use App\Models\Landlord;
use App\Models\Property;
use App\Models\Service;
use App\Utils\IdEncoder;
use Src\Service\AuthService;
use App\Traits\RecentActivityLogger;

class AccessTokensController
{
    use RecentActivityLogger;

    /**
     * Prepare data for the Access Tokens list page.
     * Optimized: Supports search and pagination, scoped to the current landlord.
     */
    public function index(): void
    {
        $currentLandlord = AuthService::currentLandlord();
        if (!$currentLandlord) {
            $GLOBALS['accessTokenRows'] = '';
            $GLOBALS['totalAccessTokensCount'] = 0;
            return;
        }

        $query = $_GET['q'] ?? '';
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 100;
        $offset = ($page - 1) * $perPage;

        $builder = AccessToken::with(['property', 'service'])
            ->where('landlord_id', (int)$currentLandlord->id);

        // Optional contextual filters (deep-linked from the property view modal)
        if (!empty($_GET['property_id'])) {
            $decodedPropertyId = IdEncoder::decode((string)$_GET['property_id']);
            if ($decodedPropertyId) {
                $builder->where('property_id', $decodedPropertyId);
            }
        }

        if (!empty($_GET['service_id']) && ctype_digit((string)$_GET['service_id'])) {
            $builder->where('service_id', (int)$_GET['service_id']);
        }

        if (!empty($query)) {
            $builder->where(function ($q) use ($query) {
                $q->where('token_code', 'LIKE', "%{$query}%")
                    ->orWhereHas('property', fn($p) => $p->where('property_name', 'LIKE', "%{$query}%"))
                    ->orWhereHas('service', fn($s) => $s->where('name', 'LIKE', "%{$query}%"));
            });
        }

        $totalFiltered = $builder->count();

        $tokens = $builder->orderBy('date_created', 'desc')
            ->offset($offset)
            ->limit($perPage)
            ->get();

        // AJAX response
        if (isset($_GET['q']) || isset($_GET['page'])) {
            header('Content-Type: application/json');

            echo json_encode([
                'success' => true,
                'data' => array_map(fn($t) => ['rowHtml' => self::renderRow($t)], $tokens->all()),
                'meta' => [
                    'total'   => $totalFiltered,
                    'loaded'  => $tokens->count(),
                    'hasMore' => ($offset + $tokens->count()) < $totalFiltered
                ]
            ]);
            exit;
        }

        // Standard Page Load
        $html = '';
        foreach ($tokens as $token) {
            $html .= self::renderRow($token);
        }

        $GLOBALS['accessTokenRows'] = $html;
        $GLOBALS['title'] = "Access Tokens";
        $GLOBALS['totalAccessTokensCount'] = $totalFiltered;
    }

    /**
     * Render individual Access Token table row HTML
     */
    public static function renderRow(AccessToken $token): string
    {
        $item = $token->toArray();
        $item['property_label'] = $token->property->portfolio_node_label ?? 'Unknown Property';
        $item['service_name'] = $token->service->name ?? 'Unknown Service';
        $item['created_at_formatted'] = $token->date_created ? $token->date_created->format('M j, Y') : 'N/A';

        $path = __DIR__ . '/../../resources/views/components/access-tokens/token-row.php';

        ob_start();
        try {
            include $path;
        } catch (\Throwable $e) {
            ob_end_clean();
            return "<tr><td class='p-4 text-red-500'>Render Error: " . $e->getMessage() . "</td></tr>";
        }
        return ob_get_clean();
    }

    /**
     * Handle creation of a new Access Token for a property + subscribed service.
     */
    public function save(array $data): array
    {
        try {
            $currentLandlord = AuthService::currentLandlord();
            if (!$currentLandlord) {
                throw new \Exception("Unauthorized: Valid session context for landlord profile not discovered.");
            }

            $encodedPropertyId = $data['property_id'] ?? null;
            $propertyId = $encodedPropertyId ? IdEncoder::decode((string)$encodedPropertyId) : null;
            $property = $propertyId ? Property::find($propertyId) : null;

            if (!$property || (int)$property->landlord_id !== (int)$currentLandlord->id) {
                throw new \Exception("The selected property could not be found.");
            }

            $serviceId = (int)($data['service_id'] ?? 0);
            $service = $serviceId > 0 ? Service::where('status_id', 1)->find($serviceId) : null;

            if (!$service) {
                throw new \Exception("The selected service module could not be found.");
            }

            if (!$currentLandlord->hasActiveService($service->id)) {
                throw new \Exception("You are not subscribed to {$service->name}.");
            }

            $hasActiveToken = AccessToken::where('property_id', $property->id)
                ->where('service_id', $service->id)
                ->where('status', 'active')
                ->exists();

            if ($hasActiveToken) {
                throw new \Exception("An active access token already exists for {$property->portfolio_node_label} on {$service->name}. Revoke it before creating a new one.");
            }

            $accessToken = AccessToken::create([
                'landlord_id' => (int)$currentLandlord->id,
                'property_id' => $property->id,
                'service_id'  => $service->id,
                'token_code'  => $this->generateTokenCode($currentLandlord),
                'status'      => 'active',
            ]);

            static::logActivity(
                "Created access token {$accessToken->token_code} for {$property->portfolio_node_label} ({$service->name})",
                'Access Tokens'
            );

            return [
                'success'  => true,
                'messages' => ["Access token {$accessToken->token_code} created successfully."],
                'data'     => $accessToken->toArray(),
                'rowHtml'  => self::renderRow($accessToken->load(['property', 'service'])),
            ];
        } catch (\Throwable $e) {
            static::logActivity("Access token creation failure: " . $e->getMessage(), 'Access Tokens');
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    /**
     * Revoke an access token so the underlying service can no longer be used
     * for that property. The row is kept for history, not deleted.
     */
    public function revoke(int|string|null $id): array
    {
        try {
            $currentLandlord = AuthService::currentLandlord();
            if (!$currentLandlord) {
                throw new \Exception("Unauthorized: Valid session context for landlord profile not discovered.");
            }

            $accessToken = is_numeric($id) ? AccessToken::find((int)$id) : null;

            if (!$accessToken || (int)$accessToken->landlord_id !== (int)$currentLandlord->id) {
                throw new \Exception("The selected access token could not be found.");
            }

            if ($accessToken->isRevoked()) {
                throw new \Exception("This access token has already been revoked.");
            }

            $accessToken->status = 'revoked';
            $accessToken->save();

            static::logActivity("Revoked access token {$accessToken->token_code}", 'Access Tokens');

            return [
                'success'  => true,
                'messages' => ["Access token {$accessToken->token_code} revoked."],
                'data'     => $accessToken->toArray(),
            ];
        } catch (\Throwable $e) {
            static::logActivity("Access token revoke failure: " . $e->getMessage(), 'Access Tokens');
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    /**
     * Generate a unique, human-readable token code, e.g. 'ACC-26-SIMCOE-4041'.
     */
    private function generateTokenCode(Landlord $landlord): string
    {
        $year = date('y');
        $slugSource = explode(' ', trim((string)($landlord->company_name ?: 'LL')))[0];
        $slug = strtoupper(preg_replace('/[^A-Za-z]/', '', $slugSource) ?: 'LL');
        $slug = substr($slug, 0, 8);

        do {
            $code = sprintf('ACC-%s-%s-%04d', $year, $slug, random_int(1000, 9999));
        } while (AccessToken::where('token_code', $code)->exists());

        return $code;
    }
}

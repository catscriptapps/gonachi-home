<?php
// /src/Controller/PropertiesController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Property;
use App\Utils\IdEncoder;
use Src\Service\AuthService;
use App\Traits\RecentActivityLogger;

class PropertiesController
{
    use RecentActivityLogger;

    /**
     * Handle Delete
     */
    public function delete(?string $id): array
    {
        try {
            $rawId = (is_string($id) && !is_numeric($id)) ? IdEncoder::decode($id) : (int)$id;
            $property = Property::find($rawId);

            if ($property) {
                $propertyName = $property->property_name;
                $unitNumber = $property->unit_number ? " (Unit {$property->unit_number})" : "";

                if ($property->delete()) {
                    static::logActivity("Deleted property asset: {$propertyName}{$unitNumber}", 'Properties');
                    return ['success' => true, 'messages' => ['Property deleted successfully.']];
                }
            }
            return ['success' => false, 'messages' => ['Failed to delete property.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    /**
     * Prepare data for the Properties List Page
     * Optimized: Supports infinite scroll and search
     */
    public function index(): void
    {
        $query = $_GET['q'] ?? '';
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 100;
        $offset = ($page - 1) * $perPage;

        $builder = Property::with(['country', 'region', 'landlord', 'pictures'])
            ->leftJoin('countries', 'properties.country_id', '=', 'countries.id')
            ->leftJoin('regions', 'properties.region_id', '=', 'regions.id')
            ->leftJoin('landlords', 'properties.landlord_id', '=', 'landlords.id')
            ->select('properties.*');

        if (!empty($query)) {
            $builder->where(function ($q) use ($query) {
                $q->where('properties.property_name', 'LIKE', "%{$query}%")
                    ->orWhere('properties.unit_number', 'LIKE', "%{$query}%")
                    ->orWhere('properties.address_line1', 'LIKE', "%{$query}%")
                    ->orWhere('properties.city', 'LIKE', "%{$query}%")
                    ->orWhere('properties.postal_code', 'LIKE', "%{$query}%")
                    ->orWhere('landlords.company_name', 'LIKE', "%{$query}%")
                    ->orWhere('countries.country', 'LIKE', "%{$query}%")
                    ->orWhere('regions.region', 'LIKE', "%{$query}%");
            });
        }

        // Contextual framing: If logged in as a landlord, restrict query bounds to their assets
        $currentLandlord = AuthService::currentLandlord();
        if ($currentLandlord) {
            $builder->where('properties.landlord_id', '=', (int)$currentLandlord->id);
        }

        $totalFiltered = $builder->count();

        $properties = $builder->orderBy('properties.date_created', 'desc')
            ->offset($offset)
            ->limit($perPage)
            ->get();

        // AJAX response
        if (isset($_GET['q']) || isset($_GET['page'])) {
            header('Content-Type: application/json');

            echo json_encode([
                'success' => true,
                'data' => array_map(fn($p) => ['cardHtml' => self::renderCard($p)], $properties->all()),
                'meta' => [
                    'total'   => $totalFiltered,
                    'loaded'  => $properties->count(),
                    'hasMore' => ($offset + $properties->count()) < $totalFiltered
                ]
            ]);
            exit;
        }

        // Standard Page Load
        $html = '';
        foreach ($properties as $property) {
            $html .= self::renderCard($property);
        }

        $GLOBALS['propertyCards'] = $html;
        $GLOBALS['title'] = "Properties";
        $GLOBALS['totalPropertiesCount'] = $totalFiltered;
    }

    /**
     * Render individual Property Card HTML
     */
    public static function renderCard(\App\Models\Property $property): string
    {
        $item = $property->toArray();

        $GLOBALS['assetBase'] = getAssetBase();

        // Object Relations Mapping
        $item['country_name'] = $property->country->country ?? 'N/A';
        $item['region_name']  = $property->region->region ?? 'N/A';

        // Owner (Landlord) data
        $landlord = $property->landlord;
        $item['landlord_name']   = $landlord->company_name ?? 'N/A';
        $item['landlord_avatar'] = $landlord->avatar_url ?? null;

        $landlordRegion  = $landlord->region->region ?? 'N/A';
        $landlordCountry = $landlord->country->country ?? 'N/A';
        $item['owner_location'] = $landlordRegion . ', ' . $landlordCountry;
        $item['user_types_json'] = '["Landlord"]';

        // Encoding ID for security
        $item['encoded_id'] = IdEncoder::encode((int)$property->id);
        $item['created_at_formatted'] = $property->date_created ? $property->date_created->format('M j, Y') : 'N/A';

        // Thumbnail: first picture ordered by pos_index
        $firstPic = $property->pictures()->orderBy('pos_index', 'asc')->first();
        $item['thumbnail'] = $firstPic ? $firstPic->pic_name : null;

        // Map of service_id => token_code for this property's active access tokens
        $item['active_tokens_by_service'] = $property->tokens()->where('status', 'active')->pluck('token_code', 'service_id')->toArray();

        $path = __DIR__ . '/../../resources/views/components/properties/data-card.php';

        ob_start();
        try {
            $assetBase = getAssetBase();
            include $path;
        } catch (\Throwable $e) {
            ob_end_clean();
            return "<div class='p-4 text-red-500'>Render Error: " . $e->getMessage() . "</div>";
        }
        return ob_get_clean();
    }

    /**
     * Handle Create or Update for Properties
     */
    public function save(array $data): array
    {
        try {
            $encodedId = $data['encoded_id'] ?? null;
            $propertyName = trim($data['property_name'] ?? '');
            $isNew = empty($encodedId);

            if (empty($propertyName)) throw new \Exception("Property asset title classification name is required.");

            $currentLandlord = AuthService::currentLandlord();
            if (!$currentLandlord) {
                throw new \Exception("Unauthorized: Valid session context for landlord profile not discovered.");
            }

            $propertyId = !$isNew ? IdEncoder::decode($encodedId) : null;
            $property = $propertyId ? Property::find($propertyId) : new Property();

            if (!$property) throw new \Exception("Property asset entry context details not found.");

            $property->property_name  = $propertyName;
            $property->unit_number    = !empty($data['unit_number']) ? trim($data['unit_number']) : null;

            if ($isNew) $property->landlord_id = (int)$currentLandlord->id;

            $property->address_line1  = $data['address_line1'] ?? null;
            $property->city           = $data['city'] ?? null;
            $property->postal_code    = $data['postal_code'] ?? null;
            $property->country_id     = isset($data['country_id']) && (int)$data['country_id'] > 0 ? (int)$data['country_id'] : null;

            $tableRegionId       = (int)($data['region_id'] ?? 0);
            $property->region_id = $tableRegionId > 0 ? $tableRegionId : null;
            $property->is_active = isset($data['is_active']) ? (int)$data['is_active'] : 1;

            $property->save();

            $property->load(['country', 'region', 'landlord', 'pictures']);

            $actionLabel = $isNew ? "Registered portfolio property asset" : "Updated property asset details";
            $unitText = $property->unit_number ? " (Unit {$property->unit_number})" : "";
            static::logActivity("{$actionLabel}: {$property->property_name}{$unitText}", 'Properties');

            return [
                'success'     => true,
                'property_id' => $property->id,
                'data'        => $property->toArray(),
                'cardHtml'    => self::renderCard($property),
                'messages'    => ['Property management records updated successfully.']
            ];
        } catch (\Throwable $e) {
            static::logActivity("Property entity write failure: " . $e->getMessage(), 'Properties');
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }
}

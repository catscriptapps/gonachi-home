<?php
// /src/Controller/LandlordServicesController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Service;
use App\Models\LandlordService;
use Src\Service\AuthService;
use App\Traits\RecentActivityLogger;

class LandlordServicesController
{
    use RecentActivityLogger;

    /**
     * Subscribe the current landlord to a service module.
     */
    public function save(array $data): array
    {
        try {
            $currentLandlord = AuthService::currentLandlord();
            if (!$currentLandlord) {
                throw new \Exception("Unauthorized: Valid session context for landlord profile not discovered.");
            }

            $serviceId = (int)($data['service_id'] ?? 0);
            $service = $serviceId > 0 ? Service::where('status_id', 1)->find($serviceId) : null;

            if (!$service) {
                throw new \Exception("The selected service module could not be found.");
            }

            $alreadySubscribed = LandlordService::where('landlord_id', $currentLandlord->id)
                ->where('service_id', $service->id)
                ->exists();

            if ($alreadySubscribed) {
                throw new \Exception("You are already subscribed to {$service->name}.");
            }

            $landlordService = LandlordService::create([
                'landlord_id' => (int)$currentLandlord->id,
                'service_id'  => $service->id,
                'status_id'   => 1,
            ]);

            static::logActivity("Subscribed to service module: {$service->name}", 'Services');

            return [
                'success'  => true,
                'messages' => ["Successfully subscribed to {$service->name}."],
                'data'     => $landlordService->toArray(),
            ];
        } catch (\Throwable $e) {
            static::logActivity("Service subscription failure: " . $e->getMessage(), 'Services');
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }
}

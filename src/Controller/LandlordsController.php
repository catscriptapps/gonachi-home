<?php
// /src/Controller/LandlordsController.php

declare(strict_types=1);

namespace Src\Controller;

use App\Models\Landlord;
use App\Utils\IdEncoder;
use Src\Service\AuthService;
use App\Traits\RecentActivityLogger;

class LandlordsController
{
    use RecentActivityLogger;

    /**
     * Handle Delete
     * @param string|null $id
     * @return array
     */
    public function delete(?string $id): array
    {
        try {
            $rawId = (is_string($id) && !is_numeric($id)) ? IdEncoder::decode($id) : (int)$id;
            $landlord = Landlord::find($rawId);

            if ($landlord) {
                $companyName = $landlord->company_name;
                $email = $landlord->email;

                if ($landlord->delete()) {
                    static::logActivity("Deleted landlord account: {$companyName} ({$email})", 'Landlords');
                    return ['success' => true, 'messages' => ['Landlord deleted successfully.']];
                }
            }
            return ['success' => false, 'messages' => ['Failed to delete landlord.']];
        } catch (\Throwable $e) {
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }

    /**
     * Prepare data for the Landlords List Page
     * Optimized: Supports infinite scroll and search
     * @return void
     */
    public function index(): void
    {
        $query = $_GET['q'] ?? '';
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 100;
        $offset = ($page - 1) * $perPage;

        $builder = Landlord::with(['country', 'region'])
            ->leftJoin('countries', 'landlords.country_id', '=', 'countries.id')
            ->leftJoin('regions', 'landlords.region_id', '=', 'regions.id')
            ->select('landlords.*');

        if (!empty($query)) {
            $builder->where(function ($q) use ($query) {
                $q->where('landlords.company_name', 'LIKE', "%{$query}%")
                    ->orWhere('landlords.email', 'LIKE', "%{$query}%")
                    ->orWhere('landlords.phone', 'LIKE', "%{$query}%")
                    ->orWhere('landlords.tax_id', 'LIKE', "%{$query}%")
                    ->orWhere('landlords.city', 'LIKE', "%{$query}%")
                    ->orWhere('countries.country', 'LIKE', "%{$query}%")
                    ->orWhere('regions.region', 'LIKE', "%{$query}%");
            });
        }

        $totalFiltered = $builder->count();

        $landlords = $builder->orderBy('landlords.date_created', 'desc')
            ->offset($offset)
            ->limit($perPage)
            ->get();

        // AJAX response
        if (isset($_GET['q']) || isset($_GET['page'])) {
            header('Content-Type: application/json');

            echo json_encode([
                'success' => true,
                'data' => array_map(fn($l) => ['rowHtml' => self::renderRow($l)], $landlords->all()),
                'meta' => [
                    'total' => $totalFiltered,
                    'loaded' => $landlords->count(),
                    'hasMore' => ($offset + $landlords->count()) < $totalFiltered
                ]
            ]);
            exit;
        }

        // Standard Page Load logic
        $html = '';
        foreach ($landlords as $landlord) {
            $html .= self::renderRow($landlord);
        }

        $GLOBALS['landlordRows'] = $html;
        $GLOBALS['title'] = "Landlords";
        $GLOBALS['totalLandlordsCount'] = $totalFiltered;
    }

    /**
     * Render individual table row HTML
     * @param Landlord $landlord
     * @return string
     */
    public static function renderRow(\App\Models\Landlord $landlord): string
    {
        $rowItem = $landlord->toArray();

        $GLOBALS['assetBase'] = getAssetBase();

        // Location Mapping
        $rowItem['country_name'] = $landlord->country->country ?? 'N/A';
        $rowItem['region_name']  = $landlord->region->region ?? 'N/A';

        // Encoding ID for security
        $rowItem['encoded_id'] = IdEncoder::encode((int)$landlord->id);
        $rowItem['created_at_formatted'] = $landlord->date_created ? $landlord->date_created->format('M j, Y') : 'N/A';

        $path = __DIR__ . '/../../resources/views/components/landlords/data-row.php';

        ob_start();
        try {
            $assetBase = getAssetBase();
            include $path;
        } catch (\Throwable $e) {
            ob_end_clean();
            return "<tr><td colspan='6'>Render Error: " . $e->getMessage() . "</td></tr>";
        }
        return ob_get_clean();
    }

    /**
     * Handle Create or Update for Landlords
     * @param array $data
     * @return array
     */
    public function save(array $data): array
    {
        try {
            $isEditMode = isset($data['_method']) && $data['_method'] === 'PUT';
            $encodedId = $data['encoded_id'] ?? null;
            $email = trim($data['email'] ?? '');
            $isNew = empty($encodedId);

            if (empty($email)) throw new \Exception("Operations email address is required.");

            // Determine the landlord instance
            $landlordId = !$isNew ? IdEncoder::decode($encodedId) : null;
            $landlord = $landlordId ? Landlord::find($landlordId) : new Landlord();

            if (!$landlord) throw new \Exception("Landlord profile not found.");

            // Email uniqueness verification check across landlord portfolios
            $existingQuery = Landlord::where('email', $email);
            if ($landlord->exists) {
                $existingQuery->where('id', '!=', $landlord->id);
            }
            if ($existingQuery->exists()) {
                throw new \Exception("The email address '{$email}' is already mapped to an active landlord entity.");
            }

            // Bind values to active database tracking schemas
            $landlord->company_name  = $data['company_name'] ?? '';
            $landlord->tax_id        = $data['tax_id'] ?? null;
            $landlord->email         = $email;
            $landlord->phone         = $data['phone'] ?? null;
            $landlord->address_line1 = $data['address_line1'] ?? null;
            $landlord->address_line2 = $data['address_line2'] ?? null;
            $landlord->city          = $data['city'] ?? null;
            $landlord->postal_code   = $data['postal_code'] ?? null;
            $landlord->country_id    = isset($data['country_id']) && (int)$data['country_id'] > 0 ? (int)$data['country_id'] : null;

            $tableRegionId           = (int)($data['region_id'] ?? 0);
            $landlord->region_id     = $tableRegionId > 0 ? $tableRegionId : null;

            // Securely hash the password if it's being set or changed
            if (!empty($data['password'])) {
                if ($isNew) {
                    if (strlen($data['password']) < 8) {
                        throw new \Exception("Password must be at least 8 characters long.");
                    }
                    if ($data['password'] !== ($data['password_confirmation'] ?? '')) {
                        throw new \Exception("Passwords do not match.");
                    }
                }
                // Note: We only require password_confirmation on creation.
                // For edits, a password change would be a separate, dedicated form/flow.

                $landlord->password = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            $appEnv = $_ENV['APP_ENV'] ?? '';
            $isLocal = $appEnv === 'local';

            if ($isNew) {
                $landlord->status_id = $isLocal ? 1 : 0;

                // If local, we skip email verification and go straight to success page
                if ($isLocal) {
                    $landlord->save();
                    return [
                        'success' => true,
                        'is_registration' => true,
                        'redirect_url' => '/account-created' // Signal for frontend redirect
                    ];
                }
            }

            $landlord->save();

            // Handle Guest Verification Emails if not local or auto-verified environment contexts
            if ($isNew && !$isLocal) {
                $token = bin2hex(random_bytes(32));

                \App\Models\UserVerification::updateOrCreate(
                    ['email' => $email],
                    [
                        'token' => password_hash($token, PASSWORD_DEFAULT),
                        'created_at' => date('Y-m-d H:i:s')
                    ]
                );

                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
                $host     = $_SERVER['HTTP_HOST'];
                $envBase  = trim($_ENV['APP_BASE_PATH'] ?? '', '/');
                $fullBaseUrl = $protocol . $host . ($envBase ? '/' . $envBase : '');

                $activationLink = rtrim($fullBaseUrl, '/') . "/verify-account?token={$token}&email=" . urlencode($email);

                $subject = "Activate Your Landlord Corporate Account";
                $body = "
                <div style='font-family: \"Quicksand\", sans-serif; color: #000000;'>
                    <h2 style='color: #0D9488;'>Welcome to the Platform, {$landlord->company_name}!</h2>
                    <p>Your institutional landlord operational profile has been requested. Please click the button below to verify your administrative email address and clear your portal profile:</p>
                    <div style='margin: 32px 0;'>
                        <a href='{$activationLink}' style='background-color: #0D9488; color: white; padding: 14px 28px; text-decoration: none; border-radius: 10px; font-weight: bold; display: inline-block; box-shadow: 0 4px 6px rgba(13, 148, 136, 0.2);'>Verify Corporate Profile</a>
                    </div>
                    <p style='font-size: 0.875rem; color: #818181;'>If the button doesn't function correctly, copy and paste this link: <br>{$activationLink}</p>
                </div>
            ";

                \Src\Service\MailService::send($email, $subject, $body);

                return [
                    'success' => true,
                    'is_registration' => true,
                    'messages' => ["Corporate entity registered! We've sent a portal authorization link to <strong>{$email}</strong>. Please verify it to activate the asset pipeline."]
                ];
            }

            $landlord->load(['country', 'region']);

            $actionLabel = $isNew ? "Created landlord entity profile" : "Updated landlord entity profile";
            static::logActivity("{$actionLabel}: {$landlord->company_name} ({$landlord->email})", 'Landlords');

            return [
                'success'  => true,
                'landlord_id' => $landlord->id,
                'data'     => $landlord->toArray(),
                'rowHtml'  => self::renderRow($landlord),
                'messages' => ['Landlord configuration entry cached successfully.']
            ];
        } catch (\Throwable $e) {
            static::logActivity("Landlord entity write failure: " . $e->getMessage(), 'Landlords');
            return ['success' => false, 'messages' => [$e->getMessage()]];
        }
    }
}

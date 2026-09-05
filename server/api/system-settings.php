<?php
// /server/api/system-settings.php
//
// Admin-only: read (GET) or update (POST) the global system settings —
// currently the lead-scraping and contractor-discovery on/off switches,
// read by scripts/cron/run-lead-extraction.php and
// run-contractor-discovery.php before they poll any sources.

declare(strict_types=1);

use Src\Service\AuthService;
use App\Models\SystemSetting;

header('Content-Type: application/json');

if (!AuthService::isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'messages' => ['Forbidden.']]);
    exit;
}

$setting = SystemSetting::current();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];

    if (array_key_exists('leads_scraping_enabled', $input)) {
        $setting->leads_scraping_enabled = (bool) $input['leads_scraping_enabled'];
    }

    if (array_key_exists('contractor_discovery_enabled', $input)) {
        $setting->contractor_discovery_enabled = (bool) $input['contractor_discovery_enabled'];
    }

    $setting->save();
}

echo json_encode([
    'success' => true,
    'leads_scraping_enabled' => (bool) $setting->leads_scraping_enabled,
    'contractor_discovery_enabled' => (bool) $setting->contractor_discovery_enabled,
]);

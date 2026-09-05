<?php
// /server/models/SystemSetting.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Singleton settings row (always exactly one, id=1) for global admin
 * toggles that apply across the whole system — currently just the
 * scraping-engine on/off switches surfaced on the Settings page and read by
 * scripts/cron/run-lead-extraction.php and run-contractor-discovery.php.
 */
class SystemSetting extends Model
{
    protected $table = 'system_settings';

    protected $fillable = [
        'leads_scraping_enabled',
        'contractor_discovery_enabled',
    ];

    protected $casts = [
        'leads_scraping_enabled' => 'boolean',
        'contractor_discovery_enabled' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1], [
            'leads_scraping_enabled' => true,
            'contractor_discovery_enabled' => true,
        ]);
    }

    public static function isLeadsScrapingEnabled(): bool
    {
        return (bool) static::current()->leads_scraping_enabled;
    }

    public static function isContractorDiscoveryEnabled(): bool
    {
        return (bool) static::current()->contractor_discovery_enabled;
    }
}

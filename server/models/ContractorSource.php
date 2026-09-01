<?php
// /server/models/ContractorSource.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Registry of contractor-discovery data sources — mirrors LeadSource.
 */
class ContractorSource extends Model
{
    protected $table = 'cde_contractor_sources';

    protected $fillable = [
        'name',
        'slug',
        'type',
        'connector_class',
        'base_url',
        'config',
        'is_active',
        'poll_interval_minutes',
        'last_polled_at',
    ];

    protected $casts = [
        'config'                 => 'array',
        'is_active'              => 'boolean',
        'poll_interval_minutes'  => 'integer',
        'last_polled_at'         => 'datetime',
        'created_at'             => 'datetime',
        'updated_at'             => 'datetime',
    ];

    public function contractors()
    {
        return $this->hasMany(Contractor::class);
    }

    public function discoveryRuns()
    {
        return $this->hasMany(ContractorDiscoveryRun::class);
    }

    public function isDueForPoll(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if (!$this->last_polled_at) {
            return true;
        }

        return $this->last_polled_at->diffInMinutes(\Carbon\Carbon::now()) >= $this->poll_interval_minutes;
    }
}

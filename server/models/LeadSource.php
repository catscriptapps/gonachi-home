<?php
// /server/models/LeadSource.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadSource extends Model
{
    protected $table = 'rel_lead_sources';

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

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function extractionRuns()
    {
        return $this->hasMany(LeadExtractionRun::class);
    }

    /**
     * Whether this source is due for another poll, based on last_polled_at
     * and its own poll_interval_minutes.
     */
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

<?php
// /server/models/LeadExtractionRun.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadExtractionRun extends Model
{
    protected $table = 'rel_lead_extraction_runs';

    protected $fillable = [
        'lead_source_id',
        'started_at',
        'finished_at',
        'items_found',
        'items_new',
        'items_duplicate',
        'items_rejected',
        'status',
        'error_message',
    ];

    protected $casts = [
        'lead_source_id'   => 'integer',
        'started_at'       => 'datetime',
        'finished_at'      => 'datetime',
        'items_found'      => 'integer',
        'items_new'        => 'integer',
        'items_duplicate'  => 'integer',
        'items_rejected'   => 'integer',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    public function source()
    {
        return $this->belongsTo(LeadSource::class, 'lead_source_id');
    }
}

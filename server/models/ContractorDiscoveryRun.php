<?php
// /server/models/ContractorDiscoveryRun.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractorDiscoveryRun extends Model
{
    protected $table = 'cde_contractor_discovery_runs';

    protected $fillable = [
        'contractor_source_id',
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
        'contractor_source_id' => 'integer',
        'started_at'    => 'datetime',
        'finished_at'   => 'datetime',
        'items_found'   => 'integer',
        'items_new'     => 'integer',
        'items_duplicate' => 'integer',
        'items_rejected' => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    public function source()
    {
        return $this->belongsTo(ContractorSource::class, 'contractor_source_id');
    }
}

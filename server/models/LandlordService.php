<?php
// /server/models/LandlordService.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Modernized Landlord Service Cross-Reference Pivot Model
 * Table: landlord_service
 */
class LandlordService extends Pivot
{
    protected $table = 'landlord_services';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'landlord_id',
        'service_id',
        'status_id', // 1 = Active, 0 = Inactive
        'expires_at',
        'date_created',
        'timestamp',
    ];

    protected $casts = [
        'id'          => 'integer',
        'landlord_id' => 'integer',
        'service_id'  => 'integer',
        'status_id'   => 'integer',
        'expires_at'   => 'datetime',
        'date_created' => 'datetime',
        'timestamp'    => 'datetime',
    ];

    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'timestamp';

    // ============================================================
    // Relationships
    // ============================================================

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(Landlord::class, 'landlord_id', 'id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id', 'id');
    }
}

<?php
// /server/models/AccessToken.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modernized Access Token Model
 * Table: access_tokens
 */
class AccessToken extends Model
{
    protected $table = 'access_tokens';

    /**
     * Standardized primary key mapping.
     */
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * Attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'landlord_id',
        'property_id',
        'service_id',
        'token_code',  // Unique identifier string (e.g., 'ACC-26-SIMCOE-4041')
        'status',      // 'active' | 'revoked' - exactly one active token per property + service
        'date_created',
        'timestamp',
    ];

    /**
     * Attribute casting for automated type safety and schema validation defaults.
     */
    protected $casts = [
        'id'           => 'integer',
        'landlord_id'  => 'integer',
        'property_id'  => 'integer',
        'service_id'   => 'integer',
        'date_created' => 'datetime',
        'timestamp'    => 'datetime',
    ];

    /**
     * Mapping legacy timestamp names to Eloquent defaults.
     */
    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'timestamp';

    // ============================================================
    // Relationships
    // ============================================================

    /**
     * Link to the parent Landlord context framework who instantiated the token code.
     */
    public function landlord(): BelongsTo
    {
        return $this->belongsTo(Landlord::class, 'landlord_id', 'id');
    }

    /**
     * Link to the specific target property unit node assigned to this pipeline channel.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id', 'id');
    }

    /**
     * Link to the subscribed service module this token grants access to.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id', 'id');
    }

    // ============================================================
    // Logic & Pipeline States
    // ============================================================

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isRevoked(): bool
    {
        return $this->status === 'revoked';
    }
}

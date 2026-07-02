<?php
// /server/models/Subscription.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modernized Subscription Model
 * Table: subscriptions
 */
class Subscription extends Model
{
    protected $table = 'subscriptions';

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
        'tier_code',      // e.g., 'basic', 'portfolio', 'enterprise'
        'status',         // e.g., 'active', 'suspended', 'cancelled'
        'token_limit',    // Maximum access keys pooled allowed for this tier
        'token_count',    // Currently instantiated access keys active in the pipeline
        'monthly_inflow', // Financial allocation value track
        'date_created',
        'timestamp',
    ];

    /**
     * Attribute casting for automated type safety.
     */
    protected $casts = [
        'id'           => 'integer',
        'landlord_id'  => 'integer',
        'token_limit'  => 'integer',
        'token_count'  => 'integer',
        'monthly_inflow' => 'decimal:2',
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
     * Link back to the parent Landlord account entity node.
     */
    public function landlord(): BelongsTo
    {
        return $this->belongsTo(Landlord::class, 'landlord_id', 'id');
    }

    // ============================================================
    // Logic & Helpers
    // ============================================================

    /**
     * Determine if the subscription level pool has reached its max capacity.
     */
    public function isPoolExhausted(): bool
    {
        return $this->token_count >= $this->token_limit;
    }
}

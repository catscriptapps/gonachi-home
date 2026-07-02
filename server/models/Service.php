<?php
// /server/models/Service.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Modernized Service Model
 * Table: services
 */
class Service extends Model
{
    protected $table = 'services';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * Attributes that are mass assignable for each platform module.
     */
    protected $fillable = [
        'id',
        'name',
        'slug',
        'short_description',
        'long_description',
        'status_id',
        'date_created',
        'timestamp',
    ];

    /**
     * Attribute casting for automated type safety.
     */
    protected $casts = [
        'id'        => 'integer',
        'status_id' => 'integer',
        'date_created' => 'datetime',
        'timestamp'    => 'datetime',
    ];

    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'timestamp';

    // ============================================================
    // Relationships
    // ============================================================

    /**
     * Direct link to individual subscription pivot records.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'service_id', 'id');
    }

    /**
     * Many-to-Many connection to the subscribed Landlords via the pivot table.
     */
    public function landlords(): BelongsToMany
    {
        return $this->belongsToMany(Landlord::class, 'landlord_service', 'service_id', 'landlord_id')
            ->withPivot(['id', 'status_id', 'expires_at', 'date_created', 'timestamp'])
            ->withTimestamps('date_created', 'timestamp');
    }
}

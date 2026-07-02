<?php
// /server/models/Tenant.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modernized Tenant Model
 * Table: tenants
 */
class Tenant extends Model
{
    protected $table = 'tenants';

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
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'status_id',
        'date_created',
        'timestamp',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Attribute casting for automated type safety.
     */
    protected $casts = [
        'id'           => 'integer',
        'status_id'    => 'integer',
        'date_created' => 'datetime',
        'timestamp'    => 'datetime',
    ];

    /**
     * Mapping legacy timestamp names to Eloquent defaults.
     */
    const CREATED_AT = 'date_created';
    const UPDATED_AT = 'timestamp';

    // ============================================================
    // Accessors & Logic
    // ============================================================

    /**
     * Virtual attribute for $tenant->full_name
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}

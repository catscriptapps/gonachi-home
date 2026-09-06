<?php
// /server/models/User.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modernized User Model
 * Table: users
 * * Brand Colors: Primary (Orange), Secondary (Navy)
 */
class User extends Model
{
    protected $table = 'users';

    /**
     * Standardized primary key.
     * We map the legacy user_id to 'id' during our reset/migration scripts.
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
        'country_id',
        'region_id',
        'city',
        'user_code',
        'password',
        'api_token',
        'status_id',
        'date_created',
        'user_last_log',
        'avatar_url',
        'email_verified',
        'timestamp',
        'user_type_ids', // Stores role array (e.g. [1, 2])
    ];

    /**
     * Attribute casting for automated JSON handling and Date objects.
     */
    protected $casts = [
        'id'             => 'integer',
        'country_id'     => 'integer',
        'region_id'      => 'integer',
        'status_id'      => 'integer',
        'email_verified' => 'boolean',
        'date_created'   => 'datetime',
        'user_last_log'  => 'datetime',
        'timestamp'      => 'datetime',
        'user_type_ids'  => 'array', // Automatically handles json_encode/decode
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
     * Link to the Country model.
     * Maps users.country_id -> countries.id
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    /**
     * Link to the Region model.
     * Maps users.region_id -> regions.id
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id', 'id');
    }

    // ============================================================
    // Accessors & Logic
    // ============================================================

    /**
     * Virtual attribute for $user->full_name
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Helper to check for a specific role/type. See the users_types table
     * (App\Models\UserType) for the current id -> name mapping — Admin = 1,
     * Landlord = 2, Tenant = 3, Property Manager = 4, Real Estate Agent = 5,
     * Contractor = 6, Mortgage Broker = 7, User = 8.
     */
    public function hasType(int $typeId): bool
    {
        return is_array($this->user_type_ids) && in_array($typeId, $this->user_type_ids);
    }
}

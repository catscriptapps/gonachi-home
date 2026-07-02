<?php
// /server/models/Landlord.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\LandlordService;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Modernized Landlord Model
 * Table: landlords
 */
class Landlord extends Model
{
    protected $table = 'landlords';

    /**
     * Standardized primary key.
     * Maps the legacy landlord_id to 'id' via our migration strategies.
     */
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * Attributes that are mass assignable for each landlord.
     */
    protected $fillable = [
        'id',
        'company_name',
        'email',
        'password',
        'phone',
        'country_id',
        'region_id',
        'city',
        'address_line1',
        'address_line2',
        'postal_code',
        'tax_id',
        'status_id',
        'avatar_url',
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
     * Attribute casting for automated JSON handling and Date objects.
     */
    protected $casts = [
        'id'           => 'integer',
        'country_id'   => 'integer',
        'region_id'    => 'integer',
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
    // Relationships
    // ============================================================

    /**
     * Link to the Country lookup model.
     * Maps landlords.country_id -> countries.id
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    /**
     * Link to the Region lookup model.
     * Maps landlords.region_id -> regions.id
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id', 'id');
    }

    /**
     * Link to the Landlord's original macro billing tier subscription records.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'landlord_id', 'id');
    }

    /**
     * Link to individual active feature modules using the new cross-reference model.
     */
    public function activeServices(): HasMany
    {
        return $this->hasMany(LandlordService::class, 'landlord_id', 'id');
    }

    /**
     * Many-to-Many connection to platform Services via the landlord_service table.
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, (new LandlordService())->getTable(), 'landlord_id', 'service_id')
            ->using(LandlordService::class)
            ->withPivot(['id', 'status_id', 'expires_at', 'date_created', 'timestamp'])
            ->withTimestamps('date_created', 'timestamp');
    }

    /**
     * Link to managed portfolio asset entity nodes.
     */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'landlord_id', 'id');
    }

    // ============================================================
    // Accessors & Logic
    // ============================================================

    /**
     * Virtual attribute for $landlord->full_address block strings
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_line1,
            $this->address_line2,
            $this->city,
            $this->region->name ?? null,
            $this->postal_code,
            $this->country->name ?? null
        ]);

        return implode(', ', $parts);
    }

    /**
     * Checks if this landlord possesses a valid active deployment for a given service suite.
     */
    public function hasActiveService(int $serviceId): bool
    {
        return $this->activeServices()
            ->where('service_id', $serviceId)
            ->where('status_id', 1)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', \Illuminate\Support\Carbon::now());
            })
            ->exists();
    }
}

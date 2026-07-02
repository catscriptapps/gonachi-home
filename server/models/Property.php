<?php
// /server/models/Property.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\AccessToken;
use App\Models\Country;
use App\Models\PropertyPic;
use App\Models\Region;

/**
 * Modernized Property Model
 * Table: properties
 */
class Property extends Model
{
    protected $table = 'properties';

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
        'property_name',   // e.g., 'Victoria Complex' or '88 Lakeshore Rd'
        'unit_number',     // e.g., 'Suite 404' or 'Townhouse 12'
        'address_line1',
        'city',
        'region_id',
        'country_id',
        'postal_code',
        'is_active',
        'views',
        'date_created',
        'timestamp',
    ];

    /**
     * Attribute casting for automated type safety.
     */
    protected $casts = [
        'id'           => 'integer',
        'landlord_id'  => 'integer',
        'is_active'    => 'boolean',
        'city'         => 'string',
        'region_id'    => 'integer',
        'country_id'   => 'integer',
        'views'        => 'integer',
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
     * Link back to the owning Landlord profile framework context.
     */
    public function landlord(): BelongsTo
    {
        return $this->belongsTo(Landlord::class, 'landlord_id', 'id');
    }

    /**
     * Link to the assigned regional/state market structure boundary.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id', 'id');
    }

    /**
     * Link to the operational country system context.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    /**
     * Link to generated intake hooks/access keys mapped to this specific property unit.
     */
    public function tokens(): HasMany
    {
        return $this->hasMany(AccessToken::class, 'property_id', 'id');
    }

    /**
     * Link to uploaded pictures for this property.
     */
    public function pictures(): HasMany
    {
        return $this->hasMany(PropertyPic::class, 'property_id', 'id');
    }

    // ============================================================
    // Accessors & Formatters
    // ============================================================

    /**
     * Virtual attribute for clean view label rendering: $property->portfolio_node_label
     * Outputs: "Suite 404 - Victoria Complex" or "Townhouse 12 - 88 Lakeshore Rd"
     */
    public function getPortfolioNodeLabelAttribute(): string
    {
        $prefix = $this->unit_number ? "{$this->unit_number} - " : "";
        return $prefix . ($this->property_name ?: $this->address_line1);
    }
}

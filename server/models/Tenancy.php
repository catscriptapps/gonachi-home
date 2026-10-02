<?php
// /server/models/Tenancy.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tenancy
 * Links a landlord + tenant (+ optionally a property) into a single
 * relationship that both sides can review — the entity that makes
 * double-blind review release possible: without a shared Tenancy, there
 * would be no way to know a landlord's review and a tenant's review are
 * about the same real-world relationship. See Src\Service\TenancyService.
 */
class Tenancy extends Model
{
    protected $table = 'ltv_tenancies';

    protected $fillable = [
        'landlord_id',
        'tenant_id',
        'property_id',
        'initiated_by_user_id',
        'landlord_confirmed_at',
        'tenant_confirmed_at',
        'status',
        'start_date',
        'end_date',
        'country_id',
        'is_verified',
        'verification_method',
        'review_window_opened_at',
        'review_window_closes_at',
    ];

    protected $casts = [
        'landlord_id' => 'integer',
        'tenant_id' => 'integer',
        'property_id' => 'integer',
        'initiated_by_user_id' => 'integer',
        'country_id' => 'integer',
        'is_verified' => 'boolean',
        'landlord_confirmed_at' => 'datetime',
        'tenant_confirmed_at' => 'datetime',
        'start_date' => 'date',
        'end_date' => 'date',
        'review_window_opened_at' => 'datetime',
        'review_window_closes_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function landlord()
    {
        return $this->belongsTo(LandlordRecord::class, 'landlord_id');
    }

    public function tenant()
    {
        return $this->belongsTo(TenantRecord::class, 'tenant_id');
    }

    public function property()
    {
        return $this->belongsTo(PropertyRecord::class, 'property_id');
    }

    public function initiatedBy()
    {
        return $this->belongsTo(User::class, 'initiated_by_user_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'tenancy_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }
}

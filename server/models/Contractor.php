<?php
// /server/models/Contractor.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contractor extends Model
{
    protected $table = 'cde_contractors';

    protected $fillable = [
        'contractor_source_id',
        'external_id',
        'business_name',
        'service_category',
        'location',
        'operating_areas',
        'phone',
        'website',
        'description',
        'rating',
        'review_count',
        'claimed_by_user_id',
        'claim_status',
        'status',
    ];

    protected $casts = [
        'contractor_source_id' => 'integer',
        'claimed_by_user_id' => 'integer',
        'rating' => 'decimal:1',
        'review_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function source()
    {
        return $this->belongsTo(ContractorSource::class, 'contractor_source_id');
    }

    public function claimedBy()
    {
        return $this->belongsTo(User::class, 'claimed_by_user_id');
    }

    public function claims()
    {
        return $this->hasMany(ContractorClaim::class, 'contractor_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}

<?php
// /server/models/RentalListing.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentalListing extends Model
{
    protected $table = 'ltv_rental_listings';

    protected $fillable = [
        'property_id',
        'landlord_id',
        'user_id',
        'area',
        'bedrooms',
        'property_type',
        'rent_amount',
        'rent_period',
        'description',
        'status',
    ];

    protected $casts = [
        'property_id' => 'integer',
        'landlord_id' => 'integer',
        'user_id' => 'integer',
        'bedrooms' => 'integer',
        'rent_amount' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function property()
    {
        return $this->belongsTo(PropertyRecord::class, 'property_id');
    }

    public function landlord()
    {
        return $this->belongsTo(LandlordRecord::class, 'landlord_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function photos()
    {
        return $this->hasMany(RentalListingPhoto::class, 'listing_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}

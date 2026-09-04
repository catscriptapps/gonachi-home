<?php
// /server/models/RentalListingPhoto.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentalListingPhoto extends Model
{
    protected $table = 'ltv_rental_listing_photos';

    protected $fillable = [
        'listing_id',
        'file_path',
    ];

    protected $casts = [
        'listing_id' => 'integer',
    ];

    public function listing()
    {
        return $this->belongsTo(RentalListing::class, 'listing_id');
    }
}

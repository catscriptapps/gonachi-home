<?php
// /server/models/SwapSavedListing.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SwapSavedListing extends Model
{
    protected $table = 'swp_saved_listings';

    protected $fillable = [
        'user_id',
        'listing_id',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'listing_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function listing()
    {
        return $this->belongsTo(SwapListing::class, 'listing_id');
    }
}

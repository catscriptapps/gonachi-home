<?php
// /server/models/SwapListingCategory.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SwapListingCategory extends Model
{
    protected $table = 'swp_listing_categories';

    protected $fillable = [
        'name',
        'slug',
    ];

    public function listings()
    {
        return $this->hasMany(SwapListing::class, 'category_id');
    }
}

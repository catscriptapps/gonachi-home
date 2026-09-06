<?php
// /server/models/ListingCategory.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ListingCategory extends Model
{
    protected $table = 'rew_listing_categories';
    protected $primaryKey = 'category_id';

    public $incrementing = true;

    protected $fillable = ['category_id', 'category'];

    protected $casts = [
        'category_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function types(): HasMany
    {
        return $this->hasMany(ListingCategoryType::class, 'category_id', 'category_id');
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class, 'category_id', 'category_id');
    }
}

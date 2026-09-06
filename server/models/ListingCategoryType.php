<?php
// /server/models/ListingCategoryType.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingCategoryType extends Model
{
    protected $table = 'rew_listing_category_types';
    protected $primaryKey = 'category_type_id';

    public $incrementing = true;

    protected $fillable = ['category_id', 'category_type'];

    protected $casts = [
        'category_type_id' => 'integer',
        'category_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ListingCategory::class, 'category_id', 'category_id');
    }
}

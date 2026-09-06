<?php
// /server/models/Amenity.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Amenity extends Model
{
    protected $table = 'rew_amenities';
    protected $primaryKey = 'amenity_id';

    public $incrementing = true;

    protected $fillable = ['category_id', 'name'];

    protected $casts = [
        'amenity_id' => 'integer',
        'category_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(AmenityCategory::class, 'category_id', 'category_id');
    }
}

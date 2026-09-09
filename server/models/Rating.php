<?php
// /server/models/Rating.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rating extends Model
{
    protected $table = 'rew_ratings';
    protected $primaryKey = 'rating_id';

    public $incrementing = true;

    protected $fillable = [
        'orig_user_id',
        'dest_user_id',
        'dest_country_id',
        'dest_region_id',
        'dest_city',
        'dest_user_type_id',
        'comment',
    ];

    protected $casts = [
        'rating_id' => 'integer',
        'orig_user_id' => 'integer',
        'dest_user_id' => 'integer',
        'dest_country_id' => 'integer',
        'dest_region_id' => 'integer',
        'dest_user_type_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function rater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'orig_user_id', 'id');
    }

    public function ratee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dest_user_id', 'id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'dest_country_id', 'id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'dest_region_id', 'id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(RatingCriteriaScore::class, 'rating_id', 'rating_id');
    }
}

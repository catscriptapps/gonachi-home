<?php
// /server/models/Recommendation.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recommendation extends Model
{
    protected $table = 'rew_recommendations';
    protected $primaryKey = 'recommend_id';

    public $incrementing = true;

    protected $fillable = [
        'orig_user_id',
        'dest_user_id',
        'dest_country_id',
        'dest_region_id',
        'dest_city',
        'dest_user_type_id',
        'rec_user_type_id',
        'comment',
    ];

    protected $casts = [
        'recommend_id' => 'integer',
        'orig_user_id' => 'integer',
        'dest_user_id' => 'integer',
        'dest_country_id' => 'integer',
        'dest_region_id' => 'integer',
        'dest_user_type_id' => 'integer',
        'rec_user_type_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function recommender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'orig_user_id', 'id');
    }

    public function recommended(): BelongsTo
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
}

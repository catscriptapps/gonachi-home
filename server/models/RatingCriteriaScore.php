<?php
// /server/models/RatingCriteriaScore.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RatingCriteriaScore extends Model
{
    protected $table = 'rew_rating_criteria_scores';

    public $timestamps = true;

    protected $fillable = ['rating_id', 'criteria_id', 'stars'];

    protected $casts = [
        'id' => 'integer',
        'rating_id' => 'integer',
        'criteria_id' => 'integer',
        'stars' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(RatingCriterion::class, 'criteria_id', 'criteria_id');
    }

    public function rating(): BelongsTo
    {
        return $this->belongsTo(Rating::class, 'rating_id', 'rating_id');
    }
}

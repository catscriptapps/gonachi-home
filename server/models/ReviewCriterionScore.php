<?php
// /server/models/ReviewCriterionScore.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewCriterionScore extends Model
{
    protected $table = 'ltv_review_criterion_scores';

    protected $fillable = [
        'review_id',
        'criterion_id',
        'stars',
        'is_na',
    ];

    protected $casts = [
        'review_id' => 'integer',
        'criterion_id' => 'integer',
        'stars' => 'integer',
        'is_na' => 'boolean',
    ];

    public function review()
    {
        return $this->belongsTo(Review::class, 'review_id');
    }

    public function criterion()
    {
        return $this->belongsTo(ReviewCriterion::class, 'criterion_id');
    }
}

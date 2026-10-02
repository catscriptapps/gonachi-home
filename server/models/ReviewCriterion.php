<?php
// /server/models/ReviewCriterion.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewCriterion extends Model
{
    protected $table = 'ltv_review_criteria';

    protected $fillable = [
        'subject_type',
        'key',
        'label',
        'prompt',
        'weight',
        'sort_order',
    ];

    protected $casts = [
        'weight' => 'integer',
        'sort_order' => 'integer',
    ];

    public function scores()
    {
        return $this->hasMany(ReviewCriterionScore::class, 'criterion_id');
    }

    public static function forSubject(string $subjectType)
    {
        return self::where('subject_type', $subjectType)->orderBy('sort_order')->get();
    }
}

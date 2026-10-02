<?php
// /server/models/ReviewTag.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewTag extends Model
{
    protected $table = 'ltv_review_tags';

    protected $fillable = [
        'review_id',
        'tag_key',
    ];

    protected $casts = [
        'review_id' => 'integer',
    ];

    public function review()
    {
        return $this->belongsTo(Review::class, 'review_id');
    }
}

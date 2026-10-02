<?php
// /server/models/ReviewResponse.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewResponse extends Model
{
    protected $table = 'ltv_review_responses';

    protected $fillable = [
        'review_id',
        'responder_user_id',
        'response',
    ];

    protected $casts = [
        'review_id' => 'integer',
        'responder_user_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function review()
    {
        return $this->belongsTo(Review::class, 'review_id');
    }

    public function responder()
    {
        return $this->belongsTo(User::class, 'responder_user_id');
    }
}

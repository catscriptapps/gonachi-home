<?php
// /server/models/ReviewReport.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewReport extends Model
{
    protected $table = 'ltv_review_reports';

    protected $fillable = [
        'review_id',
        'reporter_user_id',
        'reason',
        'details',
        'status',
        'resolved_by_user_id',
        'resolved_at',
        'resolution_notes',
    ];

    protected $casts = [
        'review_id' => 'integer',
        'reporter_user_id' => 'integer',
        'resolved_by_user_id' => 'integer',
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function review()
    {
        return $this->belongsTo(Review::class, 'review_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }

    public function scopePendingReview($query)
    {
        return $query->where('status', 'pending_review');
    }
}

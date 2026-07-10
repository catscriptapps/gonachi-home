<?php
// /server/models/JobRequest.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobRequest extends Model
{
    protected $table = 'cde_job_requests';

    protected $fillable = [
        'user_id',
        'service_category',
        'location',
        'budget',
        'description',
        'timeline',
        'contact_phone',
        'status',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'budget' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function photos()
    {
        return $this->hasMany(JobRequestPhoto::class, 'job_request_id');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }
}

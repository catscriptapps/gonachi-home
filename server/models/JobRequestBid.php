<?php
// /server/models/JobRequestBid.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobRequestBid extends Model
{
    protected $table = 'cde_job_request_bids';

    public $timestamps = true;

    protected $fillable = ['job_request_id', 'sender_id', 'quote_amount', 'message', 'status'];

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_DECLINED = 'declined';

    protected $casts = [
        'id' => 'integer',
        'job_request_id' => 'integer',
        'sender_id' => 'integer',
        'quote_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }

    public function jobRequest(): BelongsTo
    {
        return $this->belongsTo(JobRequest::class, 'job_request_id', 'id');
    }
}

<?php
// /server/models/QuotationResponse.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationResponse extends Model
{
    protected $table = 'rew_quotation_responses';

    public $timestamps = true;

    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_DECLINED = 'declined';

    protected $fillable = ['sender_id', 'quotation_id', 'status', 'message'];

    protected $casts = [
        'sender_id' => 'integer',
        'quotation_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'quotation_id', 'quotation_id');
    }
}

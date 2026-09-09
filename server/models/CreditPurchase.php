<?php
// /server/models/CreditPurchase.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditPurchase extends Model
{
    protected $table = 'rel_credit_purchases';

    public const STATUS_PENDING = 'pending';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'reference',
        'credits',
        'amount_kobo',
        'status',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'credits' => 'integer',
        'amount_kobo' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

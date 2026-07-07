<?php
// /server/models/CreditTransaction.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditTransaction extends Model
{
    protected $table = 'rel_credit_transactions';

    protected $fillable = [
        'user_id',
        'amount',
        'balance_after',
        'reason',
        'reference_type',
        'reference_id',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'amount' => 'integer',
        'balance_after' => 'integer',
        'reference_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

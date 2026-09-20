<?php
// /server/models/LandlordCreditAccount.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandlordCreditAccount extends Model
{
    protected $table = 'ltv_credit_accounts';

    protected $fillable = [
        'user_id',
        'balance',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'balance' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

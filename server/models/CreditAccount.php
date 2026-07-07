<?php
// /server/models/CreditAccount.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditAccount extends Model
{
    protected $table = 'rel_credit_accounts';

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

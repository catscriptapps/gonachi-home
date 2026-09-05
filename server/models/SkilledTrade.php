<?php
// /server/models/SkilledTrade.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SkilledTrade extends Model
{
    protected $table = 'rew_skilled_trades';
    protected $primaryKey = 'skilled_trade_id';

    public $incrementing = true;

    protected $fillable = ['skilled_trade'];

    protected $casts = [
        'skilled_trade_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class, 'skilled_trade_id', 'skilled_trade_id');
    }
}

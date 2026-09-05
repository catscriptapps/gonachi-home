<?php
// /server/models/QuotationType.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuotationType extends Model
{
    protected $table = 'rew_quotation_types';
    protected $primaryKey = 'quotation_type_id';

    public $incrementing = true;

    protected $fillable = ['quotation_type'];

    protected $casts = [
        'quotation_type_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class, 'quotation_type_id', 'quotation_type_id');
    }
}

<?php
// /server/models/QuotationDestination.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuotationDestination extends Model
{
    protected $table = 'rew_quotation_destinations';
    protected $primaryKey = 'quotation_dest_id';

    public $incrementing = true;

    protected $fillable = ['quotation_dest'];

    protected $casts = [
        'quotation_dest_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class, 'quotation_dest_id', 'quotation_dest_id');
    }
}

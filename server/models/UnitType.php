<?php
// /server/models/UnitType.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitType extends Model
{
    protected $table = 'rew_unit_types';
    protected $primaryKey = 'unit_type_id';

    public $incrementing = true;

    protected $fillable = ['unit_type'];

    protected $casts = [
        'unit_type_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class, 'unit_type_id', 'unit_type_id');
    }
}

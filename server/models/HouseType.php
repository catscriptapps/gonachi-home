<?php
// /server/models/HouseType.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HouseType extends Model
{
    protected $table = 'rew_house_types';
    protected $primaryKey = 'house_type_id';

    public $incrementing = true;

    protected $fillable = ['house_type'];

    protected $casts = [
        'house_type_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class, 'house_type_id', 'house_type_id');
    }
}

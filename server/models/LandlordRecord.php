<?php
// /server/models/LandlordRecord.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandlordRecord extends Model
{
    protected $table = 'ltv_landlords';

    protected $fillable = [
        'name',
        'normalized_name',
    ];

    public function properties()
    {
        return $this->hasMany(PropertyRecord::class, 'landlord_id');
    }

    public function reports()
    {
        return $this->hasMany(LandlordReport::class, 'landlord_id');
    }
}

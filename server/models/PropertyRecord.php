<?php
// /server/models/PropertyRecord.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyRecord extends Model
{
    protected $table = 'ltv_properties';

    protected $fillable = [
        'landlord_id',
        'address',
        'normalized_address',
        'property_type',
    ];

    protected $casts = [
        'landlord_id' => 'integer',
    ];

    public function landlord()
    {
        return $this->belongsTo(LandlordRecord::class, 'landlord_id');
    }

    public function reports()
    {
        return $this->hasMany(LandlordReport::class, 'property_id');
    }
}

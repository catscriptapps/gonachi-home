<?php
// /server/models/LandlordContactUnlock.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandlordContactUnlock extends Model
{
    protected $table = 'ltv_contact_unlocks';

    protected $fillable = [
        'user_id',
        'landlord_id',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'landlord_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function landlord()
    {
        return $this->belongsTo(LandlordRecord::class, 'landlord_id');
    }
}

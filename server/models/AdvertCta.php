<?php
// /server/models/AdvertCta.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvertCta extends Model
{
    protected $table = 'rew_advert_ctas';

    protected $fillable = [
        'label',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

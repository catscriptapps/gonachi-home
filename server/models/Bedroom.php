<?php
// /server/models/Bedroom.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bedroom extends Model
{
    protected $table = 'rew_bedrooms';
    protected $primaryKey = 'bedroom_id';

    public $incrementing = true;

    protected $fillable = ['bedroom_id', 'bedroom'];

    protected $casts = [
        'bedroom_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

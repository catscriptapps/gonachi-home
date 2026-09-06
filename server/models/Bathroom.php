<?php
// /server/models/Bathroom.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bathroom extends Model
{
    protected $table = 'rew_bathrooms';
    protected $primaryKey = 'bathroom_id';

    public $incrementing = true;

    protected $fillable = ['bathroom_id', 'bathroom'];

    protected $casts = [
        'bathroom_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

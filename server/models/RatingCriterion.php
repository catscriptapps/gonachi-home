<?php
// /server/models/RatingCriterion.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RatingCriterion extends Model
{
    protected $table = 'rew_rating_criteria';
    protected $primaryKey = 'criteria_id';

    public $incrementing = true;

    protected $fillable = ['user_type_id', 'criteria'];

    protected $casts = [
        'criteria_id' => 'integer',
        'user_type_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

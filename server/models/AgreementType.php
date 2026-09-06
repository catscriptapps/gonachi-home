<?php
// /server/models/AgreementType.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgreementType extends Model
{
    protected $table = 'rew_agreement_types';
    protected $primaryKey = 'agreement_type_id';

    public $incrementing = true;

    protected $fillable = ['agreement_type_id', 'agreement_type'];

    protected $casts = [
        'agreement_type_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

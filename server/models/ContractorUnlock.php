<?php
// /server/models/ContractorUnlock.php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractorUnlock extends Model
{
    protected $table = 'cde_contractor_unlocks';

    protected $fillable = [
        'user_id',
        'contractor_id',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'contractor_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }
}
